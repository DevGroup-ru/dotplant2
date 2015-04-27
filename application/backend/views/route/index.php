<?php
use app\backend\components\ActionColumn;
use kartik\helpers\Html;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use yii\helpers\Url;

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
        'options' => [
            'id' => 'routes-grid',
        ],
        'columns' => [
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'id',
            ],
            'name',
            'route',
            [
                'class' => ActionColumn::className(),
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