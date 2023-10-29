<?php

namespace common\models;

use common\models\feed\item\ItemDto;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $hash
 * @property string $name
 * @property string[] $aliases [jsonb]
 * @property string $license
 * @property int $wlStatus [smallint]
 * @property string $link [varchar(255)]
 * @property array $attributes [jsonb]
 * @property array $stats [jsonb]
 * @property string $created_at [integer]
 * @property string $updated_at [integer]
 *
 * @property Image[] $images
 */
class Pornstar extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return 'pornstar';
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Image::class, ['pornstar_id' => 'id'])
            ->inverseOf('pornstar');
    }

    /**
     * @param ItemDto $dto
     * @return static
     */
    public function loadFromDto(ItemDto $dto): static
    {
        $this->name = $dto->name;
        $this->license = $dto->license;
        $this->wlStatus = $dto->wlStatus;
        $this->link = $dto->link;
        $this->aliases = $dto->getAliases();

        $attributes = $dto->getAttributes()?->toArray();
        unset($attributes['stats']);
        $this->attributes = $attributes;

        $this->stats = $dto->getAttributes()?->getStats();

        return $this;
    }
}
