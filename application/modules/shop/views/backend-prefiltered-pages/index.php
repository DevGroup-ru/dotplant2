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

$this->title = Yii::t('app', 'Prefiltered pages');
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
                        'id' => 'prefiltered-pages-grid',
                    ],
                    'columns' => [
                        [
                            'class' => \kartik\grid\CheckboxColumn::className(),
                            'options' => [
                                'width' => '10px',
                            ],
                        ],
                        'id',
                        'slug',
                        'title',
                        'h1',
                        'meta_description',
                        [
                            'class' => ActionColumn::className(),
                            'options' => [
                                'width' => '95px',
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
                                ['edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                                ['class' => 'btn btn-success']
                            ) . \app\backend\widgets\RemoveAllButton::widget([
                                'url' => 'remove-all',
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
