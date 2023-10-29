<?php

use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\mutex\FileMutex;
use yii\queue\LogBehavior;
use yii\queue\redis\Queue;

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
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => '172.21.0.4',
            'database' => 0,

            // retry connecting after connection has timed out
            // yiisoft/yii2-redis >=2.0.7 is required for this.
            'retries' => 1,
        ],
        'queueDownload' => [
            'class' => Queue::class,
            'redis' => 'redis',
            'channel' => 'queueDownload',
            'as log' => LogBehavior::class,
        ],
        'queueImport' => [
            'class' => Queue::class,
            'redis' => 'redis',
            'channel' => 'queueImport',
            'as log' => LogBehavior::class,
        ],
        'queueImageCache' => [
            'class' => Queue::class,
            'redis' => 'redis',
            'channel' => 'queueImageCache',
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
