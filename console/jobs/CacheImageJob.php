<?php

namespace console\jobs;

use common\models\FileSystemStorage;
use common\models\Image;
use common\models\StorageInterface;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\Exception as HttpClientException;
use yii\httpclient\Response;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CacheImageJob implements JobInterface
{
    /** @var int */
    public int $imageId;
    /** @var string */
    public string $storage = FileSystemStorage::class;

    /**
     * @param Queue $queue
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    public function execute($queue)
    {
        Yii::info("caching image id {$this->imageId}...", __METHOD__);
        $this->cache($this->imageId);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param int $imageId
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    protected function cache(int $imageId): void
    {
        $image = Image::findOne(['id' => $imageId]);
        if ($image === null) {
            throw new RuntimeException("Image not found (id {$imageId})");
        }

        $storage = $this->getStorage();
        if ($image->cached && $storage->has($image->cached)) {
            Yii::info("image is already cached: {$image->cached}", __METHOD__);
            return;
        }

        $response = $this->download($image->url);

        $path = $image->getCachePath();
        $storage->write($path, $response->getContent());

        $image->cached = $path;
        $image->save(false);

        $size = $storage->getSize($path);
        Yii::debug("saved to {$path}, size is {$size} bytes", __METHOD__);
    }

    /**
     * @return StorageInterface|object
     * @throws InvalidConfigException
     */
    protected function getStorage(): StorageInterface
    {
        return Instance::ensure($this->storage, StorageInterface::class);
    }

    /**
     * @param string $url
     * @return Response
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    protected function download(string $url): Response
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        if ($response->getIsOk() === false) {
            throw new RuntimeException($response->getContent(), $response->getStatusCode());
        }
        return $response;
    }
}
