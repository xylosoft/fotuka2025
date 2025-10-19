<?php
return [
    'name' => 'Fotuka',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false, // Set to false to remove index.php from URLs
            'enableStrictParsing' => false, // Set to true if you want to strictly enforce rules
            'rules' => [
                '/signup' => 'site/signup',
                '/contact' => 'site/contact',
                '/about' => 'site/about',
                '/login' => 'site/login',
                '/reset-password' => 'site/request-password-reset',
                '/resend-verification' => 'site/resend-verification-email',
                '/json/folders' => 'json/folders',
                'json/folder/<id:\d+>' => 'json/folder',
                'json/assets/<id:\d+>' => 'json/assets',
                '/folder/add' => 'folder/add',
                '/folder/move' => 'folder/move',
                '/folder/rename' => 'folder/rename',
                '/folders' => 'folder/folders',
            ],
        ],
    ],
];
