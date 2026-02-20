<?php

namespace console\controllers;

use Yii;
use common\ImageProcessing\BaseImageHandler;
use common\models\Asset;
use \yii\console\Controller;
use Aws\Sqs\SqsClient;

class ImageProcessingController extends Controller {

    private $processTime = null;


    public function actionIndex(){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        echo "Running Image Processing Cronjob\n";

        $this->processTime = filemtime(__FILE__);
        echo "Current file timestamp: " . $this->processTime . "\n";


        $sqs = new SqsClient([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $queueUrl = 'https://sqs.' . Yii::$app->params['AWS_REGION'] . '.amazonaws.com/191728941649/' . $env . '_processing';
        $iteration = 0;
        $running = true;
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function() use (&$running) {
            echo "SIGTERM received. Stopping Process\n";
            $running = false;
        });

        // Run forever...
        while ($running){
            $this->checkTimestamp();
            $iteration++;


            if ($iteration % 10 === 0) {
                echo"SQS worker heartbeat iteration=" . $iteration . " mem=" . memory_get_usage(true) . "\n";
            }

            try {
                echo "Getting messages from queue...\n";
                $result = $sqs->receiveMessage([
                    'QueueUrl' => $queueUrl,
                    'MaxNumberOfMessages' => 1,
                    'WaitTimeSeconds' => 20,
                    'VisibilityTimeout' => 60,
                ]);

                if (empty($result['Messages'])) {
                    echo "No messages\n";
                    continue;
                }

                foreach ($result['Messages'] as $message) {
                    $data = json_decode($message['Body'])->data;
                    echo "Processing message: for Asset ID: " . $data->assetId . "\n";
                    $asset = Asset::findOne($data->assetId);

                    $imageHandler = BaseImageHandler::fetchHandler($asset);
                    if (!$imageHandler){
                        $asset->thumbnail_state = Asset::THUMBNAIL_UNSUPPORTED;
                        $asset->preview_state = Asset::THUMBNAIL_UNSUPPORTED;
                        $asset->save();
                    }else{
                        $imageHandler->setDestinationFormat(BaseImageHandler::FORMAT_JPG);
                        $imageHandler->createThumbnail(250, 250)->saveThumbnail($asset);
                        $imageHandler->cleanup($asset);

                        // 🔹 Delete AFTER successful processing
                        $sqs->deleteMessage([
                            'QueueUrl' => $queueUrl,
                            'ReceiptHandle' => $message['ReceiptHandle'],
                        ]);
                    }

                    $asset = null;
                    $data = null;
                    $imageHandler = null;
                }

                $result = null;
            } catch (\Aws\Exception\AwsException $e) {
                // AWS SDK errors (throttling, timeouts, auth, etc.)
                echo  "AwsException - " . $e->getAwsErrorMessage() . "\n" .  $e->getMessage() . "\n";
                echo "Ending Image Processing job 1\n";
                exit;
            }
            catch(\Throwable $e){
                echo  "Exception2 - " .  $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
                echo "Ending Image Processing job 2\n";
                exit;
            }

            // If file has changed, exit so new process can be restarted by crontab.
            $this->checkTimestamp();
        }

        echo "Done\n";
    }

    private function checkTimestamp(){
        clearstatcache(true, __FILE__);
        if (filemtime(__FILE__) !== $this->processTime){
            echo "Exiting Process as it has been modified.\n";
            exit;
        }

    }

}