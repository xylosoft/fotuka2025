<?php

namespace common\classes;

use Yii;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class SiteHelper
{
    public static function DownloadGoogleProfile($user, $profile_url)
    {
        $env = YII_ENV_DEV ? 'dev' : 'prod';

        try {
            if (empty($profile_url) || !is_string($profile_url)) {
                return null;
            }

            $parts = parse_url($profile_url);
            if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
                return null;
            }

            $tmpPath = '/tmp/' . 'google_profile_' . $user->id . '_' . time();

            $download = self::httpDownloadToFile($profile_url, $tmpPath);

            if (!$download['ok']) {
                @unlink($tmpPath);
                return null;
            }

            $mime = $download['mime']?$download['mime']:'image/jpeg';
            $ext  = self::mimeToExt($mime)?self::mimeToExt($mime):'jpg';
            $key = "{$env}/profile/{$user->id}.{$ext}";

            $s3 = new S3Client([
                'region' => Yii::$app->params['AWS_REGION'],
                'version' => 'latest',
                'credentials' => [
                    'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                    'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
                ],
            ]);

            $s3->putObject([
                'Bucket' => Yii::$app->params['AWS_BUCKET'],
                'Key'         => $key,
                'SourceFile'  => $tmpPath,
                'ContentType' => $mime,
                'StorageClass' => 'INTELLIGENT_TIERING',
            ]);
            unlink($tmpPath);

            $user->profile_picture = Yii::$app->params['CLOUDFRONT_URL'] . '/' . $env . '/profile/' . $user->id . "." . $ext;
            $user->save();

            return true;
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return false;
        }
    }

    private static function httpDownloadToFile(string $url, string $path)
    {
        $ch = curl_init($url);
        $fp = fopen($path, 'wb');

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FAILONERROR => true,
        ]);

        $success = curl_exec($ch);
        $error   = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if (!$success) {
            @unlink($path);
            return ['ok' => false, 'error' => 'Download failed: ' . $error];
        }

        $mime = self::detectMime($path);

        if (!self::isAllowedImageMime($mime)) {
            unlink($path);
            return ['ok' => false, 'error' => 'Invalid image type'];
        }

        return ['ok' => true, 'mime' => $mime];
    }

    private static function detectMime($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mime ?: null;
    }

    private static function isAllowedImageMime($mime)
    {
        return in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ], true);
    }

    private static function mimeToExt($mime)
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => null,
        };
    }

    public static function invalidateCloudFront(string $path)
    {
        $cloudFront = new CloudFrontClient([
            'version' => 'latest',
            'region'  => Yii::$app->params['AWS_REGION'],
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $cloudFront->createInvalidation([
            'DistributionId' => Yii::$app->params['CLOUDFRONT_DISTRIBUTION_ID'],
            'InvalidationBatch' => [
                'Paths' => [
                    'Quantity' => 1,
                    'Items' => [$path],  // must start with /
                ],
                'CallerReference' => (string) time() . '-' . uniqid(),
            ],
        ]);
    }

}