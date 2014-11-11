<?php

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Navigations');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Navigation: ').'"'.$model->name.'"';

}
$parent_id = is_object($model) ? $model->id : '0';

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-4">
        <?=
            app\backend\widgets\JSTree::widget([
                'model' => new \app\widgets\navigation\models\Navigation(),
                'routes' => [
                    'getTree' => ['/backend/navigation/getTree', 'selected_id' => $parent_id],
                    'open' => ['/backend/navigation/index'],
                    'edit' => ['/backend/navigation/edit'],
                    'delete' => ['/backend/navigation/delete'],
                    'create' => ['/backend/navigation/edit'],
                ],
            ]);
        ?>
    </div>
    <div class="col-md-8">
        <?php
        $this->beginBlock('add-button');
        ?>
                <?= \yii\helpers\Html::a(
                    Icon::show('plus') . " " . Yii::t('app', 'Add'),
                    [
                        '/backend/navigation/edit',
                        'parent_id' => $parent_id,
                    ],
                    [
                        'class' => 'btn btn-success',
                    ]
                ) ?>
        <?php
        $this->endBlock();
        ?>
        <?=
            DynaGrid::widget([
                'options' => [
                    'id' => 'nav-grid',
                ],
                'columns' => [
                    [
                        'class' => 'yii\grid\DataColumn',
                        'attribute' => 'id',
                    ],
                    'name',
                    'url',
                    'route',
                    'route_params',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'buttons' => [
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
                        'url_append' => "&parent_id={$parent_id}",
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
            ]);
        ?>
    </div>
</div>
