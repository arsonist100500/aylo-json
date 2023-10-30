<?php

namespace console\jobs;

use common\models\feed\FeedSettings;
use common\models\FileSystemStorage;
use common\models\StorageInterface;
use console\jobs\import\FeedImportJob;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
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
    /** @var string|array|CacheInterface */
    public $cache = 'cache';
    /** @var string */
    public $storage = FileSystemStorage::class;
    /** @var string|array|Queue */
    public $queueImport = 'queueImport';

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
        $feedTimestamp = $this->getFeedTimestamp($url);
        $cache = $this->getCache();
        if ($this->isUpdated($cache, $feedTimestamp) === false) {
            Yii::debug("feed is not updated since the last download (ts {$feedTimestamp})", __METHOD__);
            return;
        }

        $client = new Client();
        $downloadTimestamp = time();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->send();

        if ($response->getIsOk() === false) {
            throw new RuntimeException($response->getContent(), $response->getStatusCode());
        }

        $storage = $this->getStorage();
        $storage->write($path, $response->getContent());

        $size = $storage->getSize($path);
        Yii::debug("size is {$size} bytes", __METHOD__);

        $cache->set($this->getCacheKeyDownloadTimestamp(), $downloadTimestamp);

        $settings = FeedSettings::instance();
        $job = new FeedImportJob();
        $job->path = $settings->getFilePath();
        $this->getImportQueue()->push($job);
    }

    /**
     * @param string $url
     * @return int|null
     * @throws HttpClientException
     * @throws InvalidConfigException
     */
    protected function getFeedTimestamp(string $url): ?int
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('HEAD')
            ->setUrl($url)
            ->send();

        if ($response->getIsOk() === false) {
            return null;
        }
        $headers = $response->getHeaders();
        $lastModified = $headers->get('last-modified');
        return strtotime($lastModified);
    }

    /**
     * @param CacheInterface $cache
     * @param int $feedTimestamp
     * @return bool
     */
    protected function isUpdated(CacheInterface $cache, int $feedTimestamp): bool
    {
        if (empty($feedTimestamp)) {
            return true;
        }

        $keyDownload = $this->getCacheKeyDownloadTimestamp();
        $downloadTimestamp = $cache->get($keyDownload);

        if (empty($downloadTimestamp)) {
            return true;
        }

        return $feedTimestamp >= $downloadTimestamp;
    }

    /**
     * @return string
     */
    protected function getCacheKeyDownloadTimestamp(): string
    {
        return 'feed.download.timestamp';
    }

    /**
     * @return CacheInterface|object
     * @throws InvalidConfigException
     */
    protected function getCache(): CacheInterface
    {
        return Instance::ensure($this->cache, CacheInterface::class);
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
     * @return Queue
     * @throws InvalidConfigException
     */
    public function getImportQueue(): Queue
    {
        if ($this->queueImport instanceof Queue === false) {
            $this->queueImport = Instance::ensure($this->queueImport, Queue::class);
        }
        return $this->queueImport;
    }
}
