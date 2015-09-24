<?php

use app\backend\widgets\BackendWidget;
use app\modules\shop\controllers\BackendProductController;
use app\modules\shop\models\Product;
use app\backend\widgets\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;
use kartik\widgets\DateTimePicker;
use yii\data\ActiveDataProvider;
use app\backend\components\Helper;
use app\modules\shop\ShopModule;
use yii\helpers\Url;

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
                '@product',
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

<article class="<?= Helper::getBackendGridClass('shop', ShopModule::BACKEND_PRODUCT_GRID, 1) ?>">

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
            \app\backend\widgets\Select2Ajax::widget([
                'initialData' => $data,
                'form' => $form,
                'model' => $model,
                'modelAttribute' => 'relatedProductsArray',
                'multiple' => true,
                'searchUrl' => '/shop/backend-product/ajax-related-product',
                'additional' => [
                    'placeholder' => 'Поиск продуктов ...',
                ],
            ]);
        ?>
    <?php
    endif;
    ?>

    <?=
    $form->field($model, 'measure_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\modules\shop\models\Measure::className(), 'id', 'name')
        );
    ?>

    <?php BackendWidget::end(); ?>



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
        <?php
        if (Yii::$app->getModule('elfinder')) {
            echo \DotPlant\ElFinder\widgets\ElfinderFileInput::widget(
                ['url' => Url::toRoute(['addImage', 'objId' => $object->id, 'objModelId' => $model->id])]
            );
        }
        ?>
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


<article class="<?= Helper::getBackendGridClass('shop', ShopModule::BACKEND_PRODUCT_GRID, 2) ?>">
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

    <?= $form->field($model, 'content')->widget(Yii::$app->getModule('core')->wysiwyg_class_name(), Yii::$app->getModule('core')->wysiwyg_params()); ?>

    <?= $form->field($model, 'announce')->widget(Yii::$app->getModule('core')->wysiwyg_class_name(), Yii::$app->getModule('core')->wysiwyg_params()); ?>

    <?= $form->field($model, 'sort_order'); ?>

    <?=$form->field($model, 'date_added')->widget(
        DateTimePicker::classname(),
        [
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd hh:ii',
                'todayHighlight' => true,
                'todayBtn' => true,

            ]
        ]
    );?>

    <?php BackendWidget::end(); ?>



    <?php if ($model->parent_id == 0) : ?>

        <?= \app\modules\shop\widgets\OptionGenerate::widget([
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
