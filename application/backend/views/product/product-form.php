<?php

use app\backend\widgets\BackendWidget;
use app\models\Product;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\Product
 */
$this->title = Yii::t('app', 'Product edit');

$this->params['breadcrumbs'][] = ['url' => ['/backend/product/index'], 'label' => Yii::t('app', 'Products')];
if ($parent !== null) {
    $this->params['breadcrumbs'][] = ['url' => ['/backend/product/edit?id='.$parent->id], 'label' => $parent->name];
}
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'product-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('eye') . Yii::t('app', 'Preview'),
        Url::to(['/product/show', 'model' => $model]),
        ['class' => 'btn btn-success', 'target' => '_blank']
    ) ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
    ) ?>
</div>
<?php $this->endBlock('submit'); ?>

<section id="widget-grid">
<div class="row">

<article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

    <?php
    BackendWidget::begin(
        [
            'title'=> Yii::t('app', 'Product'),
            'icon'=>'shopping-cart',
            'footer'=>$this->blocks['submit']
        ]
    ); ?>

    <?= $form->field($model, 'active')->widget(\kartik\switchinput\SwitchInput::className()) ?>
    <?= $form->field($model, 'name')?>
    <?= $form->field($model, 'price')?>
    <?= $form->field($model, 'old_price')?>

    <?=
    $form->field(app\models\ViewObject::getByModel($model, true), 'view_id')
        ->dropDownList(
            app\models\View::getAllAsArray()
        );
    ?>

    <?php BackendWidget::end(); ?>

    <?php if (!$model->isNewRecord):?>

        <?php
        BackendWidget::begin(
            [
                'title'=> Yii::t('app', 'Images'),
                'icon'=>'image',
                'footer'=>$this->blocks['submit']
            ]
        ); ?>

        <div id="actions">
            <?=
            \yii\helpers\Html::tag(
                'span',
                Icon::show('plus') . Yii::t('app', 'Add files..'),
                [
                    'class' => 'btn btn-success fileinput-button'
                ]
            ) ?>
        </div>

        <?= \app\widgets\image\ImageDropzone::widget([
            'name' => 'file',
            'url' => ['/backend/product/upload'],
            'removeUrl' => ['/backend/product/remove'],
            'uploadDir' => '/theme/resources/product-images',
            'sortable' => true,
            'sortableOptions' => [
                'items' => '.dz-image-preview',
            ],
            'objectId' => $object->id,
            'modelId' => $model->id,
            'htmlOptions' => [
                'class' => 'table table-striped files',
                'id' => 'previews',
            ],
            'options' => [
                'clickable' => ".fileinput-button",
            ],
        ]); ?>

        <?php BackendWidget::end(); ?>
    <?php endif; ?>

    <?php BackendWidget::begin(['title'=> Yii::t('app', 'SEO'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit']]); ?>

    <?=
    $form->field($model, 'slug', [
        'addon' => [
            'append' => [
                'content' => Html::button(
                    Icon::show('code'),
                    ['class'=>'btn btn-primary', 'id'=>'translit-slug']
                ),
                'asButton' => true,
            ]
        ]
    ])
    ?>

    <?=
    $form->field($model, 'title', [
        'addon' => [
            'append' => [
                'content' => Html::button(
                    Icon::show('copy'),
                    ['class'=>'btn btn-primary', 'id'=>'copy-title']
                ),
                'asButton' => true,
            ]
        ]
    ])
    ?>

    <?=
    $form->field($model, 'h1', [
        'addon' => [
            'append' => [
                'content' => Html::button(
                    Icon::show('copy'),
                    ['class'=>'btn btn-primary', 'id'=>'copy-h1']
                ),
                'asButton' => true,
            ]
        ]
    ])
    ?>

    <?=
    $form->field($model, 'breadcrumbs_label', [
        'addon' => [
            'append' => [
                'content' => Html::button(
                    Icon::show('copy'),
                    ['class'=>'btn btn-primary', 'id'=>'copy-breadcrumbs_label']
                ),
                'asButton' => true,
            ]
        ]
    ])
    ?>

    <?= $form->field($model, 'meta_description')->textarea() ?>

    <?php BackendWidget::end(); ?>

    <?=
    \app\properties\PropertiesWidget::widget([
        'model' => $model,
        'form' => $form,
    ]);
    ?>

</article>


<article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php
    BackendWidget::begin(
        [
            'title'=> Yii::t('app', 'Categories'),
            'icon'=>'tree',
            'footer'=>$this->blocks['submit']
        ]
    ); ?>

    <?=
    \app\backend\widgets\JSSelectableTree::widget([
        'flagFieldName' => 'main_category_id',
        'fieldName' => 'categories',
        'model' => $model,
        'selectedItems' => $selected,
        'selectOptions' => ['class' => 'form-control'],
        'selectLabel' => Yii::t('app', 'Main category'),
        'routes' => [
            'getTree' => ['/backend/product/getCatTree'],
        ],
        'stateKey' => $model->id . $model->isNewRecord?time() : '',
    ]);
    ?>
    <br />

    <?php BackendWidget::end(); ?>

    <?php
    BackendWidget::begin(
        [
            'title'=> Yii::t('app', 'Content'),
            'icon'=>'file-text',
            'footer'=>$this->blocks['submit']
        ]
    ); ?>

    <?= $form->field($model, 'content')->widget(ImperaviWidget::className(), [
        'settings' => [
            'replaceDivs' => false,
            'minHeight' => 200,
            'paragraphize' => true,
            'pastePlainText' => true,
            'buttonSource' => true,
            'plugins' => [
                'table',
                'fontsize',
                'fontfamily',
                'fontcolor',
                'video',
            ],
        ],
    ]); ?>

    <?= $form->field($model, 'announce')->widget(ImperaviWidget::className(), [
        'settings' => [
            'replaceDivs' => false,
            'minHeight' => 200,
            'paragraphize' => true,
            'pastePlainText' => true,
            'buttonSource' => true,
            'plugins' => [
                'table',
                'fontsize',
                'fontfamily',
                'fontcolor',
                'video',
            ],
        ],
    ]); ?>
    
    <?= $form->field($model, 'sort_order'); ?>

    <?php BackendWidget::end(); ?>

    <?php
    BackendWidget::begin(
        [
            'title'=> Yii::t('app', 'Warehouse'),
            'icon'=>'archive',
            'footer'=>$this->blocks['submit']
        ]
    ); ?>

    <?= $form->field($model, 'sku') ?>
    <?= $form->field($model, 'in_warehouse')?>
    <?= $form->field($model, 'unlimited_count')->widget(\kartik\switchinput\SwitchInput::className())?>
    <?= $form->field($model, 'reserved_count')?>

    <?php BackendWidget::end(); ?>

    <?php if ($model->parent_id == 0) : ?>

        <?= \app\backend\widgets\OptionGenerate::widget([
            'model' => $model,
            'form' => $form,
            'footer' => $this->blocks['submit'],
        ]); ?>

    <?php endif; ?>

    <?php if (!empty($model->options)) : ?>
        <?php
        BackendWidget::begin(
            [
                'title'=> Yii::t('app', 'Product Options'),
                'icon'=>'shopping-cart',
                'footer'=>$this->blocks['submit']
            ]
        ); ?>

        <?=
        GridView::widget([
            'dataProvider' =>  $dataProvider = new ActiveDataProvider(
                [
                    'query' => Product::find()
                        ->where(['parent_id' => $model->id]),
                ]
            ),
            'columns' => [
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'id',
                ],
                'name',
                'price',
                'old_price',
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
                ],
            ],
            'hover' => true,
        ]);
        ?>
        <?php BackendWidget::end(); ?>
    <?php endif; ?>
</article>
</div>
</section>

<?php ActiveForm::end(); ?>
<script>
    $(function(){
        $("#translit-slug").click(function () {
            Admin.makeSlug(
                [
                    "#product-name",
                    "#product-title",
                    "#product-h1",
                    "#product-breadcrumbs_label"
                ],
                "#product-slug"
            );
            return false;
        });

        $("#copy-title").click(function () {
            Admin.copyFrom(
                [
                    "#product-name",
                    "#product-h1",
                    "#product-breadcrumbs_label"
                ],
                "#product-title"
            );
            return false;
        });

        $("#copy-h1").click(function () {
            Admin.copyFrom(
                [
                    "#product-name",
                    "#product-title",
                    "#product-breadcrumbs_label"
                ],
                "#product-h1"
            );
            return false;
        });


        $("#copy-breadcrumbs_label").click(function () {
            Admin.copyFrom(
                [
                    "#product-name",
                    "#product-title",
                    "#product-h1"
                ],
                "#product-breadcrumbs_label"
            );
            return false;
        });
    });
</script>