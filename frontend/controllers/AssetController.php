<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\FileHelper;
use common\models\Asset;
use common\models\File;
use Aws\S3\S3Client;
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
        error_log(count($files) . " files are being Uploaded...");

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
        foreach ($files as $uploadedFile) {
            error_log("Original Filename: {$uploadedFile->baseName}");
            error_log("Original Extension: {$uploadedFile->extension}");
            error_log("Original Filename: {$uploadedFile->name}");
            //$safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->name);
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
                $file->thumbnail = 'pending';
                $file->preview = 'pending';
                $file->filename = $uploadedFile->name;
                $file->extension = $uploadedFile->extension;
                $file->orientation = null;
                $file->filesize = $uploadedFile->size;
                $file->pages = 0;
                $res = $file->save();
                error_log("File entry was created: " . ($res ? "Successfully" : "FAILED"));
                if (!$res) {
                    error_log(print_r($file->getErrors(), true));
                }


                // Then we need to create the asset
                error_log("Creating Asset");
                $asset = new Asset();
                $asset->folder_id = $folderId;
                $asset->customer_id = $customerId;
                $asset->user_id = $userId;
                $asset->file_id = $file->id; // adjust if you have file references
                $asset->title = $uploadedFile->name;
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
                    error_log("_1");
                    $mimeType = finfo_file($finfo, $targetFile);
                    error_log("_2");
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
                    error_log("S3 upload result: " . ($result?"OK":"FAILED"));

                    // Optionally delete local file
                    @unlink($targetFile);

                    // CloudFront public URL (if needed)
                    $url = "https://" . $CloudfrontDomain . '/' . $key;
                    error_log("Cloudfront URL: $url");

                    // Queue file for thumbnail & preview generation.

                    return ['ok' => true, 'url' => $url];

                } catch (AwsException $e) {
                    // If anything fails, remove the file & asset entries.

                    error_log('S3 Upload error: ' . $e->getMessage());
                    return ['ok' => false, 'message' => 'S3 Upload failed.'];
                }
            }
        }

        return ['ok' => true, 'uploaded' => $uploaded];
    }
}
