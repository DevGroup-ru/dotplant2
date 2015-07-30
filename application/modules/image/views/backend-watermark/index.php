<?php

use app\backend\columns\TextWrapper;
use app\backend\components\Helper;
use app\backend\widgets\RemoveAllButton;
use kartik\dynagrid\DynaGrid;
use kartik\grid\CheckboxColumn;
use kartik\icons\Icon;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

$this->title = Yii::t('app', 'Watermark');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $this->beginBlock('add-button'); ?>
<?=Html::a(
    Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['edit', 'returnUrl' => Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
)?>
<?=RemoveAllButton::widget(
    [
        'url' => 'remove-all',
        'gridSelector' => '.grid-view',
        'htmlOptions' => [
            'class' => 'btn btn-danger pull-right'
        ],
    ]
);?>
<?php $this->endBlock(); ?>

<?=DynaGrid::widget(
    [
        'options' => [
            'id' => 'form-grid',
        ],
        'columns' => [
            [
                'class' => CheckboxColumn::className(),
                'options' => [
                    'width' => '10px',
                ],
            ],
            'id',
            [
                'class' => TextWrapper::className(),
                'attribute' => 'watermark_path',
                'callback_wrapper' => function ($content, $model) {
                    return Html::img($model->file, ['style' => 'max-width:200px;max-height:200px;']);
                }
            ],
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => [
                    [
                        'url' => 'edit',
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
                'options' => [
                    'width' => '85px',
                ]
            ],
        ],
        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                'after' => $this->blocks['add-button'],
            ],

        ]
    ]
);?>