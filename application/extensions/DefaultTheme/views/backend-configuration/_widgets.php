<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var \yii\data\ActiveDataProvider $widgetsDataProvider */
/** @var \app\extensions\DefaultTheme\models\ThemeWidgets  $widgetsSearchModel */

?>
<?php $this->beginBlock('bottom-buttons-parts'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/DefaultTheme/backend-configuration/edit-widget', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['enablePushState' => false]) ?>
<?= \kartik\dynagrid\DynaGrid::widget([
    'options' => [
        'id' => 'theme-widgets-grid',
    ],
    'columns' => [
        'id',
        'name',
        'widget',
        [
            'class' => \app\backend\columns\BooleanStatus::className(),
            'attribute' => 'is_cacheable',
            'header' => Yii::t('app', 'Is cacheable'),
        ],
        'cache_lifetime',
        [
            'class' => \app\backend\columns\BooleanStatus::className(),
            'attribute' => 'cache_vary_by_session',
            'header' => Yii::t('app', 'Cache vary by session'),
            'true_value' => Yii::t('app', 'Yes'),
            'false_value' => Yii::t('app', 'No'),
        ],
        'cache_tags',
        [
            'class' => app\backend\components\ActionColumn::className(),
            'buttons' => [
                'edit-part' => [
                    'url' => 'edit-widget',
                    'icon' => 'pencil',
                    'class' => 'btn-default',
                    'label' => Yii::t('app', 'Edit'),
                ],
                'delete-part' => [
                    'options' => [
                        'data-action' => 'delete',
                    ],
                    'url' => 'delete-widget',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app', 'Delete'),
                ]
            ],
        ],
    ],
    'theme' => 'panel-default',
    'gridOptions' => [
        'dataProvider' => $widgetsDataProvider,
        'filterModel' => $widgetsSearchModel,
        'hover' => true,
        'panel' => [
            'heading' => Html::tag('h3', Yii::t('app', 'All available theme widgets'), ['class' => 'panel-title']),
            'after' => $this->blocks['bottom-buttons-parts'],
        ],
    ]
]);?>
<?php Pjax::end() ?>
