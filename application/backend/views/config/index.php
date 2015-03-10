<?php

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Configs');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="config-index">
    <div class="row">
        <div class="col-md-4">
            <?=
                app\backend\widgets\JSTree::widget([
                    'model' => new app\models\Config,
                    'routes' => [
                        'getTree' => ['/backend/config/getTree', 'selected_id' => 0],
                        'open' => ['/backend/config/index'],
                        'edit' => ['/backend/config/update'],
                        'delete' => ['/backend/config/delete'],
                        'create' => ['/backend/config/update'],
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-8">
            <?php \yii\widgets\Pjax::begin() ?>
                <?=
                    DynaGrid::widget(
                        [
                            'options' => [
                                'id' => 'configs-grid',
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
                                        ['/backend/config/update', 'parent_id'
                                            => Yii::$app->request->get('parent_id', 0),
                                            'returnUrl' => \app\backend\components\Helper::getReturnUrl()
                                        ],
                                        ['class' => 'btn btn-success']
                                    ) . \app\backend\widgets\RemoveAllButton::widget([
                                        'url' => \yii\helpers\Url::to(['/backend/config/remove-all', 'parent_id'
                                        => Yii::$app->request->get('parent_id', 0)]),
                                        'gridSelector' => '.grid-view',
                                        'htmlOptions' => [
                                            'class' => 'btn btn-danger pull-right'
                                        ],
                                    ]),
                                ],
                            ],
                            'columns' => [
                                [
                                    'class' => \kartik\grid\CheckboxColumn::className(),
                                    'options' => [
                                        'width' => '10px',
                                    ],
                                ],
                                'id',
                                'parent_id',
                                'name',
                                'key',
                                'value',
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
                        ]
                    );
                ?>
            <?php \yii\widgets\Pjax::end() ?>
        </div>
    </div>
</div>
