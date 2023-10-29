<?php

namespace console\jobs;

use common\models\feed\FeedParser;
use common\models\feed\item\ItemDto;
use common\models\FileSystemStorage;
use common\models\Pornstar;
use common\models\Hash;
use common\models\StorageInterface;
use JsonException;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class FeedImportJob implements JobInterface
{
    /** @var string */
    public string $path;
    /** @var string */
    public string $storage = FileSystemStorage::class;

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
        $data = $storage->get($path);

        if (empty($data)) {
            throw new RuntimeException('Empty feed');
        }

        $size = strlen($data);
        Yii::debug("size: {$size} bytes", __METHOD__);

        $parser = new FeedParser();
        $parser->setData($data);
        foreach ($parser->getItems() as $itemDto) {
            Yii::debug("importing item id {$itemDto->id}", __METHOD__);
            $this->importItem($itemDto);
        }
    }

    /**
     * @param ItemDto $dto
     * @throws JsonException
     */
    protected function importItem(ItemDto $dto): void
    {
        /** @var Pornstar|null $model */
        $model = Pornstar::findOne(['id' => $dto->id]);

        if ($model === null) {
            $model = new Pornstar();
            $model->id = $dto->id;
        }

        $hash = $this->getItemHash($dto);
        if ($model->hash !== $hash) {
            $model->loadFromDto($dto);
            $model->hash = $hash;
        }

        $model->save(false);
    }

    /**
     * @param ItemDto $dto
     * @return string
     * @throws JsonException
     */
    protected function getItemHash(ItemDto $dto): string
    {
        $aliases = $dto->getAliases();
        $thumbnails = $dto->getThumbnails();
        sort($aliases);
        ArrayHelper::multisort($thumbnails, ['type', 'height', 'width'], [SORT_ASC, SORT_ASC, SORT_ASC]);

        $data = $dto->toArray();
        $data['aliases'] = $aliases;
        $data['thumbnails'] = $thumbnails;

        $dataAsString = json_encode($data, JSON_THROW_ON_ERROR);
        return (new Hash())->calculate($dataAsString);
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
