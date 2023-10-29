<?php

namespace console\jobs;

use common\models\feed\FeedParser;
use common\models\feed\item\ItemDto;
use common\models\feed\item\ItemThumbnailDto;
use common\models\FileSystemStorage;
use common\models\Image;
use common\models\Pornstar;
use common\models\Hash;
use common\models\StorageInterface;
use JsonException;
use RuntimeException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
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

        $this->importImages($model, $dto->getThumbnails());
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
        return Hash::instance()->calculate($dataAsString);
    }

    /**
     * @param Pornstar $pornstar
     * @param ItemThumbnailDto[] $thumbnails
     * @throws Throwable
     * @throws StaleObjectException
     */
    protected function importImages(Pornstar $pornstar, array $thumbnails): void
    {
        /** @var array<string,Image> $images */
        $images = ArrayHelper::index($pornstar->images, 'hash');
        $existingHashes = array_keys($images);

        /** @var array<string,ItemThumbnailDto[]> $thumbnailsByHash */
        $thumbnailsByHash = ArrayHelper::index($thumbnails, null, fn (ItemThumbnailDto $dto) => $this->getThumbnailHash($dto));
        $thumbnailHashes = array_keys($thumbnailsByHash);

        $outdated = array_diff($existingHashes, $thumbnailHashes);
        $new = array_diff($thumbnailHashes, $existingHashes);

        foreach ($outdated as $hash) {
            $image = $images[$hash];
            $image->delete();
        }

        foreach ($new as $hash) {
            $items = $thumbnailsByHash[$hash];
            $types = ArrayHelper::getColumn($items, 'type');
            $first = reset($items);
            $this->importImage($pornstar->id, $hash, $types, $first->getFirstUrl());
        }
    }

    /**
     * @param ItemThumbnailDto $dto
     * @return string
     * @throws JsonException
     */
    protected function getThumbnailHash(ItemThumbnailDto $dto): string
    {
        $dataAsString = json_encode($dto->getFirstUrl(), JSON_THROW_ON_ERROR);
        return Hash::instance()->calculate($dataAsString);
    }

    /**
     * @param int $pornstarId
     * @param string $hash
     * @param array $types
     * @param string $url
     */
    protected function importImage(int $pornstarId, string $hash, array $types, string $url): void
    {
        $image = new Image();
        $image->pornstar_id = $pornstarId;
        $image->hash = $hash;
        $image->types = $types;
        $image->url = $url;
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
}
