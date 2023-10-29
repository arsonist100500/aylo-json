<?php

namespace console\jobs;

use common\models\Image;
use common\models\StorageInterface;
use common\models\WebStorage;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\Exception as HttpClientException;
use yii\httpclient\Response;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BatchCacheImageJob implements JobInterface
{
    /** @var int[] */
    public array $ids = [];
    /** @var string|array|StorageInterface */
    public $storage = WebStorage::class;

    /**
     * @param Queue $queue
     */
    public function execute($queue)
    {
        $ids = $this->ids;
        if (empty($ids)) {
            return;
        }

        $count = count($ids);
        $firstId = reset($ids);
        $lastId = end($ids);
        Yii::info("caching {$count} images (id: {$firstId}..{$lastId})", __METHOD__);

        $storage = $this->getStorage();
        foreach ($ids as $imageId) {
            Yii::info("caching image id {$imageId}...", __METHOD__);
            try {
                $this->cache($imageId, $storage);
            } catch (Throwable $e) {
                $message = "Failed to cache image id {$imageId}: " . $e->getMessage();
                Yii::error($message, __METHOD__);
            }
        }

        Yii::info("done", __METHOD__);
    }

    /**
     * @param int $imageId
     * @param StorageInterface $storage
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    protected function cache(int $imageId, StorageInterface $storage): void
    {
        $image = Image::findOne(['id' => $imageId]);
        if ($image === null) {
            throw new RuntimeException("Image not found (id {$imageId})");
        }

        if ($image->cached && $storage->exists($image->cached)) {
            Yii::info("image is already cached: {$image->cached}", __METHOD__);
            return;
        }

        $response = $this->download($image->url);

        $path = $image->getCachePath();
        $storage->write($path, $response->getContent());

        $size = $storage->getSize($path);
        Yii::debug("saved to {$path}, size is {$size} bytes", __METHOD__);

        $image->cached = $path;
        $image->save(false);
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
