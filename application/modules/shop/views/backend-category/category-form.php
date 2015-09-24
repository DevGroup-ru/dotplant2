<?php

use app\backend\widgets\BackendWidget;
use app\modules\shop\controllers\BackendCategoryController;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;
use kartik\widgets\DateTimePicker;
use yii\helpers\ArrayHelper;
use app\backend\components\Helper;
use app\modules\shop\ShopModule;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\modules\shop\models\Category
 */
$this->title = Yii::t('app', 'Category edit');

$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => Yii::t('app', 'Categories')];
if (($model->parent_id > 0) && (null !== $parent = \app\modules\shop\models\Category::findById($model->parent_id, null, null))) {
    $this->params['breadcrumbs'][] = [
        'url' => [
            'index',
            'id' => $parent->id,
            'parent_id' => $parent->parent_id
        ],
        'label' => $parent->name
    ];
}
$this->params['breadcrumbs'][] = $this->title;

?>

<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(['id' => 'category-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?php if (!$model->isNewRecord): ?>
        <?=Html::a(
            Icon::show('eye') . Yii::t('app', 'Preview'),
            [
                '/shop/product/list',
                'category_id' => $model->id,
                'category_group_id' => $model->category_group_id,
            ],
            [
                'class' => 'btn btn-info',
                'target' => '_blank',
            ]
        )?>
        <?=Html::a(
            Icon::show('list') . Yii::t('app', 'Products'),
            [
                '/shop/backend-product/index',
                'parent_id' => $model->id,
            ],
            [
                'class' => 'btn btn-info',
                'target' => '_blank',
            ]
        )?>
    <?php endif; ?>
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?=Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )?>
    <?php endif; ?>
    <?=Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    );?>
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
        <article class="<?= Helper::getBackendGridClass('shop', ShopModule::BACKEND_CATEGORY_GRID, 1) ?>">

            <?php BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'Category'),
                    'icon' => 'tree',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

            <?=$form->field($model, 'active')->widget(\kartik\switchinput\SwitchInput::className())?>

            <?php if ($model->parent_id == 0): ?>
                <?=$form->field($model, 'category_group_id')->dropDownList(
                        \app\components\Helper::getModelMap(\app\modules\shop\models\CategoryGroup::className(), 'id', 'name')
                    )?>
            <?php endif; ?>

            <?=$form->field($model, 'name')?>

            <?=
            $form->field(
                $model,
                'title',
                [
                    'copyFrom'=>[
                        "#category-name",
                        "#category-h1",
                        "#category-breadcrumbs_label",
                    ]
                ]
            )
            ?>

            <?=
            $form->field(app\models\ViewObject::getByModel($model, true), 'view_id')->dropDownList(
                    app\models\View::getAllAsArray()
                );
            ?>

            <?=$form->field($model, 'announce')->widget(
                Yii::$app->getModule('core')->wysiwyg_class_name(),
                Yii::$app->getModule('core')->wysiwyg_params()
            );?>

            <?=$form->field($model, 'content')->widget(
                Yii::$app->getModule('core')->wysiwyg_class_name(),
                Yii::$app->getModule('core')->wysiwyg_params()
            );?>

            <?=$form->field($model, 'sort_order');?>

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

            <?php
            if($model->isNewRecord === false) {
                echo $form->field($model, 'parent_id')->dropDownList(
                    ArrayHelper::merge(
                        [0 => Yii::t('app', 'Root')],
                        ArrayHelper::map(
                            \app\modules\shop\models\Category::find()->where('id != :id', ['id' => $model->id])->all(),
                            'id',
                            'name'
                        )
                    )
                );
            }
            ?>
            <?php BackendWidget::end(); ?>

            <?php
            BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'Images'),
                    'icon' => 'image',
                    'footer' => $this->blocks['submit']
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
                )?>
                <?php
                if (Yii::$app->getModule('elfinder')) {
                    echo \DotPlant\ElFinder\widgets\ElfinderFileInput::widget(
                        ['url' => Url::toRoute(['addImage', 'objId' => $object->id, 'objModelId' => $model->id])]
                    );
                }
                ?>
            </div>

            <?=\app\modules\image\widgets\ImageDropzone::widget(
                [
                    'name' => 'file',
                    'url' => ['/shop/backend-product/upload'],
                    'removeUrl' => ['/shop/backend-product/remove'],
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
                ]
            );?>

            <?php BackendWidget::end(); ?>
        </article>

        <article class="<?= Helper::getBackendGridClass('shop', ShopModule::BACKEND_CATEGORY_GRID, 2) ?>">
            <?php BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'SEO'),
                    'icon' => 'cogs',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

            <?=
            $form->field(
                $model,
                'slug',
                [
                    'makeSlug' => [
                        "#category-name",
                        "#category-title",
                        "#category-h1",
                        "#category-breadcrumbs_label",
                    ]
                ]
            )
            ?>

            <?=
            $form->field(
                $model,
                'h1',
                [
                    'copyFrom' => [
                        "#category-name",
                        "#category-title",
                        "#category-breadcrumbs_label",
                    ]
                ]
            )
            ?>

            <?=
            $form->field(
                $model,
                'breadcrumbs_label',
                [
                    'copyFrom' => [
                        "#category-name",
                        "#category-title",
                        "#category-h1",
                    ]
                ]
            )
            ?>

            <?=$form->field($model, 'meta_description')->textarea()?>

            <?=$form->field($model, 'title_append')?>

            <?php BackendWidget::end(); ?>

            <?=
            \app\properties\PropertiesWidget::widget(
                [
                    'model' => $model,
                    'form' => $form,
                ]
            );
            ?>

        </article>
    </div>
</section>
<?php
$event = new \app\backend\events\BackendEntityEditFormEvent($form, $model);
$this->trigger(BackendCategoryController::BACKEND_CATEGORY_EDIT_FORM, $event);
?>
<?php ActiveForm::end(); ?>
