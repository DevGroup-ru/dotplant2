<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use app\backend\components\ActionColumn;
use app\backend\widgets\BackendWidget;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Update');
$this->params['breadcrumbs'][] = ['url' => ['/backend/properties/index'], 'label' => Yii::t('app', 'Properties')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $this->beginBlock('submit'); ?>
<?=
Html::a(
    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
    Yii::$app->request->get('returnUrl', ['/backend/properties/index']),
    ['class' => 'btn btn-danger']
)
?>

<?php if ($model->isNewRecord): ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go next'),
        [
            'class' => 'btn btn-success',
            'name' => 'action',
            'value' => 'next',
        ]
    )
    ?>
<?php endif; ?>

<?=
Html::submitButton(
    Icon::show('save') . Yii::t('app', 'Save & Go back'),
    [
        'class' => 'btn btn-warning',
        'name' => 'action',
        'value' => 'back',
    ]
)
?>

<?=
Html::submitButton(
    Icon::show('save') . Yii::t('app', 'Save'),
    [
        'class' => 'btn btn-primary',
        'name' => 'action',
        'value' => 'save',
    ]
)
?>
<?php $this->endBlock('submit'); ?>

<?php $form = ActiveForm::begin(['id' => 'property-group-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php
                    BackendWidget::begin(
                        [
                            'title' => Yii::t('app', 'Property groups'),
                            'icon' => 'list',
                            'footer' => $this->blocks['submit'],
                        ]
                    );
                ?>
                    <?= $form->field($model, 'name') ?>
                    <?= $form->field($model, 'object_id')->dropDownList(app\models\Object::getSelectArray())?>
                    <?= $form->field($model, 'is_internal')->checkbox() ?>
                    <?= $form->field($model, 'hidden_group_title')->checkbox() ?>
                    <?= $form->field($model, 'sort_order') ?>
                <?php BackendWidget::end(); ?>
            </article>
        </div>
    </section>
<?php ActiveForm::end(); ?>
<?php if (!$model->isNewRecord): ?>
    <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'group-grid',
                ],
                'columns' => [
                    [
                        'class' => \kartik\grid\CheckboxColumn::className(),
                        'options' => [
                            'width' => '10px',
                        ],
                    ],
                    [
                        'class' => \kartik\grid\DataColumn::className(),
                        'attribute' => 'id',
                    ],
                    [
                        'attribute' => 'property_handler_id',
                        'filter' => app\models\PropertyHandler::getSelectArray(),
                        'value' => function ($model, $key, $index, $widget) {
                            $array = app\models\PropertyHandler::getSelectArray();
                            return $array[$model->property_handler_id];
                        },
                    ],
                    'name',
                    'key',
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'has_static_values',
                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'has_slugs_in_values',
                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'is_eav',
                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'is_column_type_stored',
                    ],
                    [
                        'class' => \kartik\grid\BooleanColumn::className(),
                        'attribute' => 'multiple',
                    ],
                    [
                        'class' => ActionColumn::className(),
                        'buttons' => [
                            [
                                'url' => 'edit-property',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => 'Edit',
                            ],
                            [
                                'url' => 'delete-property',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => 'Delete',
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ],
                        'url_append' => '&property_group_id=' . $model->id,
                    ],
                ],
                'theme' => 'panel-default',
                'gridOptions'=>[
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'hover' => true,
                    'panel' => [
                        'heading' => Html::tag('h3', Yii::t('app', 'Properties'), ['class' => 'panel-title']),
                        'after' => Html::a(
                            Icon::show('plus') . Yii::t('app', 'Add'),
                            ['/backend/properties/edit-property', 'property_group_id' => $model->id, 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                            ['class' => 'btn btn-success']
                        ) . \app\backend\widgets\RemoveAllButton::widget([
                            'url' => \yii\helpers\Url::to(['/backend/properties/remove-all-properties', 'group_id' => $model->id]),
                            'gridSelector' => '.grid-view',
                            'htmlOptions' => [
                                'class' => 'btn btn-danger pull-right'
                            ],
                        ]),
                    ],
                ],
            ]
        );
    ?>
<?php endif; ?>
