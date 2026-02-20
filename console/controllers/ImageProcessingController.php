<?php

namespace console\controllers;

use Yii;
use common\ImageProcessing\BaseImageHandler;
use common\models\Asset;
use \yii\console\Controller;
use Aws\Sqs\SqsClient;

class ImageProcessingController extends Controller {

    private $lockName = 'image-processing-lock';
    private $processTime = null;

    public function actionIndex(){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        if (!\Yii::$app->mutex->acquire($this->lockName, 0)) {
            $this->stdout("Image Processing job already running. Exiting.\n");
            return;
        }
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

        // Run forever...
        while (1){
            $this->checkTimestamp();
            $iteration++;

            if ($iteration % 10 === 0) {
                echo"SQS worker heartbeat iteration=" . $iteration . " mem=" . memory_get_usage(true) . "\n";
            }

            try {
                echo "Getting messages from queue...\n";
                $result = $sqs->receiveMessage([
                    'QueueUrl' => $queueUrl,
                    'MaxNumberOfMessages' => 10,   // up to 10 per call
                    'WaitTimeSeconds' => 20,       // long polling (recommended)
                    'VisibilityTimeout' => 60,     // seconds to process
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
                        $imageHandler->createThumbnail(250, 250)->saveThumbnail($asset);

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
                echo  "AwsException - " . $e->getAwsErrorMessage() . "\n", $e->getMessage() . "\n";
                echo "Ending Image Processing job 1\n";
                \Yii::$app->mutex->release($this->lockName);
                exit;
            }
            catch(\Throwable $e){
                echo  "AwsException2 - " . $e->getAwsErrorMessage() . "\n", $e->getMessage() . "\n";
                echo "Ending Image Processing job 2\n";
                \Yii::$app->mutex->release($this->lockName);
                exit;
            }

            // If file has changed, exit so new process can be restarted by crontab.
            $this->checkTimestamp();

            // Retry processing in x more seconds.
            sleep(10);
        }
        \Yii::$app->mutex->release($this->lockName);
        echo "Done\n";
    }

    private function checkTimestamp(){
        clearstatcache(true, __FILE__);
        echo "Current file timestamp: " . filemtime(__FILE__) . "\n";
        if (filemtime(__FILE__) !== $this->processTime){
            echo "Exiting Process as it has been modified.\n";
            Yii::$app->mutex->release($this->lockName);
            exit;
        }

    }

}