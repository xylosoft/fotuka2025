<?php

namespace console\controllers;

use Yii;
use common\ImageProcessing\BaseImageHandler;
use common\models\Asset;
use \yii\console\Controller;
use Aws\Sqs\SqsClient;


/*
use common\ImageProcessing\JPGHandler;
use common\ImageProcessing\PNGHandler;
use common\ImageProcessing\GIFHandler;
use common\ImageProcessing\WEBPHandler;
use common\ImageProcessing\AIHandler;
use common\ImageProcessing\EPSHandler;
use common\ImageProcessing\TIFHandler;
use common\ImageProcessing\PSDHandler;
use common\ImageProcessing\TGAHandler;
*/


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

        // Run forever...
        while (1){
            $this->checkTimestamp();
            try {
                echo "Getting messages from queue...\n";
                $result = $sqs->receiveMessage([
                    'QueueUrl' => $queueUrl,
                    'MaxNumberOfMessages' => 1,   // up to 10 per call
                    'WaitTimeSeconds' => 20,       // long polling (recommended)
                    'VisibilityTimeout' => 60,     // seconds to process
                ]);

                if (empty($result['Messages'])) {
                    echo "No messages\n";
                    continue;
                }

                foreach ($result['Messages'] as $message) {

                    $body = json_decode($message['Body'], true);

                    echo "Processing message: " . $message['MessageId'] . "\n";
                    echo "Message: " . print_r($message,1) . "\n";
                    //echo "Body: " . print_r($message['Body'],1) . "\n";
                    $data = json_decode($message['Body'])->data;
                    //echo "DATA: " . print_r($data,1) . "\n";
                    echo "Asset ID: " . $data->assetId . "\n";
                    $asset = Asset::findOne($data->assetId);

                    echo "ImageProcessingController -1\n";
                    $imageHandler = BaseImageHandler::fetchHandler($asset);
                    echo "ImageProcessingController -2\n";
                    $imageHandler->createThumbnail(250, 250)->saveThumbnail($asset);


                    echo "ImageProcessingController -4\n";

                    error_log("COMPLETED fetchhandler");


                    // 🔹 Delete AFTER successful processing
                    $sqs->deleteMessage([
                        'QueueUrl' => $queueUrl,
                        'ReceiptHandle' => $message['ReceiptHandle'],
                    ]);

                    echo "Deleted\n";
                }
            } catch(\Throwable $e){
                echo "Ending Image Processing job 1\n";
                \Yii::$app->mutex->release($this->lockName);
            }

            // If file has changed, exit so new process can be restarted by crontab.
            $this->checkTimestamp();

            // Retry processing in x more seconds.
            sleep(1);
        }
        \Yii::$app->mutex->release($this->lockName);


        // SVG
        /*
        $imageHandler = new TGAHandler("/Users/rom/Sites/fotuka/testing/jsonatom.svg");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_JPG)
            //->resize(null, 500)
            ->setQuality(100)
            ->convert();
        */

        // JPG
        /*
        $imageHandler = new JPGHandler("/Users/rom/Sites/fotuka/testing/Background_original.jpg");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
                     ->setQuality(100)
                     ->resize(null, 3024/2)
                     ->convert();
        */

        // PNG
        /*
        $imageHandler = new PNGHandler("/Users/rom/Sites/fotuka/testing/Schnauzer_original.png");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            ->setQuality(100)
            ->resize(null, 1024/2)
            ->convert();
        */


        // GIF
        /*
        $imageHandler = new GIFHandler("/Users/rom/Sites/fotuka/testing/Car_original.gif");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            ->setQuality(100)
            ->convert();
        */

        // WEBP
        /*
        $imageHandler = new WEBPHandler("/Users/rom/Sites/fotuka/testing/WEBP_Sample_original.webp");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_TGA)
            ->setQuality(100)
            ->convert();
        */

        // AI
        /*
        $imageHandler = new AIHandler("/Users/rom/Sites/fotuka/testing/Paint_original.ai");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            ->setQuality(100)
            ->resize(null, 1024/2)
            ->convert();
        */

        // EPS
        /*
        $imageHandler = new EPSHandler("/Users/rom/Sites/fotuka/testing/Circle_original.eps");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            ->setQuality(100)
            ->convert();
        */

        // TIF/TIFF
        /*
        $imageHandler = new TIFHandler("/Users/rom/Sites/fotuka/testing/Body_original.tif");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_JPG)
            ->setQuality(100)
            ->convert();
        */

        // PSD
        /*
        $imageHandler = new PSDHandler("/Users/rom/Sites/fotuka/testing/ColorTheme_original.psd");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_JPG)
            //->resize(null, 500)
            ->setQuality(100)
            ->convert();
        */

        // TGA
        /*
        $imageHandler = new TGAHandler("/Users/rom/Sites/fotuka/testing/Seal_original.tga");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            //->resize(null, 120)
            ->setQuality(100)
            ->convert();
        */

        // BMP
        /*
        $imageHandler = new TGAHandler("/Users/rom/Sites/fotuka/testing/Greenland_original.bmp");
        $imageHandler->setDestinationFormat($imageHandler::FORMAT_BMP)
            ->resize(null, 500)
            ->setQuality(100)
            ->convert();
        */

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