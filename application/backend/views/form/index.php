<?php

use kartik\dynagrid\DynaGrid;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

$this->title = Yii::t('app', 'Forms');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('add-button'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/backend/form/edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?= \app\backend\widgets\RemoveAllButton::widget([
    'url' => '/backend/form/remove-all',
    'gridSelector' => '.grid-view',
    'htmlOptions' => [
        'class' => 'btn btn-danger pull-right'
    ],
]); ?>
<?php $this->endBlock(); ?>

<?= DynaGrid::widget([
    'options' => [
        'id' => 'form-grid',
    ],
    'columns' => [
        [
            'class' => \kartik\grid\CheckboxColumn::className(),
            'options' => [
                'width' => '10px',
            ],
        ],
        'id',
        'name',
        [
            'attribute' => 'form_view',
            'format' => ['truncated', 40],
        ],
        [
            'attribute' => 'form_success_view',
            'format' => ['truncated', 40],
        ],
        'email_notification_addresses:email',
        [
            'attribute' => 'email_notification_view',
            'format' => ['truncated', 40],
        ],
        'form_open_analytics_action_id',
        'form_submit_analytics_action_id',
        [
            'class' => 'app\backend\components\ActionColumn',
            'buttons' => [
                [
                    'url' => 'view',
                    'icon' => 'eye',
                    'class' => 'btn-info',
                    'label' => Yii::t('app', 'View'),
                ],
                [
                    'url' => 'edit',
                    'icon' => 'pencil',
                    'class' => 'btn-primary',
                    'label' => Yii::t('app','Edit'),
                ],
                [
                    'url' => 'delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app','Delete'),
                    'options' => [
                        'data-action' => 'delete',
                    ],
                ],
            ],
            'options' => [
                'width' => '125px',
            ]
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
]); ?>
