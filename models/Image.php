<?php

namespace app\models;

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
     * @return ActiveQuery
     */
    public function getPornstar()
    {
        return $this->hasOne(Pornstar::class, ['id' => 'pornstar_id'])
            ->inverseOf('images');
    }
}
