<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var \yii\data\ActiveDataProvider $partsDataProvider */
/** @var \app\extensions\DefaultTheme\models\ThemeParts  $partsSearchModel */

?>
<?php $this->beginBlock('bottom-buttons-parts'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/DefaultTheme/backend-configuration/edit-part', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['enablePushState' => false]) ?>
<?= \kartik\dynagrid\DynaGrid::widget([
    'options' => [
        'id' => 'theme-parts-grid',
    ],
    'columns' => [
        'id',
        'name',
        'key',
        [
            'class' => \app\backend\columns\BooleanStatus::className(),
            'attribute' => 'is_cacheable',
            'header' => Yii::t('app', 'Is cacheable'),
        ],
        [
            'class' => app\backend\components\ActionColumn::className(),
            'buttons' => [
                'edit-part' => [
                    'url' => 'edit-part',
                    'icon' => 'pencil',
                    'class' => 'btn-default',
                    'label' => Yii::t('app', 'Edit'),
                ],
                'delete-part' => [
                    'options' => [
                        'data-action' => 'delete',
                    ],
                    'url' => 'delete-part',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app', 'Delete'),
                ]
            ],
        ],
    ],
    'theme' => 'panel-default',
    'gridOptions' => [
        'dataProvider' => $partsDataProvider,
        'filterModel' => $partsSearchModel,
        'hover' => true,
        'panel' => [
            'heading' => Html::tag('h3', Yii::t('app', 'Theme parts'), ['class' => 'panel-title']),
            'after' => $this->blocks['bottom-buttons-parts'],
        ],
    ]
]);?>
<?php Pjax::end() ?>
