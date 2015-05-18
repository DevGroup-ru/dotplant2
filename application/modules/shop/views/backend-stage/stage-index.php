<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\OrderStage $searchModel
 * @var \yii\data\ActiveDataProvider $dataProvider
 */

use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\icons\Icon;

    $this->title = Yii::t('app', 'Order stages');
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
        echo Html::a(Icon::show('plus') . Yii::t('app', 'Add'), Url::to(['stage-edit']), ['class' => 'btn btn-success']);
    $this->endBlock();

    echo \kartik\dynagrid\DynaGrid::widget([
        'options' => [
            'id' => 'stages-list',
        ],
        'columns' => [
            'id',
            'name',
            'name_frontend',
            'name_short',
            'event_name',
            'is_initial',
            'is_buyer_stage',
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => [
                    [
                        'url' => 'stage-edit',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'Edit'),
                    ],
                    [
                        'url' => 'stage-delete',
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