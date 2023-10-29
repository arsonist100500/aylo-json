<?php

namespace backend\models;

use common\models\WebStorage;
use yii\base\BaseObject;
use yii\helpers\Html;

class ImageDecorator extends BaseObject
{
    /**
     * @param Image $owner
     * @param array $config
     */
    public function __construct(protected Image $owner, array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getImg(): string
    {
        $url = $this->owner->getCacheUrl() ?? $this->owner->url;
        return Html::img($url);
    }
}
