<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = $this->title = Yii::t('app', 'Views');

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-12">
        <?php
        $this->beginBlock('add-button');
        ?>
        <?=
        \yii\helpers\Html::a(
            Icon::show('plus') . Yii::t('app', 'Add'),
            ['/backend/view/add', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
            ['class' => 'btn btn-success']
        )
        ?>
        <?= \app\backend\widgets\RemoveAllButton::widget([
            'url' => '/backend/view/remove-all',
            'gridSelector' => '.grid-view',
            'htmlOptions' => [
                'class' => 'btn btn-danger pull-right'
            ],
        ]); ?>
        <?php
        $this->endBlock();
        ?>
        <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'views-grid',
            ],
            'columns' => [
                [
                    'class' => \kartik\grid\CheckboxColumn::className(),
                    'options' => [
                        'width' => '10px',
                    ],
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                'name',
                'view',
                'category',
                'internal_name',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'options' => [
                        'width' => '95px',
                    ],
                ],
            ],
            'theme' => 'panel-default',
            'gridOptions'=>[
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover'=>true,
                'panel'=>[
                    'heading'=>'<h3 class="panel-title">'.$this->title.'</h3>',
                    'after' => $this->blocks['add-button'],
                ],
            ]
        ]);
        ?>
    </div>
</div>
