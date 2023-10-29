<?php

use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\mutex\FileMutex;
use yii\queue\file\Queue;
use yii\queue\LogBehavior;

$config = [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'bootstrap' => [
        'log',
        'queueDownload',
        'queueImport',
        'queueImageCache',
    ],
    'components' => [
        'cache' => [
            'class' => FileCache::class,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['trace', 'info', 'warning', 'error'],
                    'microtime' => true,
                    'logFile' => '@console/runtime/logs/trace.log',
                    'maxFileSize' => 1024,
                ]
            ],
        ],
        'mutex' => [
            'class' => FileMutex::class,
        ],
        'queueDownload' => [
            'class' => Queue::class, // todo redis
            'path' => '@runtime/queue/download',
            'as log' => LogBehavior::class,
        ],
        'queueImport' => [
            'class' => Queue::class, // todo redis
            'path' => '@runtime/queue/import',
            'as log' => LogBehavior::class,
        ],
        'queueImageCache' => [
            'class' => Queue::class, // todo redis
            'path' => '@runtime/queue/imageCache',
            'as log' => LogBehavior::class,
        ],
    ],
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

return $config;
