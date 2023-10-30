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
    public int $batchSize = 500;
    /** @var int Approximate amount of seconds after which the job will be restarted */
    public int $ttl = 30;
    /** @var int Amount of items to skip */
    public int $skippedItems = 0;
    /** @var string */
    public string $storage = FileSystemStorage::class;
    /** @var string|array|Queue */
    public $queue = 'queueImport';

    /** @var int */
    protected int $startedAt;

    /**
     * @param Queue $queue
     * @throws InvalidConfigException
     */
    public function execute($queue)
    {
        $this->startedAt = time();
        Yii::info("importing feed {$this->path}, skipped items = {$this->skippedItems}", __METHOD__);
        $this->importFeed($this->path, $this->skippedItems, $this->batchSize);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param string $path
     * @param int $skippedItems
     * @param int $batchSize
     * @throws InvalidConfigException
     */
    protected function importFeed(string $path, int $skippedItems, int $batchSize): void
    {
        $parser = $this->getFeedParser($path);
        $parser->setSkippedItemsCount($skippedItems);

        $itemsCount = 0;
        $jobsCount = 0;

        do {
            $batch = $this->getItemsBatch($parser, $batchSize);
            if (empty($batch)) {
                break;
            }

            $this->pushImportJob($batch);
            $itemsCount += count($batch);
            ++$jobsCount;
            Yii::debug("total jobs: {$jobsCount}, total items: {$itemsCount}", __METHOD__);

            if ($itemsCount > 0 && $this->isTimeout()) {
                // Restart the job to prevent exceeding the process ttl.
                $job = clone $this;
                $job->skippedItems += $itemsCount;
                $this->getQueue()->push($job);
                return;
            }
        } while (true);
    }

    /**
     * @param FeedParser $parser
     * @param int $batchSize
     * @return ItemDto[]
     */
    protected function getItemsBatch(FeedParser $parser, int $batchSize): array
    {
        $generator = $parser->getItems($batchSize);
        if ($generator->valid() === false) {
            return [];
        }

        $batch = [];
        foreach ($generator as $item) {
            $batch[] = $item;
        }
        return $batch;
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
     * @return bool
     */
    protected function isTimeout(): bool
    {
        $elapsed = time() - $this->startedAt;
        Yii::debug("time elapsed: {$elapsed} seconds");
        if ($elapsed > $this->ttl) {
            Yii::info('timeout');
            return true;
        }
        return false;
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
