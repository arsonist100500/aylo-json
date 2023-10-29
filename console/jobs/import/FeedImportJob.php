<?php

namespace console\jobs\import;

use common\models\feed\FeedParser;
use common\models\feed\item\ItemDto;
use common\models\FileSystemStorage;
use common\models\StorageInterface;
use JsonException;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FeedImportJob implements JobInterface
{
    /** @var string */
    public string $path;
    /** @var int */
    public int $batchSize = 50;
    /** @var string */
    public string $storage = FileSystemStorage::class;
    /** @var string|array|Queue */
    public $queue = 'queueImport';

    /**
     * @param Queue $queue
     * @throws InvalidConfigException
     * @throws JsonException
     */
    public function execute($queue)
    {
        Yii::info("importing feed {$this->path}...", __METHOD__);
        $this->importFeed($this->path, $this->batchSize);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param string $path
     * @param int $batchSize
     * @throws InvalidConfigException
     * @throws JsonException
     */
    protected function importFeed(string $path, int $batchSize): void
    {
        $parser = $this->getFeedParser($path);

        $itemsCount = 0;
        $jobsCount = 0;

        $batch = [];
        foreach ($parser->getItems() as $itemDto) {
            ++$itemsCount;

            if (count($batch) >= $batchSize) {
                ++$jobsCount;
                $this->pushImportJob($batch);
                $batch = [];
            }

            $batch[] = $itemDto;
        }

        if ($batch) {
            ++$jobsCount;
            $this->pushImportJob($batch);
        }

        Yii::debug("created {$jobsCount} jobs to import {$itemsCount} items", __METHOD__);
    }

    /**
     * @param string $path
     * @return FeedParser
     * @throws InvalidConfigException
     */
    protected function getFeedParser(string $path): FeedParser
    {
        $storage = $this->getStorage();
        $data = $storage->read($path);

        if (empty($data)) {
            throw new RuntimeException('Empty feed');
        }

        $size = strlen($data);
        Yii::debug("size: {$size} bytes", __METHOD__);

        $parser = new FeedParser();
        $parser->setData($data);
        return $parser;
    }

    /**
     * @param ItemDto[] $batch
     * @throws InvalidConfigException
     */
    protected function pushImportJob(array $batch): void
    {
        $job = new BatchItemJob();
        $job->items = $batch;
        $this->getQueue()->push($job);
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
    public function getQueue(): Queue
    {
        if ($this->queue instanceof Queue === false) {
            $this->queue = Instance::ensure($this->queue, Queue::class);
        }
        return $this->queue;
    }
}
