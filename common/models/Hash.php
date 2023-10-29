<?php

namespace common\models;

use Exception;
use Yii;
use yii\helpers\ArrayHelper;

class Hash
{
    /** @var string|null */
    protected ?string $secret = null;

    /**
     * @param string $data
     * @return string Returns SHA-384 hash of the given data string.
     * @throws Exception
     */
    public function calculate(string $data): string
    {
        return hash_hmac('sha256', $data, $this->getSecretKey());
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getSecretKey(): string
    {
        return $this->secret ??= $this->getParam('feed.hash.secret');
    }

    /**
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    protected function getParam(string $path)
    {
        return ArrayHelper::getValue(Yii::$app->params, $path);
    }
}
