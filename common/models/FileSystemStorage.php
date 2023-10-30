<?php

namespace common\models;

use creocoder\flysystem\Filesystem;
use yii\base\InvalidConfigException;
use yii\base\StaticInstanceTrait;
use yii\di\Instance;

class FileSystemStorage implements StorageInterface
{
    use StaticInstanceTrait;

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
     * @return false|mixed|string
     * @throws InvalidConfigException
     */
    public function read(string $id)
    {
        return $this->getFs()->read($id);
    }

    /**
     * @param string $id
     * @return false|mixed|resource
     * @throws InvalidConfigException
     */
    public function readStream(string $id)
    {
        return $this->getFs()->readStream($id);
    }

    /**
     * @param string $id
     * @return int
     * @throws InvalidConfigException
     */
    public function getSize(string $id): int
    {
        return $this->getFs()->getSize($id);
    }

    /**
     * @param string $id
     * @param $data
     * @return bool
     * @throws InvalidConfigException
     */
    public function write(string $id, $data): bool
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
