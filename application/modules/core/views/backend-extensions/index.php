<?php

use kartik\dynagrid\DynaGrid;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

$this->title = Yii::t('app', 'Extensions');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('add-button'); ?>
    <?=
        \yii\helpers\Html::a(
            \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Install new extension'),
            ['/core/backend-extensions/explore', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
            [
                'class' => 'btn btn-success',
            ]
        )
    ?>

<?php $this->endBlock(); ?>

<?= DynaGrid::widget([
    'options' => [
        'id' => 'extensions-grid',
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
        'type.name',
        'force_version',
        'current_package_version_timestamp',
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
