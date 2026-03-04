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

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionUpload(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $folderId = Yii::$app->request->post('id');
        $customerId = Yii::$app->user->identity->customer_id;
        $userId = Yii::$app->user->id;
        $env = YII_ENV_DEV ? 'dev' : 'prod';

        $files = UploadedFile::getInstancesByName('files');
        $paths = Yii::$app->request->post('paths', []);

        if (empty($files)) {
            return ['ok' => false, 'error' => 'No files received'];
        }

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
        $assets = [];

        error_log("AssetController UPLOAD: There are: " . count($files) . " uploaded.");
        foreach ($files as $index => $uploadedFile) {
            // Ignore .DS_Store files
            if ($uploadedFile->name == ".DS_Store"){
                error_log("Skipping .DS_Store file.");
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
            $uploadFolderId = AssetController::ensureFolderPath($customerId, $userId, $folderId, $folderPath);

            error_log("Original Filename: {$uploadedFile->baseName}");
            error_log("Original Extension: {$uploadedFile->extension}");
            error_log("Original Filename: {$uploadedFile->name}");
            error_log("Relative Path: {$relativePath}");
            error_log("New Folder ID: {$uploadFolderId}");

            $targetFile = $uploadPath . '/' . $uploadedFile->name;
            error_log("Target File: {$targetFile}");

            if ($uploadedFile->saveAs($targetFile)) {

                $uploaded++;
                $fileSize = $uploadedFile->size;

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
                $file->filesize = $fileSize;
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
                }else{
                    $assets[] = [
                        'id' => (int)$asset->id,
                        'title' => (string)$asset->title,
                        'thumbnail_url' => $asset->thumbnail_url,
                        'thumbnail_state' => Asset::PREVIEW_PENDING,
                    ];
                }

                // Also update customer's and folder's ustorage usage
                $customer = $asset->customer;
                $customer->storage_used = $customer->storage_used + $fileSize;
                $customer->save('space_used');

                $folder = $asset->folder;
                $folder->storage_used = $folder->storage_used + $fileSize;
                $folder->save('space_used');


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
                        'StorageClass' => 'INTELLIGENT_TIERING',
                    ]);
                    error_log("S3 upload result: " . ($result ? "OK" : "FAILED"));


                    // Queue file for thumbnail & preview generation.
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

                } catch (AwsException $e) {
                    // If anything fails, remove the file & asset entries.
                    error_log('S3 Upload error: ' . $e->getMessage());
                    return ['error' => false, 'message' => 'S3 Upload failed.', 'assets' => []];
                }catch (\Throwable $e){
                    error_log('S3 Upload error: ' . $e->getMessage());
                    return ['error' => false, 'message' => 'S3 Upload failed.', 'assets' => []];
                }
            }
        }

        return ['ok' => true, 'uploaded' => $uploaded, 'assets' => $assets,];
    }

    public static function ensureFolderPath($customerId, $userId, $folderId, $path){
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
                $folder = Folder::findOne(['parent_id' => $parentId, 'name' => $part, 'status' => 'active']);
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

    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;

        // Bulk delete: ids[] posted
        $ids = Yii::$app->request->post('ids', null);

        // Single delete fallback: id posted
        if ($ids === null) {
            $id = Yii::$app->request->post('id');
            $ids = $id ? [$id] : [];
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return ['ok' => false, 'message' => 'No asset ids provided.'];
        }

        $assets = Asset::find()
            ->where(['id' => $ids, 'user_id' => $userId])
            ->all();

        if (empty($assets)) {
            return ['ok' => false, 'message' => 'No matching assets found.'];
        }

        // Soft delete recommended
        $deletedCount = 0;
        foreach ($assets as $asset) {
            $asset->status = Asset::STATUS_DELETED; // adjust constant to yours
            if ($asset->save(false)) {
                $deletedCount++;
            }
        }

        return [
            'ok' => true,
            'deleted' => $deletedCount,
            'requested' => count($ids),
        ];
    }

}
