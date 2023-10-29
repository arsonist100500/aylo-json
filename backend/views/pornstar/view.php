<?php
/**
 * @var $this yii\web\View
 * @var $model Pornstar
 */

use backend\models\Pornstar;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Pornstars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="pornstar-view">

    <div class="panel-header">
        <div class="panel-header-title">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'decorator.aliases:raw',
                    'license',
                    'wlStatus:boolean',
                    'decorator.link:raw',

                    'attributes.hairColor',
                    'attributes.ethnicity',
                    'attributes.tattoos:boolean',
                    'attributes.piercings:boolean',
                    'attributes.breastSize',
                    'attributes.breastType',
                    'attributes.gender',
                    'attributes.orientation',
                    'attributes.age',

                    'decorator.images:raw',
                    'created_at:datetime',
                    'created_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

</div>
