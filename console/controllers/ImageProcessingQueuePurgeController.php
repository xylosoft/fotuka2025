<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class ImageProcessingQueuePurgeController extends Controller
{
    /**
     * Purges the hardcoded SQS queue.
     *
     * Usage:
     * php yii queue/purge
     */
    public function actionIndex()
    {
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $queueUrl = 'https://sqs.' . Yii::$app->params['AWS_REGION'] . '.amazonaws.com/191728941649/' . $env . '_processing';
        echo "Queue URL: " . $queueUrl . "\n";
        $this->purgeQueue($queueUrl);
        $queueUrl .= '-failed';
        echo "Queue URL: " . $queueUrl . "\n";
        $this->purgeQueue($queueUrl);
    }

    private function purgeQueue($queueUrl){
        $sqs = new SqsClient([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        try {
            $sqs->purgeQueue([
                'QueueUrl' => $queueUrl,
            ]);

            $this->stdout("Queue $queueUrl initiated successfully.\n");

        } catch (AwsException $e) {
            $this->stderr("Error purging queue:\n");
            $this->stderr($e->getAwsErrorMessage() . "\n");
        }


    }
}