<?php

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Trash');
    $this->params['breadcrumbs'][] = $this->title;
?>

    <?= app\widgets\Alert::widget([
        'id' => 'alert',
    ]); ?>

<?php
$this->beginBlock('add-button');
?>
<a href="<?= Url::toRoute('/backend/product/edit') ?>" class="btn btn-success">
    <?= Icon::show('plus') ?>
    <?= Yii::t('app', 'Add') ?>
</a>
<?php
$this->endBlock();
?>

<div class="row">
    <div class="col-md-4">
        <?=
        app\backend\widgets\JSTreeTrash::widget([
            'model' => new app\models\Category,
            'routes' => [
                'getTree' => [Url::toRoute('getTree')],
                'restore' => ['/backend/category/restore'],
                'delete' => ['/backend/category/delete'],
            ]
        ]);
        ?>
    </div>
    <div class="col-md-8" id="jstree-more">
        <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'Product-grid',

            ],
            'columns' => [
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                'name',
                'slug',
                [
                    'class' => 'app\backend\columns\BooleanStatus',
                    'attribute' => 'active',
                ],
                'price',
                'old_price',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'controller' => 'product',
                    'buttons' => [
                        [
                            'url' => 'restore',
                            'icon' => 'refresh',
                            'class' => 'btn-success',
                            'label' => 'Restore',
                        ],
                        [
                            'url' => 'delete',
                            'icon' => 'trash-o',
                            'class' => 'btn-danger',
                            'label' => 'Delete',
                        ],
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
        ]);
        ?>
    </div>
</div>
