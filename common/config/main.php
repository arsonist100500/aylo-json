<?php

use yii\debug\Module as DebugModule;
use yii\gii\Module as GiiModule;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => [
        'fs' => [
            'class' => creocoder\flysystem\LocalFilesystem::class,
            'path' => '@storage',
        ],
        'db' => $db,
    ],
    'params' => $params,
];

return $config;
