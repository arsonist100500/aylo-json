<?php

namespace common\models;

use creocoder\flysystem\Filesystem;
use creocoder\flysystem\LocalFilesystem;
use yii\helpers\Url;

class WebStorage extends FileSystemStorage
{
    /**
     * @var string|array|Filesystem
     */
    public $fs = [
        'class' => LocalFilesystem::class,
        'path' => '@webStorage',
    ];

    /**
     * @param string $id
     * @return string
     */
    public function getUrl(string $id): string
    {
        return Url::to('@web/storage/' . $id);
    }
}
