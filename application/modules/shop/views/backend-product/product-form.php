<?php

use app\backend\widgets\BackendWidget;
use app\modules\shop\controllers\BackendProductController;
use app\modules\shop\models\Product;
use app\backend\widgets\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;
use kartik\widgets\Select2;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;

/**
 * @var $this \yii\web\View
 * @var $model \app\modules\shop\models\Product
 */
$this->title = Yii::t('app', 'Product edit');

$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => Yii::t('app', 'Products')];
if ($parent !== null) {
    $this->params['breadcrumbs'][] = ['url' => ['edit', 'id' => $parent->id], 'label' => $parent->name];
}
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php
    $form = ActiveForm::begin(
    [
        'id' => 'product-form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]);
?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?php if (!$model->isNewRecord): ?>
        <?=
        Html::a(
            Icon::show('eye') . Yii::t('app', 'Preview'),
            [
                '/shop/product/show',
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
        Yii::$app->request->get('returnUrl', ['index']),
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
                'content' => Html::activeDropDownList($model, 'currency_id', app\modules\shop\models\Currency::getSelection()),
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
        $data = \yii\helpers\ArrayHelper::map($model->relatedProducts, 'id', 'name');
    ?>
    <?=
    $form->field($model, 'relatedProductsArray')
        ->widget(
            Select2::className(),
            [
                'language' => Yii::$app->language,
                'data' => $data,
                'options' => [
                    'placeholder' => 'Поиск продуктов ...',
                    'multiple' => true,
                ],
                'pluginOptions' => [
                    'multiple' => true,
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'ajax' => [
                        'url' => '/shop/backend-product/ajax-related-product',
                        'dataType' => 'json',
                        'data' => new \yii\web\JsExpression('function(term,page) { return {search:term}; }'),
                        'results' => new \yii\web\JsExpression('function(data,page) { return {results:data.results}; }'),
                    ],
                ],
            ]
        );
    ?>
    <?php
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

        <?= \app\modules\image\widgets\ImageDropzone::widget([
            'name' => 'file',
            'url' => ['upload'],
            'removeUrl' => ['remove'],
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

        'makeSlug' => [
            "#product-name",
            "#product-title",
            "#product-h1",
            "#product-breadcrumbs_label",
        ]

    ])
    ?>

    <?=
    $form->field($model, 'title', [
        'copyFrom' => [
            "#product-name",
            "#product-h1",
            "#product-breadcrumbs_label",
        ]
    ])
    ?>

    <?=
    $form->field($model, 'h1', [
        'copyFrom' => [
            "#product-name",
            "#product-title",
            "#product-breadcrumbs_label",
        ]
    ])
    ?>

    <?=
    $form->field($model, 'breadcrumbs_label', [
        'copyFrom' => [
            "#product-name",
            "#product-title",
            "#product-h1",
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
            'getTree' => ['getCatTree'],
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
            'imageManagerJson' => Url::to(['/backend/dashboard/imperavi-images-get']),
            'plugins' => [
                'table',
                'fontsize',
                'fontfamily',
                'fontcolor',
                'video',
                'imagemanager',
            ],
            'replaceStyles' => [],
            'replaceTags' => [],
            'deniedTags' => [],
            'removeEmpty' => [],
            'imageUpload' => Url::to(['/backend/dashboard/imperavi-image-upload']),
        ],
    ]); ?>

    <?= $form->field($model, 'announce')->widget(ImperaviWidget::className(), [
        'settings' => [
            'replaceDivs' => false,
            'minHeight' => 200,
            'paragraphize' => true,
            'pastePlainText' => true,
            'buttonSource' => true,
            'imageManagerJson' => Url::to(['/backend/dashboard/imperavi-images-get']),
            'plugins' => [
                'table',
                'fontsize',
                'fontfamily',
                'fontcolor',
                'video',
                'imagemanager',
            ],
            'replaceStyles' => [],
            'replaceTags' => [],
            'deniedTags' => [],
            'removeEmpty' => [],
            'imageUpload' => Url::to(['/backend/dashboard/imperavi-image-upload']),
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

                        return $content;
                    }
                ],
                'price',
                'old_price',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => function($model, $key, $index, $parent) {

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
<?php
$event = new \app\backend\events\BackendEntityEditFormEvent($form, $model);
$this->trigger(BackendProductController::EVENT_BACKEND_PRODUCT_EDIT_FORM, $event);
?>
<?php ActiveForm::end(); ?>
