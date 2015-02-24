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
                            'class' => app\backend\columns\ActionColumn::className(),
                        ],
                    ],
                    'theme' => 'panel-default',
                    'gridOptions' => [
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'hover' => true,
                        'panel' => [
                            'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                            'after' => \app\backend\widgets\helpers\AddRemoveAllPanel::widget([
                                'baseRoute' => '/backend/dynamic-content/',
                            ]),
                        ],
                    ]
                ]
            );
        ?>
    </div>
</div>
