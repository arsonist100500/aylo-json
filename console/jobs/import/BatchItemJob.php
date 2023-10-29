<?php

namespace console\jobs\import;

use common\models\feed\item\ItemDto;
use common\models\feed\item\ItemThumbnailDto;
use common\models\Image;
use common\models\Pornstar;
use common\models\Hash;
use console\jobs\BatchCacheImageJob;
use JsonException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class BatchItemJob implements JobInterface
{
    /** @var ItemDto[] */
    public array $items;
    /** @var string|array|Queue */
    public $queueImageCache = 'queueImageCache';
    /** @var int */
    public int $batchSize = 10;

    /**
     * @param Queue $queue
     * @throws JsonException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function execute($queue)
    {
        if (empty($this->items)) {
            return;
        }

        $count = count($this->items);
        $firstId = reset($this->items)->id;
        $lastId = end($this->items)->id;
        Yii::info("importing {$count} items (id: {$firstId}..{$lastId})", __METHOD__);

        /** @var array<string,ItemDto> $items */
        $items = ArrayHelper::index($this->items, fn (ItemDto $dto) => $this->getItemHash($dto));
        /** @var string[] $hashes */
        $hashes = array_keys($items);

        /** @var Pornstar[] $pornstars */
        $pornstars = Pornstar::find()
            ->where([
                'hash' => $hashes,
            ])
            ->indexBy('hash')
            ->all();

        /** @var string[] $existingHashes */
        $existingHashes = array_keys($pornstars);

        $newHashes = array_diff($hashes, $existingHashes);

        $imageIds = [];

        // Import new items & update changed items
        foreach ($newHashes as $hash) {
            $item = $items[$hash];
            $model = $this->importItem($item);
            $ids = $this->saveImages($model, $item->getThumbnails());
            array_push($imageIds, ...$ids);
        }

        // Import images for existing (unchanged) items
        foreach ($pornstars as $hash => $model) {
            $item = $items[$hash];
            $ids = $this->saveImages($model, $item->getThumbnails());
            array_push($imageIds, ...$ids);
        }

        $this->cacheImages($imageIds, $this->batchSize);

        Yii::info("done", __METHOD__);
    }

    /**
     * @param ItemDto $dto
     * @return Pornstar
     * @throws JsonException
     */
    protected function importItem(ItemDto $dto): Pornstar
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
        return $model;
    }

    /**
     * @param ItemDto $dto
     * @return string
     * @throws JsonException
     */
    protected function getItemHash(ItemDto $dto): string
    {
        $aliases = $dto->getAliases();
        sort($aliases);

        $data = $dto->toArray();
        $data['aliases'] = $aliases;
        unset($data['thumbnails']);

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
    protected function saveImages(Pornstar $pornstar, array $thumbnails): array
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
     * @param int[] $ids
     * @param int $batchSize
     * @throws InvalidConfigException
     */
    protected function cacheImages(array $ids, int $batchSize): void
    {
        $imagesCount = 0;
        $jobsCount = 0;

        $batch = [];
        foreach ($ids as $id) {
            ++$imagesCount;

            if (count($batch) >= $batchSize) {
                ++$jobsCount;
                $this->pushCacheImageJob($batch);
                $batch = [];
            }

            $batch[] = $id;
        }

        if ($batch) {
            ++$jobsCount;
            $this->pushCacheImageJob($batch);
        }

        Yii::debug("created {$jobsCount} jobs to cache {$imagesCount} images", __METHOD__);
    }

    /**
     * @param array $ids
     * @throws InvalidConfigException
     */
    protected function pushCacheImageJob(array $ids): void
    {
        $job = new BatchCacheImageJob();
        $job->ids = $ids;
        $this->getImageCacheQueue()->push($job);
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
