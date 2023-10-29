<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $pornstar_id
 * @property string $hash
 * @property string $types [jsonb]
 * @property string $url
 * @property string $cached
 * @property int $created_at
 * @property int|null $updated_at
 */
class Image extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'image';
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
    public function getPornstar()
    {
        return $this->hasOne(Pornstar::class, ['id' => 'pornstar_id'])
            ->inverseOf('images');
    }
}
