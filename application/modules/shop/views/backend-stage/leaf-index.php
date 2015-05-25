<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\OrderStage $searchModel
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\icons\Icon;

    $this->title = Yii::t('app', 'Order stages leafs');
    $this->params['breadcrumbs'] = [
        [
            'label' => Yii::t('app', 'Order stage subsystem'),
            'url' => Url::to(['index']),
        ],
        $this->title,
    ];
?>

<?= app\widgets\Alert::widget(['id' => 'alert']); ?>

<?php
    $this->beginBlock('buttons');
        echo Html::a(Icon::show('plus') . Yii::t('app', 'Add'), Url::to(['leaf-edit']), ['class' => 'btn btn-success']);
    $this->endBlock();

    echo \kartik\dynagrid\DynaGrid::widget([
        'options' => [
            'id' => 'stages-leafs-list',
        ],
        'columns' => [
            'id',
            [
                'attribute' => 'stage_from_id',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    return $model->stageFrom->name;
                }
            ],
            [
                'attribute' => 'stage_to_id',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    return $model->stageTo->name;
                }
            ],
            'button_label',
            'event_name',
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => [
                    [
                        'url' => 'leaf-edit',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'Edit'),
                    ],
                    [
                        'url' => 'leaf-delete',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => Yii::t('app', 'Delete'),
                        'options' => [
                            'data-action' => 'delete',
                        ],
                    ],
                ],
            ],
        ],
        'userSpecific' => false,
        'showPersonalize' => false,
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                'after' => $this->blocks['buttons'],
            ],
        ],
    ]);
?>