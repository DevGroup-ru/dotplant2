<?php
use yii\helpers\Url;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

$blockName = 'add-button-'.md5($objectModel::tableName());

?>
<?php
$this->beginBlock($blockName);
?>
<?= \app\backend\widgets\RemoveAllButton::widget([
    'url' => Url::toRoute(
        [
            '/backend/trash/clean',
            'returnUrl' => \app\backend\components\Helper::getReturnUrl(),
            'modelName' => $objectModel::className(),
        ]
    ),
    'gridSelector' => '.grid-view',
    'htmlOptions' => [
        'class' => 'btn btn-danger pull-right',
        'id' => 'id-'.$blockName
    ],
]); ?>
<div class="clearfix"></div>
<?php
$this->endBlock();
?>


<div class="row">
    <div class="col-md-12">
        <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'Product-grid',
                ],
                'columns' => $colums,
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $objectProvider,
                    'filterModel' => $objectModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => Html::tag('h3', $name, ['class' => 'panel-title']),
                        'after' => $this->blocks[$blockName],
                    ],
                ]
            ]
        );
        ?>
    </div>
</div>
