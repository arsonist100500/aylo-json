<?php

namespace console\jobs\import;

use common\models\feed\item\ItemDto;
use common\models\feed\item\ItemThumbnailDto;
use common\models\Image;
use common\models\Pornstar;
use common\models\Hash;
use console\jobs\CacheImageJob;
use JsonException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class ItemJob implements JobInterface
{
    /** @var ItemDto */
    public ItemDto $itemDto;
    /** @var string|array|Queue */
    public $queueImageCache = 'queueImageCache';

    /**
     * @param Queue $queue
     * @throws JsonException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function execute($queue)
    {
        Yii::info("importing item id {$this->itemDto->id}...", __METHOD__);
        $this->importItem($this->itemDto);
        Yii::info("done", __METHOD__);
    }

    /**
     * @param ItemDto $dto
     * @throws JsonException
     * @throws StaleObjectException
     * @throws Throwable
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

        $imageIds = $this->importImages($model, $dto->getThumbnails());
        $queue = $this->getImageCacheQueue();
        foreach ($imageIds as $id) {
            $job = new CacheImageJob();
            $job->imageId = $id;
            $queue->push($job);
        }
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
     * @return int[] Returns array of created image IDs.
     * @throws StaleObjectException
     * @throws Throwable
     */
    protected function importImages(Pornstar $pornstar, array $thumbnails): array
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

        $imageIds = [];
        foreach ($new as $hash) {
            $items = $thumbnailsByHash[$hash];
            $types = ArrayHelper::getColumn($items, 'type');
            $first = reset($items);
            $image = $this->createImage($pornstar->id, $hash, $types, $first->getFirstUrl());
            $image->save(false);
            $imageIds[] = $image->id;
        }
        return $imageIds;
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
     * @return Image
     */
    protected function createImage(int $pornstarId, string $hash, array $types, string $url): Image
    {
        $image = new Image();
        $image->pornstar_id = $pornstarId;
        $image->hash = $hash;
        $image->types = $types;
        $image->url = $url;
        return $image;
    }

    /**
     * @return Queue
     * @throws InvalidConfigException
     */
    protected function getImageCacheQueue(): Queue
    {
        if ($this->queueImageCache instanceof Queue === false) {
            $this->queueImageCache = Instance::ensure($this->queueImageCache, Queue::class);
        }
        return $this->queueImageCache;
    }
}
