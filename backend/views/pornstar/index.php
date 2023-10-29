<?php
/**
 * @var $this yii\web\View
 * @var $searchModel backend\models\PornstarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;

$this->title = 'Pornstars';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pornstar-index">

    <div class="panel-header">
        <div class="panel-header-title">
            <h1 ><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'format' => 'raw',
                'value' => 'decorator.id', /** @see \backend\models\PornstarDecorator::getId() */
                'headerOptions' => ['style' => 'width: 1%;']
            ],
            'name',
            'license',
            'wlStatus:boolean',
            'decorator.smallLink:raw', /** @see \backend\models\PornstarDecorator::getSmallLink() */

            'attributes.hairColor',
            'attributes.ethnicity',
            'attributes.tattoos:boolean',
            'attributes.piercings:boolean',
            'attributes.breastSize',
            'attributes.breastType',
            'attributes.gender',
            'attributes.orientation',
            'attributes.age',

            [
                'class' => ActionColumn::class,
                'template' => '{view}',
            ],
        ],
    ]); ?>
</div>
