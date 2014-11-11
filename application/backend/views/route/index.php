<?php

use karik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use yii\helpers\Url;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Routes');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php
$this->beginBlock('add-button');
?>
        <a href="<?= Url::toRoute('/backend/route/edit') ?>" class="btn btn-success">
            <?= Icon::show('plus') ?>
            <?= Yii::t('app', 'Add') ?>
        </a>
<?php
$this->endBlock();
?>

<?=
    DynaGrid::widget([
        'columns' => [
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            'name',
            'route',
            [
                'class' => 'app\backend\components\ActionColumn',
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