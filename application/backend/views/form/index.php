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
    ['edit'],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?php $this->endBlock(); ?>

<?= DynaGrid::widget([
        'options' => [
            'id' => 'form-grid',
        ],
        'columns' => [
            'id',
            'name',
            'form_view',
            'form_success_view',
            'email_notification_addresses:email',
            'email_notification_view',
            'form_open_analytics_action_id',
            'form_submit_analytics_action_id',
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => [
                    [
                        'url' => 'view',
                        'icon' => 'eye',
                        'class' => 'btn-info',
                        'label' => 'View',
                    ],
                    [
                        'url' => 'edit',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => 'Edit',
                    ],
                    [
                        'url' => 'delete',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => 'Delete',
                    ],
                ],
                'options' => [
                    'width' => '210px',
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
