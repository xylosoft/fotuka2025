<?php

$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

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
                // Site Actions
                '/signup' => 'site/signup',
                '/contact' => 'site/contact',
                '/about' => 'site/about',
                '/login' => 'site/login',
                '/logout' => 'site/logout',
                '/request-password-reset' => 'site/request-password-reset',
                '/reset-password' => 'site/reset-password',
                '/resend-verification' => 'site/resend-verification-email',

                // Folder / Asset Actions
                '/json/folders' => 'json/folders',
                '/json/folders/<id:\d+>' => 'json/folders',
                '/json/folder/<id:\d+>' => 'json/folder',
                '/json/assets/<folderId:\d+>' => 'json/assets',
                '/json/asset/<id:\d+>' => 'json/asset',
                '/json/pending/<folderId:\d+>/<assetIds[\d,]+>' => 'json/pending',
                '/folder/add' => 'folder/add',
                '/folder/move' => 'folder/move',
                '/folder/rename' => 'folder/rename',
                '/folders' => 'folder/folders',
                '/folder/<id:\d+>' => 'folder/folders',
                '/asset/upload/<id:\d+>' => 'asset/upload',

                // User Actions
                'profile' => 'user/profile',

            ],
        ],
        'authClientCollection' => [
            'class' => \yii\authclient\Collection::class,
            'clients' => [
                'google' => [
                    'class' => \yii\authclient\clients\Google::class,
                    'clientId' => $params['GOOGLE_CLIENT_ID'],
                    'clientSecret' => $params['GOOGLE_CLIENT_SECRET'],
                    'scope' => 'openid email profile',
                ],
            ],
        ],
    ],
];
