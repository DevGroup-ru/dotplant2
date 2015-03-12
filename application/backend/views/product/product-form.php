<?php

use app\backend\widgets\BackendWidget;
use app\models\Product;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
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
    <?php if (!$model->isNewRecord): ?>
        <?=
        Html::a(
            Icon::show('eye') . Yii::t('app', 'Preview'),
            [
                '/product/show',
                'model' => $model,
                'category_group_id' => is_null($model->mainCategory) ? null : $model->mainCategory->category_group_id,
            ],
            [
                'class' => 'btn btn-info',
                'target' => '_blank',
            ]
        )
        ?>
    <?php endif; ?>
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/product/index']),
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
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
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
    <?= $form->field($model, 'price',[
        'addon' => [
            'append' => [
                'content' => Html::activeDropDownList($model, 'currency_id', app\models\Currency::getSelection()),
            ],
        ],
    ])?>
    <?= $form->field($model, 'old_price')?>

    <?=
    $form->field(app\models\ViewObject::getByModel($model, true), 'view_id')
        ->dropDownList(
            app\models\View::getAllAsArray()
        );
    ?>

    <?php
    if (!$model->isNewRecord && is_array($model->relatedProductsArray)):
        $_relatedProductsArray = $model->relatedProductsArray;
        $model->relatedProductsArray = implode(',', $_relatedProductsArray);

        $initScript = <<< SCRIPT
    function (element, callback) {
        var id={$model->id};
        if (id !== "") {
            \$.ajax("/backend/product/ajax-related-product?id=" + id, {
                dataType: "json"
            }).done(function(data) { callback(data.results);});
        }
    }
SCRIPT;
    ?>
    <?=
    $form->field($model, 'relatedProductsArray')
        ->widget(
            Select2::className(),
            [
//                'data' => \app\components\Helper::getModelMap(Product::className(), 'id', 'name'),
                'options' => [
                    'placeholder' => 'Поиск продуктов ...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'multiple' => true,
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => '/backend/product/ajax-related-product',
                        'dataType' => 'json',
                        'data' => new \yii\web\JsExpression('function(term,page) { return {search:term}; }'),
                        'results' => new \yii\web\JsExpression('function(data,page) { return {results:data.results}; }'),
                    ],
                    'initSelection' => new \yii\web\JsExpression($initScript),
                ],
            ]
        );
    ?>
    <?php
        $model->relatedProductsArray = $_relatedProductsArray;
    endif;
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

    <?php
    BackendWidget::end();
    ?>

    <?php
    BackendWidget::begin(
        [
            'title'=> Yii::t('app', 'Warehouse'),
            'icon'=>'archive',
            'footer'=>$this->blocks['submit']
        ]
    ); ?>

    <?= $form->field($model, 'sku') ?>
    <?= $form->field($model, 'unlimited_count')->widget(\kartik\switchinput\SwitchInput::className())?>
    <?= \app\backend\widgets\WarehousesRemains::widget([
        'model' => $model,
    ]) ?>
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
            'replaceStyles' => [],
            'replaceTags' => [],
            'deniedTags' => [],
            'removeEmpty' => [],
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
            'replaceStyles' => [],
            'replaceTags' => [],
            'deniedTags' => [],
            'removeEmpty' => [],
        ],
    ]); ?>
    
    <?= $form->field($model, 'sort_order'); ?>

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
                [
                    'class' => 'app\backend\columns\TextWrapper',
                    'attribute' => 'name',
                    'callback_wrapper' => function ($content, $model, $key, $index, $parent) {
                        if (1 === $model->is_deleted) {
                            $content = '<div class="is_deleted"><span class="fa fa-trash-o"></span>'.$content.'</div>';
                        }
                        return $content;
                    }
                ],
                'price',
                'old_price',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => function($model, $key, $index, $parent) {
                        if (1 === $model->is_deleted) {
                            return [
                                [
                                    'url' => 'edit',
                                    'icon' => 'pencil',
                                    'class' => 'btn-primary',
                                    'label' => 'Edit',
                                ],
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
                            ];
                        }
                        return null;
                    }
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