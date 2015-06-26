<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var \yii\data\ActiveDataProvider $variationsDataProvider */
/** @var \app\extensions\DefaultTheme\models\ThemeVariation $variationsSearchModel */

?>
<?php $this->beginBlock('bottom-buttons-parts'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/DefaultTheme/backend-configuration/edit-variation', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['enablePushState' => false]) ?>
<?= \kartik\dynagrid\DynaGrid::widget([
    'options' => [
        'id' => 'theme-variations-grid',
    ],
    'columns' => [
        'id',
        'name',
        'by_url',
        'by_route',
        'matcher_class_name',
        [
            'class' => \app\backend\columns\BooleanStatus::className(),
            'attribute' => 'exclusive',
            'header' => Yii::t('app', 'Exclusive'),
            'true_value' => Yii::t('app', 'Yes'),
            'false_value' => Yii::t('app', 'No'),
        ],
        [
            'class' => app\backend\components\ActionColumn::className(),
            'buttons' => [
                'edit-part' => [
                    'url' => 'edit-variation',
                    'icon' => 'pencil',
                    'class' => 'btn-default',
                    'label' => Yii::t('app', 'Edit'),
                ],
                'active-widgets' => [
                    'url' => 'active-widgets',
                    'icon' => 'list',
                    'class' => 'btn-primary',
                    'label' => Yii::t('app', 'Show active widgets'),
                ],
                'delete-part' => [
                    'options' => [
                        'data-action' => 'delete',
                    ],
                    'url' => 'delete-variation',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app', 'Delete'),
                ]
            ],
        ],
    ],
    'theme' => 'panel-default',
    'gridOptions' => [
        'dataProvider' => $variationsDataProvider,
        'filterModel' => $variationsSearchModel,
        'hover' => true,
        'panel' => [
            'heading' => Html::tag('h3', Yii::t('app', 'Theme variations'), ['class' => 'panel-title']),
            'after' => $this->blocks['bottom-buttons-parts'],
        ],
    ]
]);?>
<?php Pjax::end() ?>
