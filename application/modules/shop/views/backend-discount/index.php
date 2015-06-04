<?php


/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\modules\shop\models\Discount $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;

$this->title = Yii::t('app', 'Discount');
if (is_object($model)) {
    $this->title = Yii::t('app', 'Discount inside page: ') . '"' . $model->breadcrumbs_label . '"';

}
$parent_id = is_object($model) ? $model->id : '0';

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-12" id="jstree-more">

        <?php $this->beginBlock('buttonGroup'); ?>
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group">
                <?=
                \yii\helpers\Html::a(
                    Icon::show('plus') . Yii::t('app', 'Add'),
                    ['/shop/backend-discount/edit', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                )
                ?>
            </div>
            <?= \app\backend\widgets\RemoveAllButton::widget([
                'url' => \yii\helpers\Url::toRoute(['/shop/backend-discount/remove-all']),
                'gridSelector' => '.grid-view',
                'htmlOptions' => [
                    'class' => 'btn btn-danger pull-right'
                ],
            ]); ?>
        </div>
        <?php $this->endBlock(); ?>

        <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'page-grid',
            ],
            'columns' => [
                [
                    'class' => \kartik\grid\CheckboxColumn::className(),
                    'options' => [
                        'width' => '10px',
                    ],
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'name',
                ],

                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'value',
                ],
                [
                    'class' => \kartik\grid\BooleanColumn::className(),
                    'attribute' => 'value_in_percent',
                ],
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
                    'after' => $this->blocks['buttonGroup'],
                ],

            ]
        ]);
        ?>
    </div>
</div>
