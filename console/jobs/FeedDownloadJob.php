<?php

namespace console\jobs;

use common\models\FileSystemStorage;
use common\models\StorageInterface;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\httpclient\Exception as HttpClientException;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FeedDownloadJob implements JobInterface
{
    /** @var string */
    public string $url;
    /** @var string */
    public string $path;
    /** @var string */
    public string $storage = FileSystemStorage::class;

    /**
     * @param Queue $queue
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    public function execute($queue)
    {
        Yii::info("downloading {$this->url}...", __METHOD__);
        $this->download($this->url, $this->path);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param string $url
     * @param string $path
     * @throws InvalidConfigException
     * @throws HttpClientException
     */
    protected function download(string $url, string $path)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        if ($response->getIsOk() === false) {
            throw new RuntimeException($response->getContent(), $response->getStatusCode());
        }

        $storage = $this->getStorage();
        $storage->put($path, $response->getContent());
    }

    /**
     * @return StorageInterface|object
     * @throws InvalidConfigException
     */
    protected function getStorage(): StorageInterface
    {
        return Instance::ensure($this->storage, StorageInterface::class);
    }
}
