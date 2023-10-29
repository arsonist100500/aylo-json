<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Expression;

class PornstarSearch extends Model
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $license;
    /** @var Pornstar */
    private Pornstar $model;

    /**
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id'], 'trim'],
            [['id'], 'integer'],

            [['name'], 'string'],

            [['license'], 'string'],
        ];
    }

    /**
     * @return DataProviderInterface
     */
    public function search()
    {
        if ($this->validate() === false) {
            return new ArrayDataProvider();
        }

        $query = Pornstar::find();

        $query->andFilterWhere([
            'id' => $this->id,
            'license' => $this->license,
        ]);

        if ($this->name) {
            $query->andWhere(['or',
                ['ilike', 'name', $this->name],
                //// ['@>', '[[aliases]]::jsonb', $this->name], // todo store aliases in a separate table
            ]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ]
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return $this->getModel()->attributeLabels();
    }

    /**
     * @return Pornstar
     */
    protected function getModel(): Pornstar
    {
        return $this->model ??= Pornstar::instance();
    }
}