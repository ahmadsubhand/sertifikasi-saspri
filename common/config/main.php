<?php

return [
    'language' => 'id-ID',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
    ],
];
