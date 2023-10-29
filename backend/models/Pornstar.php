<?php

namespace backend\models;

use yii\db\ActiveQuery;

/**
 * @property Image[] $images
 */
class Pornstar extends \common\models\Pornstar
{
    public function attributeLabels()
    {
        return [
            'wlStatus' => 'WL Status',
            'decorator.aliases' => 'Aliases',
            'decorator.link' => 'Link',
            'decorator.smallLink' => 'Link',
            'decorator.images' => 'Images',

            'attributes.hairColor' => 'Hair color',
            'attributes.ethnicity' => 'Ethnicity',
            'attributes.tattoos' => 'Has tattoos',
            'attributes.piercings' => 'Has piercings',
            'attributes.breastSize' => 'Breast size',
            'attributes.breastType' => 'Breast type',
            'attributes.gender' => 'Gender',
            'attributes.orientation' => 'Orientation',
            'attributes.age' => 'Age',

        ];
    }

    /**
     * @return PornstarDecorator
     */
    public function getDecorator(): PornstarDecorator
    {
        return new PornstarDecorator($this);
    }

    /**
     * @return ActiveQuery
     */
    public function getImages()
    {
        $models = parent::getImages();
        $models->modelClass = Image::class;
        return $models;
    }
}