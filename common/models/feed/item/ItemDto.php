<?php

namespace common\models\feed\item;

use common\models\DTObject;
use common\models\Hash;
use JsonException;
use yii\helpers\ArrayHelper;

class ItemDto extends DTObject
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $license;
    /** @var string|int */
    public $wlStatus;
    /** @var string */
    public $link;
    /** @var string[] */
    private array $aliases = [];
    /** @var ItemThumbnailDto[] */
    private array $thumbnails = [];
    /** @var ItemAttributesDto|null */
    private ?ItemAttributesDto $attributes = null;

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param string[] $aliases
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * @return ItemThumbnailDto[]
     */
    public function getThumbnails(): array
    {
        return $this->thumbnails;
    }

    /**
     * @param array[] $thumbnails
     */
    public function setThumbnails(array $thumbnails): void
    {
        $this->thumbnails = [];
        foreach ($thumbnails as $data) {
            $this->thumbnails[] = new ItemThumbnailDto($data);
        }
    }

    /**
     * @return ItemAttributesDto|null
     */
    public function getAttributes(): ?ItemAttributesDto
    {
        return $this->attributes;
    }

    /**
     * @param array[] $value
     */
    public function setAttributes(array $value): void
    {
        $this->attributes = new ItemAttributesDto($value);
    }
}
