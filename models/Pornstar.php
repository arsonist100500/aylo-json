<?php

namespace app\models;

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
     * @return string
     */
    public static function tableName(): string
    {
        return 'pornstar';
    }

    /**
     * @return ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Image::class, ['pornstar_id' => 'id'])
            ->inverseOf('pornstar');
    }
}
