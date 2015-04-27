<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;


$this->title = Yii::t('app', 'Dynamic content');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>
<?php $this->beginBlock('bottom-buttons'); ?>
<?= \yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Add'),
    ['/backend/dynamic-content/edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    [
        'class' => 'btn btn-success',
    ]
) ?>
<?= \app\backend\widgets\RemoveAllButton::widget([
    'url' => '/backend/dynamic-content/remove-all',
    'gridSelector' => '.grid-view',
    'htmlOptions' => [
        'class' => 'btn btn-danger pull-right'
    ],
]); ?>
<?php $this->endBlock(); ?>
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
                            'class' => \app\backend\columns\CheckboxColumn::className(),
                        ],
                        'id',
                        'route',
                        'name',
                        'content_block_name',
                        'title',
                        'h1',
                        'meta_description',
                        [
                            'class' => app\backend\components\ActionColumn::className(),
                        ],
                    ],
                    'theme' => 'panel-default',
                    'gridOptions' => [
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'hover' => true,
                        'panel' => [
                            'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                            'after' => $this->blocks['bottom-buttons'],
                        ],
                    ]
                ]
            );
        ?>
    </div>
</div>
