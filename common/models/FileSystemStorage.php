<?php

namespace common\models;

use creocoder\flysystem\Filesystem;
use yii\base\InvalidConfigException;
use yii\di\Instance;

class FileSystemStorage implements StorageInterface
{
    /**
     * @var string|array|Filesystem
     */
    public $fs = 'fs';

    /**
     * @param string $id
     * @return bool
     * @throws InvalidConfigException
     */
    public function exists(string $id): bool
    {
        return $this->getFs()->has($id);
    }

    /**
     * @param string $id
     * @return \League\Flysystem\Handler|mixed
     * @throws InvalidConfigException
     */
    public function get(string $id)
    {
        return $this->getFs()->get($id);
    }

    /**
     * @param string $id
     * @param $data
     * @return bool
     * @throws InvalidConfigException
     */
    public function put(string $id, $data): bool
    {
        return $this->getFs()->put($id, $data);
    }

    /**
     * @return Filesystem
     * @throws InvalidConfigException
     */
    protected function getFs()
    {
        if ($this->fs instanceof Filesystem === false) {
            $this->fs = Instance::ensure($this->fs, Filesystem::class);
        }
        return $this->fs;
    }
}
