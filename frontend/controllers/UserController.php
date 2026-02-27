<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use Aws\CloudFront\CloudFrontClient;

use Aws\S3\S3Client;

use frontend\models\ProfileForm;
use frontend\models\PasswordForm;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['profile'],
                'rules' => [
                    [
                        'actions' => ['profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionProfile()
    {
        $this->layout = "folder";
        $user = Yii::$app->user->identity;

        $profileForm = new ProfileForm();
        $profileForm->loadFromUser($user);

        $passwordForm = new PasswordForm($user);

        $request = Yii::$app->request;

        // Distinguish which form posted using a hidden field "formType"
        if ($request->isPost) {
            $formType = $request->post('formType');

            if ($formType === 'profile') {
                if ($profileForm->load($request->post()) && $profileForm->validate()) {

                    // 1) Save username/email
                    $profileForm->saveToUser($user);

                    // 2) If avatar cropped exists, upload to S3 and update profile_picture
                    if (!empty($profileForm->avatarCropped)) {
                        $user->profile_picture = $this->uploadCroppedAvatarToS3($profileForm->avatarCropped, $user);
                        $user->save(false, ['profile_picture']);
                    }

                    Yii::$app->session->setFlash('success', 'Profile updated.');
                    return $this->refresh();
                }
            }

            if ($formType === 'password') {
                if ($passwordForm->load($request->post()) && $passwordForm->validate()) {
                    $passwordForm->changePassword();
                    Yii::$app->session->setFlash('success', 'Password changed.');
                    return $this->refresh();
                }
            }
        }

        return $this->render('profile', [
            'user' => $user,
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    /**
     * Uploads a base64-cropped image to S3.
     * Returns the S3 key.
     */
    private function uploadCroppedAvatarToS3(string $dataUrl, $user): string
    {
        $env = YII_ENV_DEV ? 'dev' : 'prod';

        // Expected format: data:image/jpeg;base64,...
        if (!preg_match('#^data:image/(png|jpeg|jpg);base64,#i', $dataUrl, $m)) {
            throw new BadRequestHttpException('Invalid image data.');
        }

        $ext = strtolower($m[1]);
        if ($ext === 'jpg') {
            $ext = 'jpeg';
        }

        $base64 = preg_replace('#^data:image/(png|jpeg|jpg);base64,#i', '', $dataUrl);
        $raw = base64_decode($base64, true);

        if ($raw === false) {
            throw new BadRequestHttpException('Could not decode image.');
        }

        // Safety limit: ~8MB raw
        if (strlen($raw) > 8 * 1024 * 1024) {
            throw new BadRequestHttpException('Image is too large.');
        }

        // Create unique key
        $timestamp = gmdate('Ymd_His');
        $key = "{$env}/profile/{$user->id}." . ($ext === 'png' ? 'png' : 'jpg');

        $s3 = new S3Client([
            'region' => Yii::$app->params['AWS_REGION'],
            'version' => 'latest',
            'credentials' => [
                'key'    => Yii::$app->params['AWS_ACCESS_KEY_ID'],
                'secret' => Yii::$app->params['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);

        $contentType = ($ext === 'png') ? 'image/png' : 'image/jpeg';

        error_log("Putting file to S3...");
        $s3->putObject([
            'Bucket' => Yii::$app->params['AWS_BUCKET'],
            'Key' => $key,
            'Body' => $raw,
            'ContentType' => $contentType,
            'CacheControl' => 'max-age=31536000',
            'StorageClass' => 'INTELLIGENT_TIERING',
        ]);
        $this->invalidateCloudFront('/' . $env . '/profile/' . $user->id . '.' . ($ext === 'png' ? 'png' : 'jpg'));

        return Yii::$app->params['CLOUDFRONT_URL'] . '/' . $env . '/profile/' . $user->id . "." . ($ext === 'png' ? 'png' : 'jpg') ;
    }

    /**
     * Helper: if you store S3 key, build a URL for rendering.
     * You can move this to User model later.
     */
    public static function s3PublicUrl(string $key): string
    {
        $bucket = 'YOUR_BUCKET_NAME';
        $region = 'us-east-1';

        // Virtual-hosted style URL (common):
        return "https://{$bucket}.s3.{$region}.amazonaws.com/{$key}";
    }

    private function invalidateCloudFront(string $path): void
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