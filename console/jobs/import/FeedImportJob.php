<?php

namespace console\jobs\import;

use common\models\feed\FeedParser;
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
        $this->importFeed($this->path);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param string $path
     * @throws InvalidConfigException
     * @throws JsonException
     */
    protected function importFeed(string $path): void
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

        $itemsCount = 0;
        foreach ($parser->getItems() as $itemDto) {
            $job = new ItemJob();
            $job->itemDto = $itemDto;
            $this->getQueue()->push($job);
            ++$itemsCount;
        }
        Yii::debug("created {$itemsCount} jobs to import items", __METHOD__);
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
