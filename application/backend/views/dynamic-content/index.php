<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Dynamic content');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-12">
        <?=
            DynaGrid::widget(
                [
                    'options' => [
                        'id' => 'dynamic-content-grid',
                    ],
                    'columns' => [
                        [
                            'class' => \kartik\grid\CheckboxColumn::className(),
                            'options' => [
                                'width' => '10px',
                            ],
                        ],
                        'id',
                        'route',
                        'name',
                        'content_block_name',
                        'title',
                        'h1',
                        'meta_description',
                        [
                            'class' => ActionColumn::className(),
                            'options' => [
                                'width' => '95px',
                            ],
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
                                ['/backend/dynamic-content/edit'],
                                ['class' => 'btn btn-success']
                            ) . \app\backend\widgets\RemoveAllButton::widget([
                                'url' => '/backend/dynamic-content/remove-all',
                                'gridSelector' => '.grid-view',
                                'htmlOptions' => [
                                    'class' => 'btn btn-danger pull-right'
                                ],
                            ]),
                        ],
                    ]
                ]
            );
        ?>
    </div>
</div>
