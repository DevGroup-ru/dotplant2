<?php

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Wysiwygs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wysiwyg-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DynaGrid::widget([
        'options' => [
            'id' => 'backend-wysiwyg-grid',
        ],
        'columns' => [
            'id',
            'name',
            'class_name',
            // 'configuration_view',

            [
                'class' => ActionColumn::className(),
                'options' => [
                    'width' => '95px',
                ],
                'buttons' => [
                    [
                        'url' => 'update',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'Edit'),
                    ],
                    [
                        'url' => 'delete',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => Yii::t('app', 'Delete'),
                    ],
                ],
            ],
        ],
        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [

                'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                'after' => Html::a(
                    Icon::show('plus') . Yii::t('app', 'Add'),
                    [
                        '/core/backend-wysiwyg/create',
                        'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                    ],
                    ['class' => 'btn btn-success']
                )
                ,
            ],

        ]
    ]); ?>

</div>
