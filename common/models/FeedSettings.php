<?php

namespace common\models;

use Exception;
use Yii;
use yii\base\StaticInstanceTrait;
use yii\helpers\ArrayHelper;

class FeedSettings
{
    use StaticInstanceTrait;

    /**
     * @return string
     * @throws Exception
     */
    public function getUrl(): string
    {
        return $this->getParam('feed.url');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFilePath(): string
    {
        return Yii::getAlias($this->getParam('feed.filePath'));
    }

    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected function getParam(string $path)
    {
        return ArrayHelper::getValue(Yii::$app->params, $path);
    }
}