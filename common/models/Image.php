<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

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
