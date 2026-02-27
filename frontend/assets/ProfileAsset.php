<?php
namespace frontend\assets;

use yii\web\AssetBundle;

class ProfileAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        // CropperJS CDN
        'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css',
        'css/profile.css',
    ];

    public $js = [
        // CropperJS CDN
        'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js',
        'js/profile.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        // If you're using Bootstrap 5 via yii\bootstrap5 in your app, keep this:
        'yii\bootstrap5\BootstrapAsset',
    ];
}