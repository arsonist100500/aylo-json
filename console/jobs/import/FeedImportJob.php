<?php

namespace console\jobs\import;

use common\models\feed\FeedParser;
use common\models\feed\item\ItemDto;
use common\models\FileSystemStorage;
use common\models\StorageInterface;
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
     */
    protected function importFeed(string $path, int $batchSize): void
    {
        $parser = $this->getFeedParser($path);

        $itemsCount = 0;
        $jobsCount = 0;

        do {
            $generator = $parser->getItems($batchSize);
            if ($generator->valid() === false) {
                break;
            }

            $batch = [];
            foreach ($generator as $item) {
                /** @var ItemDto $item */
                $batch[] = $item;
            }
            $itemsCount += count($batch);

            ++$jobsCount;
            $this->pushImportJob($batch);
        } while (true);

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
        $stream = $storage->readStream($path);
        $parser = new FeedParser();
        $parser->setStream($stream);
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
