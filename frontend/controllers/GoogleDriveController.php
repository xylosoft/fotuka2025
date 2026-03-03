<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use common\models\File;
use common\models\Asset;
use frontend\controllers\AssetController;

class GoogleDriveController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionStart()
    {
        $client = $this->buildClient();

        $state = Yii::$app->security->generateRandomString(32);
        Yii::$app->session->set('gd_oauth_state', $state);

        $client->setState($state);
        return $this->redirect($client->createAuthUrl());
    }

    public function actionCallback()
    {
        $req = Yii::$app->request;

        $code = $req->get('code');
        $state = $req->get('state');

        $expectedState = Yii::$app->session->get('gd_oauth_state');
        Yii::$app->session->remove('gd_oauth_state');

        if (!$code || !$state || !$expectedState || !hash_equals($expectedState, $state)) {
            return $this->renderContent("Google Drive auth failed: invalid state.");
        }

        $client = $this->buildClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return $this->renderContent("Google Drive auth failed: " . htmlspecialchars($token['error']));
        }

        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        // access_token + expiry
        if (!empty($token['access_token'])) {
            $user->setGoogleAccessToken($token['access_token']);
        }

        if (!empty($token['expires_in'])) {
            $user->google_token_expires_at = time() + (int)$token['expires_in'];
        }

        // refresh_token may only appear first time; preserve existing if missing
        if (!empty($token['refresh_token'])) {
            $user->setGoogleRefreshToken($token['refresh_token']);
        }

        // Optional: store email/sub by calling oauth2 userinfo (recommended)
        // This needs "openid email profile" scopes, or you can call Drive "about" with appropriate scopes.
        // For now, you can skip this. Or tell me and I’ll add it cleanly.

        $user->save(false);

        return $this->redirect(['/folders', 'gd_import' => 1]);
    }

    public function actionStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        return [
            'connected' => $user->hasGoogleDriveConnected(),
            'email' => $user->google_email,
        ];
    }

    public function actionToken()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        /** @var \common\models\User $user */
        $user = Yii::$app->user->identity;

        $accessToken = $user->getGoogleAccessToken();
        $refreshToken = $user->getGoogleRefreshToken();

        if (empty($accessToken) && empty($refreshToken)) {
            return ['ok' => false];
        }

        // If access token still valid, return it
        if (!empty($accessToken) && !$user->isGoogleAccessTokenExpired()) {
            return ['ok' => true, 'accessToken' => $accessToken];
        }

        // Refresh
        if (empty($refreshToken)) {
            return ['ok' => false];
        }

        $client = $this->buildClient();
        $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($newToken['error'])) {
            // token revoked or invalid
            $user->setGoogleAccessToken(null);
            $user->setGoogleRefreshToken(null);
            $user->google_token_expires_at = null;
            $user->save(false);

            return ['ok' => false];
        }

        if (!empty($newToken['access_token'])) {
            $user->setGoogleAccessToken($newToken['access_token']);
        }
        if (!empty($newToken['expires_in'])) {
            $user->google_token_expires_at = time() + (int)$newToken['expires_in'];
        }

        $user->save(false);

        return ['ok' => true, 'accessToken' => $user->getGoogleAccessToken()];
    }

    private function buildClient(): GoogleClient
    {
        $cfg = Yii::$app->params['googleDrive'];

        $client = new GoogleClient();
        $client->setClientId($cfg['clientId']);
        $client->setClientSecret($cfg['clientSecret']);
        $client->setRedirectUri($cfg['redirectUri']);
        $client->setScopes($cfg['scopes']);
        $client->setAccessType('offline'); // to get refresh_token
        $client->setPrompt('consent');     // ensures refresh_token on first connect
        return $client;
    }

    public function actionImportGoogleDriveOld()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetFolderId = (int)Yii::$app->request->post('targetFolderId');
        $items = Yii::$app->request->post('items', []);

        if (!$targetFolderId || !is_array($items) || empty($items)) {
            throw new BadRequestHttpException("Missing targetFolderId or items.");
        }

        $user = Yii::$app->user->identity;

        $client = $this->buildGoogleDriveClientForUser($user);
        if ($client === null) {
            return ['ok' => false, 'error' => 'Not connected to Google Drive'];
        }

        $drive = new GoogleDrive($client);

        $imported = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $fileId = $item['id'] ?? null;
            if (!$fileId) { $skipped++; continue; }

            $meta = $drive->files->get($fileId, ['fields' => 'id,name,mimeType,size']);
            if ($meta->mimeType === 'application/vnd.google-apps.folder') {
                $res = $this->importDriveFolderRecursive($drive, $meta->id, $targetFolderId);
                $imported += $res['imported'];
                $skipped += $res['skipped'];
            } else {
                $ok = $this->importDriveFile($drive, $meta->id, $targetFolderId, $meta->name);
                $ok ? $imported++ : $skipped++;
            }
        }

        return [
            'ok' => true,
            'importedCount' => $imported,
            'skippedCount' => $skipped,
        ];
    }

    private function importDriveFolderRecursiveOld(GoogleDrive $drive, string $driveFolderId, int $fotukaFolderId): array
    {
        $imported = 0;
        $skipped = 0;

        $pageToken = null;
        do {
            $resp = $drive->files->listFiles([
                'q' => sprintf("'%s' in parents and trashed = false", $driveFolderId),
                'fields' => 'nextPageToken, files(id,name,mimeType)',
                'pageSize' => 1000,
                'pageToken' => $pageToken,
            ]);

            foreach ($resp->getFiles() as $f) {
                if ($f->getMimeType() === 'application/vnd.google-apps.folder') {
                    $r = $this->importDriveFolderRecursive($drive, $f->getId(), $fotukaFolderId);
                    $imported += $r['imported'];
                    $skipped += $r['skipped'];
                } else {
                    $ok = $this->importDriveFile($drive, $f->getId(), $fotukaFolderId, $f->getName());
                    $ok ? $imported++ : $skipped++;
                }
            }

            $pageToken = $resp->getNextPageToken();
        } while (!empty($pageToken));

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importDriveFile(GoogleDrive $drive, string $fileId, int $fotukaFolderId, string $originalName): bool
    {
        try {
            // Download bytes
            $response = $drive->files->get($fileId, ['alt' => 'media']);
            $body = (string)$response->getBody();

            if ($body === '') return false;

            // Decide storage path / key
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $originalName);
            $localTmp = Yii::getAlias('@runtime') . '/gd_' . time() . '_' . $safeName;

            file_put_contents($localTmp, $body);

            // TODO: store into S3 or your existing storage pipeline.
            // Example if you already have a service:
            // $stored = Yii::$app->storage->putFile($localTmp, $safeName, $fotukaFolderId);

            // TODO: create Asset record the same way your upload pipeline does.
            // $asset = new Asset();
            // $asset->folder_id = $fotukaFolderId;
            // $asset->filename = $safeName;
            // $asset->status = Asset::STATUS_ACTIVE;
            // $asset->save(false);

            @unlink($localTmp);
            return true;
        } catch (\Throwable $e) {
            Yii::error("Google Drive import failed for $fileId: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private function buildGoogleDriveClientForUser(\common\models\User $user): ?\Google\Client
    {
        $accessToken = $user->getGoogleAccessToken();
        $refreshToken = $user->getGoogleRefreshToken();

        if (empty($accessToken) && empty($refreshToken)) {
            return null;
        }

        $cfg = Yii::$app->params['googleDrive'];

        $client = new \Google\Client();
        $client->setClientId($cfg['clientId']);
        $client->setClientSecret($cfg['clientSecret']);
        $client->setScopes($cfg['scopes']);

        // Set current access token if we have one
        if (!empty($accessToken)) {
            $client->setAccessToken(['access_token' => $accessToken]);
        }

        // If expired (or missing), refresh using refresh token and save back to DB
        if ($user->isGoogleAccessTokenExpired() && !empty($refreshToken)) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (isset($newToken['error'])) {
                // token revoked/invalid: clear stored tokens
                $user->setGoogleAccessToken(null);
                $user->setGoogleRefreshToken(null);
                $user->google_token_expires_at = null;
                $user->save(false);
                return null;
            }

            if (!empty($newToken['access_token'])) {
                $user->setGoogleAccessToken($newToken['access_token']);
            }
            if (!empty($newToken['expires_in'])) {
                $user->google_token_expires_at = time() + (int)$newToken['expires_in'];
            }

            // keep refresh token if Google doesn't return it again
            $user->save(false);

            $client->setAccessToken(['access_token' => $user->getGoogleAccessToken()]);
        }

        // If we still have no access token, we can't call Drive
        if (empty($user->getGoogleAccessToken())) {
            return null;
        }

        return $client;
    }

    public function actionImportGoogleDrive()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $targetFolderId = (int)Yii::$app->request->post('targetFolderId');
        error_log("TargetFolderId: " . $targetFolderId);
        $items = Yii::$app->request->post('items', []);
        error_log("Items: " . print_r($items, true));

        if (!$targetFolderId || !is_array($items) || empty($items)) {
            return ['ok' => false, 'error' => 'Missing targetFolderId or items'];
        }

        $customerId = Yii::$app->user->identity->customer_id;
        $userId = Yii::$app->user->id;
        $env = YII_ENV_DEV ? 'dev' : 'prod';

        // Build Google Drive client (DB-backed tokens)
        $client = $this->buildGoogleDriveClientForUser(Yii::$app->user->identity);
        if ($client === null) {
            return ['ok' => false, 'error' => 'Not connected to Google Drive'];
        }

        $drive = new \Google\Service\Drive($client);

        // AWS clients
        $s3 = $this->buildS3Client();
        $sqs = $this->buildSqsClient();
        $queueUrl = 'https://sqs.' . Yii::$app->params['AWS_REGION'] . '.amazonaws.com/191728941649/' . $env . '_processing';

        // Temp dir for this import request
        $tempBase = sys_get_temp_dir() . '/uploads';
        FileHelper::createDirectory($tempBase);
        $uniqueDir = uniqid('gd_import_' . $targetFolderId . '_', true);
        $uploadPath = $tempBase . '/' . $uniqueDir;
        FileHelper::createDirectory($uploadPath);

        $imported = 0;
        $skipped = 0;
        $assets = [];

        foreach ($items as $item) {
            error_log("Processing item: " . print_r($item, true));
            $driveId = $item['id'] ?? null;
            if (!$driveId) { $skipped++; continue; }

            // Get metadata
            try {
                $meta = $drive->files->get($driveId, [
                    'fields' => 'id,name,mimeType,size,shortcutDetails(targetId,targetMimeType)',
                    'supportsAllDrives' => true,
                ]);
                error_log("Drive meta:");
                error_log("ID: " . $meta->getId());
                error_log("Name: " . $meta->getName());
                error_log("Mime: " . $meta->getMimeType());
                error_log("Size: " . $meta->getSize());
            } catch (\Throwable $e) {
                Yii::error("Google Drive meta fetch failed for {$driveId}: " . $e->getMessage(), __METHOD__);
                error_log($e->getMessage());
                $skipped++;
                continue;
            }

            // If folder -> recursively import contents into target folder
            if ($meta->getMimeType() === 'application/vnd.google-apps.folder') {
                $res = $this->importDriveFolderRecursive(
                    $drive,
                    $s3,
                    $sqs,
                    $queueUrl,
                    $uploadPath,
                    $env,
                    $customerId,
                    $userId,
                    $meta->getId(),
                    $targetFolderId,
                    $meta->getName() // create a matching folder under target
                );
                $imported += $res['imported'];
                $skipped += $res['skipped'];
                $assets = array_merge($assets, $res['assets']);
                continue;
            }

            // Otherwise a file
            error_log("Calling importDriveFileToFotuka()");
            error_log("*** META SIZE 1: " . $meta->getSize());
            $res = $this->importDriveFileToFotuka(
                $drive,
                $s3,
                $sqs,
                $queueUrl,
                $uploadPath,
                $env,
                $customerId,
                $userId,
                $targetFolderId,
                $meta->getId(),
                $meta->getName(),
                $meta->getMimeType(),
                (int)($meta->getSize() ?? 0),
                '' // folderPath relative under target
            );

            if ($res['ok']) {
                $imported++;
                $assets[] = $res['asset'];
            } else {
                $skipped++;
            }
        }

        // Optional cleanup: you can remove the dir, but your File.tmp_location points inside it.
        // If your workers rely on tmp_location later, DO NOT delete here.
        // If workers do NOT rely on tmp_location (they use S3), it is safe to remove.
        // FileHelper::removeDirectory($uploadPath);

        return [
            'ok' => true,
            'importedCount' => $imported,
            'skippedCount' => $skipped,
            'assets' => $assets,
        ];
    }

    private function importDriveFolderRecursive(
        \Google\Service\Drive $drive,
        \Aws\S3\S3Client $s3,
        \Aws\Sqs\SqsClient $sqs,
        string $queueUrl,
        string $uploadPath,
        string $env,
        int $customerId,
        int $userId,
        string $driveFolderId,
        int $fotukaTargetFolderId,
        string $driveFolderName,
        string $relativeFolderPath = ''
    ): array
    {
        $imported = 0;
        $skipped = 0;
        $assets = [];

        // Create matching folder under target using ensureFolderPath
        $newRelativePath = trim($relativeFolderPath === '' ? $driveFolderName : ($relativeFolderPath . '/' . $driveFolderName), '/');

        // ensureFolderPath returns the Fotuka folder id for this folder path
        $thisFolderId = AssetController::ensureFolderPath($customerId, $userId, $fotukaTargetFolderId, $newRelativePath);

        $pageToken = null;
        do {
            $resp = $drive->files->listFiles([
                'q' => sprintf("'%s' in parents and trashed = false", $driveFolderId),
                'fields' => 'nextPageToken, files(id,name,mimeType,size)',
                'pageSize' => 1000,
                'pageToken' => $pageToken,
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
            ]);

            foreach ($resp->getFiles() as $f) {
                $mime = $f->getMimeType();

                if ($mime === 'application/vnd.google-apps.folder') {
                    $r = $this->importDriveFolderRecursive(
                        $drive,
                        $s3,
                        $sqs,
                        $queueUrl,
                        $uploadPath,
                        $env,
                        $customerId,
                        $userId,
                        $f->getId(),
                        $fotukaTargetFolderId,
                        $f->getName(),
                        $newRelativePath
                    );
                    $imported += $r['imported'];
                    $skipped += $r['skipped'];
                    $assets = array_merge($assets, $r['assets']);
                } else {
                    error_log("Calling importDriveFileToFotuka() - 2");
                    error_log("*** META SIZE 2: " . $f->getSize());
                    $r = $this->importDriveFileToFotuka(
                        $drive,
                        $s3,
                        $sqs,
                        $queueUrl,
                        $uploadPath,
                        $env,
                        $customerId,
                        $userId,
                        $thisFolderId,           // import into this specific created folder
                        $f->getId(),
                        $f->getName(),
                        $f->getMimeType(),
                        (int)($f->getSize() ?? 0),
                        '' // already resolved to folder id
                    );

                    if ($r['ok']) {
                        $imported++;
                        $assets[] = $r['asset'];
                    } else {
                        $skipped++;
                    }
                }
            }

            $pageToken = $resp->getNextPageToken();
        } while (!empty($pageToken));

        return ['imported' => $imported, 'skipped' => $skipped, 'assets' => $assets];
    }

    private function importDriveFileToFotuka(
        \Google\Service\Drive $drive,
        \Aws\S3\S3Client $s3,
        \Aws\Sqs\SqsClient $sqs,
        string $queueUrl,
        string $uploadPath,
        string $env,
        int $customerId,
        int $userId,
        int $targetFolderId,
        string $driveFileId,
        string $originalName,
        string $mimeType,
        int $declaredSize,
        string $folderPath
    ): array
    {

        error_log("---- importDriveFileToFotuka START ----");
        error_log("DriveFileId: {$driveFileId}");
        error_log("OriginalName: {$originalName}");
        error_log("MimeType: {$mimeType}");

        try {
            if ($originalName === '.DS_Store') {
                return ['ok' => false];
            }

            // If you passed folderPath (relative under target), create folders
            if ($folderPath !== '') {
                $folderPath = str_replace('\\', '/', $folderPath);
                $folderPath = ltrim($folderPath, '/');
                if (strpos($folderPath, '..') !== false) {
                    return ['ok' => false];
                }
                $targetFolderId = AssetController::ensureFolderPath($customerId, $userId, $targetFolderId, $folderPath);
            }

            // 1) Resolve Google Drive shortcuts
            if ($mimeType === 'application/vnd.google-apps.shortcut') {
                $shortcutMeta = $drive->files->get($driveFileId, [
                    'fields' => 'id,name,mimeType,shortcutDetails(targetId,targetMimeType)',
                    'supportsAllDrives' => true,
                ]);

                $sd = $shortcutMeta->getShortcutDetails();
                if (!$sd || !$sd->getTargetId()) {
                    Yii::warning("Shortcut has no targetId: {$driveFileId}", __METHOD__);
                    return ['ok' => false];
                }

                // Import the target instead
                $driveFileId = $sd->getTargetId();
                $mimeType = (string)$sd->getTargetMimeType();

                // Update name to shortcut name is optional; I prefer target name
                $targetMeta = $drive->files->get($driveFileId, [
                    'fields' => 'id,name,mimeType,size',
                    'supportsAllDrives' => true,
                ]);
                $originalName = $targetMeta->getName();
                $mimeType = $targetMeta->getMimeType();
                $declaredSize = (int)($targetMeta->getSize() ?? 0);
            }

            // 2) Download bytes (export for google-native, alt=media for normal files)
            $download = $this->downloadDriveFileBytes($drive, $driveFileId, $originalName, $mimeType);
            error_log("Download result: " . print_r($download, true));

            if (!$download['ok']) {
                Yii::warning("Drive download failed for {$driveFileId}: " . ($download['error'] ?? 'unknown'), __METHOD__);
                return ['ok' => false];
            }

            $bytes = $download['bytes'];
            $finalName = $download['filename'];         // may include new extension for exports
            $finalMime = $download['contentType'];      // correct ContentType for S3

            // 3) Write to temp file under unique uploadPath
            $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $finalName);
            $targetFile = $uploadPath . '/' . $safeName;
            file_put_contents($targetFile, $bytes);

            $fileSize = filesize($targetFile);

            // 4) Create File record (matches your upload pipeline)
            $file = new File();
            $file->customer_id = $customerId;
            $file->user_id = $userId;
            $file->type = File::TYPE_OTHER;
            $file->width = null;
            $file->height = null;
            $file->filename = $safeName;
            $file->extension = pathinfo($safeName, PATHINFO_EXTENSION);
            $file->orientation = null;
            $file->filesize = $fileSize;
            $file->pages = 0;
            $file->tmp_location = $targetFile;
            $res = $file->save();

            error_log("Temp file written to: " . $targetFile);
            error_log("Temp filesize: " . filesize($targetFile));
            error_log("File save result: " . ($res ? "OK" : "FAILED"));

            if (!$res) {
                Yii::error("File save failed: " . print_r($file->getErrors(), true), __METHOD__);
                return ['ok' => false];
            }

            // 5) Create Asset record (matches your upload pipeline)
            $asset = new Asset();
            $asset->folder_id = $targetFolderId;
            $asset->customer_id = $customerId;
            $asset->user_id = $userId;
            $asset->file_id = $file->id;
            $asset->title = $finalName; // exported name (docx/xlsx/pptx/pdf/etc.)
            $asset->thumbnail_state = Asset::THUMBNAIL_PENDING;
            $asset->preview_state = Asset::PREVIEW_PENDING;
            $asset->thumbnail_url = null;

            $res = $asset->save();

            error_log("Asset save result: " . ($res ? "OK" : "FAILED"));


            if (!$res) {
                Yii::error("Asset save failed: " . print_r($asset->getErrors(), true), __METHOD__);
                return ['ok' => false];
            }

            // 6) Update storage usage (same as upload action)
            $customer = $asset->customer;
            $customer->storage_used = $customer->storage_used + $fileSize;
            $customer->save(false);

            $folder = $asset->folder;
            $folder->storage_used = $folder->storage_used + $fileSize;
            $folder->save(false);

            // 7) Upload to S3: <env>/original/<customer_id>/<asset_id>
            $key = "{$env}/original/{$customerId}/{$asset->id}";

            $s3->putObject([
                'Bucket' => Yii::$app->params['AWS_BUCKET'],
                'Key' => $key,
                'SourceFile' => $targetFile,
                'ACL' => 'private',
                'CacheControl' => 'max-age=31536000',
                'ContentType' => $finalMime, // use export mime or sniffed mime
                'StorageClass' => 'INTELLIGENT_TIERING',
            ]);

            // 8) Enqueue SQS processing (same payload pattern)
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

            $sqs->sendMessage([
                'QueueUrl' => $queueUrl,
                'MessageBody' => json_encode($event, JSON_UNESCAPED_SLASHES),
                'MessageAttributes' => [
                    'eventType' => [
                        'DataType' => 'String',
                        'StringValue' => $event['type'],
                    ],
                ],
            ]);

            // IMPORTANT: you said worker deletes tmp_location, so we do NOT delete $targetFile here.

            return [
                'ok' => true,
                'asset' => [
                    'id' => (int)$asset->id,
                    'folder_id' => (int)$asset->folder_id,
                    'title' => (string)$asset->title,
                    'thumbnail_url' => $asset->thumbnail_url,
                    'thumbnail_state' => $asset->thumbnail_state,
                ],
            ];
        } catch (\Aws\Exception\AwsException $e) {
            Yii::error("Google import AWS error for {$driveFileId}: " . $e->getMessage(), __METHOD__);
            error_log("Returning ok=false at 1");
            return ['ok' => false];
        } catch (\Throwable $e) {
            Yii::error("Google import error for {$driveFileId}: " . $e->getMessage(), __METHOD__);
            error_log("Returning ok=false at 2");
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return ['ok' => false];
        }
    }

    private function buildS3Client(): \Aws\S3\S3Client
    {
        return new \Aws\S3\S3Client([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    private function buildSqsClient(): \Aws\Sqs\SqsClient
    {
        return new \Aws\Sqs\SqsClient([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key' => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    private function downloadDriveFileBytes(
        \Google\Service\Drive $drive,
        string $driveFileId,
        string $originalName,
        string $mimeType
    ): array
    {
        // Google-native types start with application/vnd.google-apps.
        $isGoogleNative = str_starts_with($mimeType, 'application/vnd.google-apps.');

        // If it's not a google-native file, download binary content directly
        if (!$isGoogleNative || $mimeType === 'application/vnd.google-apps.folder') {
            $resp = $drive->files->get($driveFileId, [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);
            $bytes = (string)$resp->getBody();
            if ($bytes === '') {
                return ['ok' => false, 'error' => 'empty body'];
            }

            // Use finfo to determine mime type from bytes after write would be ideal,
            // but since we return bytes, we can keep the source mimeType for S3.
            // (If you prefer, you can sniff it after writing the temp file like upload action does.)
            return [
                'ok' => true,
                'bytes' => $bytes,
                'filename' => $originalName,
                'contentType' => $mimeType ?: 'application/octet-stream',
            ];
        }

        // Google-native: choose an export target
        $export = $this->chooseGoogleExport($originalName, $mimeType);
        if (!$export) {
            // Some types may not support export (e.g., google-apps.form)
            return ['ok' => false, 'error' => 'unsupported google-native type for export: ' . $mimeType];
        }

        // Export
        $resp = $drive->files->export($driveFileId, $export['exportMime'], [
            'supportsAllDrives' => true,
        ]);

        $bytes = (string)$resp->getBody();
        if ($bytes === '') {
            return ['ok' => false, 'error' => 'empty export body'];
        }

        return [
            'ok' => true,
            'bytes' => $bytes,
            'filename' => $export['filename'],
            'contentType' => $export['exportMime'],
        ];
    }

    private function chooseGoogleExport(string $originalName, string $mimeType): ?array
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);

        switch ($mimeType) {
            case 'application/vnd.google-apps.document':
                return [
                    'exportMime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'filename' => $base . '.docx',
                ];

            case 'application/vnd.google-apps.spreadsheet':
                return [
                    'exportMime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'filename' => $base . '.xlsx',
                ];

            case 'application/vnd.google-apps.presentation':
                return [
                    'exportMime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'filename' => $base . '.pptx',
                ];

            case 'application/vnd.google-apps.drawing':
                // Drawing export options include image/png, image/jpeg, application/pdf, etc.
                return [
                    'exportMime' => 'image/png',
                    'filename' => $base . '.png',
                ];

            case 'application/vnd.google-apps.script':
                // Apps Script can export JSON (source)
                return [
                    'exportMime' => 'application/vnd.google-apps.script+json',
                    'filename' => $base . '.json',
                ];

            case 'application/vnd.google-apps.jam':
                // Jamboard: safest is PDF export
                return [
                    'exportMime' => 'application/pdf',
                    'filename' => $base . '.pdf',
                ];

            case 'application/vnd.google-apps.site':
                // Sites export is not like Docs; often not supported reliably via export
                return [
                    'exportMime' => 'application/pdf',
                    'filename' => $base . '.pdf',
                ];

            case 'application/vnd.google-apps.form':
                // Forms typically cannot be exported via files.export in a useful way
                return null;

            default:
                // For any other google-apps type: try PDF as a generic fallback
                return [
                    'exportMime' => 'application/pdf',
                    'filename' => $base . '.pdf',
                ];
        }
    }
}