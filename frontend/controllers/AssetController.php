<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\FileHelper;
use common\models\Asset;
use common\models\File;

class AssetController extends Controller
{
    public $enableCsrfValidation = true;

    public function actionUpload(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $folderId = Yii::$app->request->post('id');
        error_log("Uploading file to folder: " .  $folderId);
        $files = UploadedFile::getInstancesByName('files');
        error_log("Files Uploaded: " . count($files));

        if (empty($files)) {
            return ['ok' => false, 'error' => 'No files received'];
        }

        // Generate a unique temporary upload directory under /tmp
        $tempBase = sys_get_temp_dir() . '/uploads';
        FileHelper::createDirectory($tempBase);

        $uniqueDir = uniqid('upload_' . $folderId . '_', true);
        $uploadPath = $tempBase . '/' . $uniqueDir;
        FileHelper::createDirectory($uploadPath);
        error_log("Created folder: " . $uploadPath);

        $uploaded = 0;
        foreach ($files as $file) {
            $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file->name);
            $targetFile = $uploadPath . '/' . $safeName;

            if ($file->saveAs($targetFile)) {
                $uploaded++;

                // First we need to create the file
                $file = new File();
                $file->customer_id = Yii::$app->user->identity->customer_id;
                $file->user_id = Yii::$app->user->id;
                $file->type =  File::TYPE_OTHER;
                $file->width = null;
                $file->height = null;
                $file->thumbnail = 'pending';
                $file->preview = 'pending';
                $file->filename = '';
                $file->extension = '';
                $file->orientation = null;
                $file->filesize = 0;
                $file->pages = 0;
                $file->save();

                // Then we need to create the asset
                $asset = new Asset();
                $asset->folder_id = $folderId;
                $asset->customer_id = Yii::$app->user->identity->customer_id;
                $asset->user_id = Yii::$app->user->id;
                $asset->file_id = 0; // adjust if you have file references
                $asset->title = $file->name;
                $asset->thumbnail_url = '/uploads/' . $folderId . '/' . $safeName;
                $asset->save(false);

                // Then we need to upload to S3 with the proper asset id in place
                // The path for S3 will be: <environment>/original/<customer_id>/<folder_id>/<asset_id>.extension

                // If anything fails, remove the file & asset entries.

                // Queue file for thumbnail & preview generation.
            }
        }

        return ['ok' => true, 'uploaded' => $uploaded];
    }
}
