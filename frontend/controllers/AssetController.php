<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\FileHelper;
use common\models\Asset;
use common\models\File;
use common\models\Folder;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

class AssetController extends Controller
{
    public $enableCsrfValidation = true;

    public function actionUpload(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $folderId = Yii::$app->request->post('id');
        error_log("UPLOAD - Folder ID: $folderId");
        $customerId = Yii::$app->user->identity->customer_id;
        error_log("UPLOAD - Customer ID: $customerId");
        $userId = Yii::$app->user->id;
        error_log("UPLOAD - User ID: $userId");
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        error_log("UPLOAD - Env: $env");
        $CloudfrontDomain = Yii::$app->params['CLOUDFRONT_DOIMAIN'];
        error_log("UPLOAD - Domain: $CloudfrontDomain");

        $files = UploadedFile::getInstancesByName('files');
        $paths = Yii::$app->request->post('paths', []);
        error_log(count($files) . " files are being Uploaded...");
        error_log("Paths: " . print_r($paths,1));

        if (empty($files)) {
            return ['ok' => false, 'error' => 'No files received'];
        }

        error_log("Params: " . print_r(Yii::$app->params,1));

        // AWS config
        $s3 = new S3Client([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        // Generate a unique temporary upload directory under /tmp
        $tempBase = sys_get_temp_dir() . 'uploads';
        FileHelper::createDirectory($tempBase);

        $uniqueDir = uniqid('upload_' . $folderId . '_', true);
        $uploadPath = $tempBase . '/' . $uniqueDir;
        FileHelper::createDirectory($uploadPath);
        error_log("Created folder: " . $uploadPath);

        $uploaded = 0;
        foreach ($files as $index => $uploadedFile) {
            // Ignore .DS_Store files
            if ($uploadedFile->name == ".DS_Store"){
                error_log("Skipping...");
                continue;
            }
            $relativePath = $paths[$index] ?? $uploadedFile->name;
            // Normalize and sanitize the relative path
            $relativePath = str_replace('\\', '/', $relativePath);
            $relativePath = ltrim($relativePath, '/');
            if (strpos($relativePath, '..') !== false) {
                continue; // skip dangerous paths
            }

            $folderPath = trim(dirname($relativePath), '/');
            error_log("Folder Path: $folderPath");
            $uploadFolderId = $this->ensureFolderPath($customerId, $userId, $folderId, $folderPath);

            error_log("Original Filename: {$uploadedFile->baseName}");
            error_log("Original Extension: {$uploadedFile->extension}");
            error_log("Original Filename: {$uploadedFile->name}");
            error_log("Relative Path: {$relativePath}");
            error_log("New Folder ID: {$uploadFolderId}");

            $targetFile = $uploadPath . '/' . $uploadedFile->name;
            error_log("Target File: {$targetFile}");

            if ($uploadedFile->saveAs($targetFile)) {
                $uploaded++;

                // First we need to create the file
                error_log("Creating file");
                $file = new File();
                $file->customer_id = $customerId;
                $file->user_id = $userId;
                $file->type = File::TYPE_OTHER;
                $file->width = null;
                $file->height = null;
                $file->filename = $uploadedFile->name;
                $file->extension = $uploadedFile->extension;
                $file->orientation = null;
                $file->filesize = $uploadedFile->size;
                $file->pages = 0;
                $file->tmp_location = $targetFile;
                $res = null;
                $res = $file->save();

                error_log("File entry was created: " . ($res ? "Successfully" : "FAILED"));
                if (!$res) {
                    error_log(print_r($file->getErrors(), true));
                }


                // Then we need to create the asset
                error_log("Creating Asset");
                $asset = new Asset();
                $asset->folder_id = $uploadFolderId;
                $asset->customer_id = $customerId;
                $asset->user_id = $userId;
                $asset->file_id = $file->id; // adjust if you have file references
                $asset->title = $uploadedFile->name;
                $asset->thumbnail_state = Asset::THUMBNAIL_PENDING;
                $asset->preview_state = Asset::PREVIEW_PENDING;
                $asset->thumbnail_url = null;
                $res = $asset->save();
                error_log("Asset entry was created: " . ($res?"Successfully":"FAILED"));
                if (!$res) {
                    error_log(print_r($asset->getErrors(), true));
                }


                // Then we need to upload to S3 with the proper asset id in place
                // The path for S3 will be: <environment>/original/<customer_id>/<asset_id> without
                try {
                    error_log("Uploading file to S3...");

                    // Determine proper file type.
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $targetFile);
                    finfo_close($finfo);
                    error_log("Mime Type: " . $mimeType);

                    $key = "{$env}/original/{$customerId}/{$asset->id}";
                    error_log("S3 path: " . $key);

                    $result = $s3->putObject([
                        'Bucket' => Yii::$app->params['AWS_BUCKET'],
                        'Key' => $key,
                        'SourceFile' => $targetFile,
                        'ACL' => 'private', // or 'public-read' if you want instant CloudFront access
                        'CacheControl' => 'max-age=31536000',
                        'ContentType' => $mimeType,
                    ]);
                    error_log("S3 upload result: " . ($result ? "OK" : "FAILED"));


                    // Add SQS Message to queue asset for processing
                    $queueUrl = 'https://sqs.' . Yii::$app->params['AWS_REGION'] . '.amazonaws.com/191728941649/' . $env . '_processing';
                    error_log("Queue URL: " . $queueUrl);

                    $event = [
                        'type' => 'Processing',
                        'version' => 1,
                        'MessageGroupId' => 'users',
                        'timestamp' => gmdate('c'),
                        'data' => [
                            'userId' => $userId,
                            'assetId' => $asset->id,
                        ],
                    ];

                    $sqs = new SqsClient([
                        'region' => Yii::$app->params['AWS_REGION'],
                        'version' => 'latest',
                        'credentials' => [
                            'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                            'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
                        ],
                    ]);

                    $result = $sqs->sendMessage([
                        'QueueUrl' => $queueUrl,
                        'MessageBody' => json_encode($event, JSON_UNESCAPED_SLASHES),
                        // Optional metadata:
                        'MessageAttributes' => [
                            'eventType' => [
                                'DataType' => 'String',
                                'StringValue' => $event['type'],
                            ],
                        ],
                    ]);

                    // CloudFront public URL (if needed)
                    $url = "https://" . $CloudfrontDomain . '/' . $key;
                    error_log("Cloudfront URL: $url");

                    // Queue file for thumbnail & preview generation.

                } catch (AwsException $e) {
                    // If anything fails, remove the file & asset entries.

                    error_log('S3 Upload error: ' . $e->getMessage());
                    return ['ok' => false, 'message' => 'S3 Upload failed.'];
                }
            }
        }

        return ['ok' => true, 'uploaded' => $uploaded];
    }

    private function ensureFolderPath($customerId, $userId, $folderId, $path){
        try {
            error_log("Insside ensureFolderPath");
            if ($path == ".") {
                error_log("returning $folderId");
                return $folderId;
            }

            $parts = explode('/', $path);
            error_log("Parts" . print_r($parts, 1));
            $parentId = $folderId;

            foreach ($parts as $part) {
                error_log("Processing folder: $part - Parent: $parentId - Name: $part");
                $folder = Folder::findOne(['parent_id' => $parentId, 'name' => $part]);
                if (!$folder) {
                    error_log("Creating folder: $part as child of $parentId");
                    $folder = new Folder([
                        'customer_id' => $customerId,
                        'parent_id' => $parentId,
                        'user_id' => $userId,
                        'name' => $part,
                        'status' => Folder::STATUS_ACTIVE
                    ]);
                    $folder->save();
                } else {
                    error_log("Folder already exists");
                }
                $parentId = $folder->id;
            }

            error_log("Returning: $parentId");
            return $parentId;
        }catch(\Exception $e){
            error_log($e->getMessage());
        }
    }
}
