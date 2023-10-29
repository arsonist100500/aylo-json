<?php

namespace backend\models;

use common\models\WebStorage;
use yii\helpers\Url;

class Image extends \common\models\Image
{
    /**
     * @return ImageDecorator
     */
    public function getDecorator(): ImageDecorator
    {
        return new ImageDecorator($this);
    }

    /**
     * @return string|null
     */
    public function getCacheUrl(): ?string
    {
        if (empty($this->cached)) {
            return null;
        }
        return WebStorage::instance()->getUrl($this->cached);
    }
}