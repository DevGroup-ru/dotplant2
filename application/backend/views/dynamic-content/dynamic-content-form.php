<?php
/**
 * @var View $this
 * @var array $static_values_properties
 * @var \app\models\DynamicContent $model
 */

use app\backend\assets\PropertyAsset;
use app\backend\components\Helper;
use app\backend\controllers\DynamicContentController;
use app\backend\events\BackendEntityEditFormEvent;
use app\backend\widgets\BackendWidget;
use app\models\BaseObject;
use app\modules\shop\models\Category;
use app\widgets\Alert;
use kartik\select2\Select2;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\components\ActiveForm;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$this->title = Yii::t('app', 'Dynamic content edit');
$this->params['breadcrumbs'][] = [
    'url' => ['/backend/dynamic-content/index'],
    'label' => Yii::t('app', 'Dynamic content')
];
$this->params['breadcrumbs'][] = $this->title;
$action = isset($model->id) ? 'edit?id=' . $model->id : 'edit';


$this->registerJs('
     var static_values_properties = ' . Json::encode($static_values_properties) . ';
     var current_selections = ' . (empty($model->apply_if_params) ? '{}' : $model->apply_if_params) . ';
     var current_field_id= "apply_if_params"',
    View::POS_HEAD,
    'propertyData'
);
PropertyAsset::register($this);
?>
<?= Alert::widget(['id' => 'alert']) ?>

<?php $form = ActiveForm::begin([
    'id' => 'dynamic-content-form',
    'type' => ActiveForm::TYPE_VERTICAL,
    'action' => $action
]); ?>
<?php $this->beginBlock('submit'); ?>
<?= Helper::saveButtons($model) ?>
<?php $this->endBlock(); ?>
<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin([
                'title' => Yii::t('app', 'Dynamic Content'),
                'icon' => 'cogs',
                'footer' => $this->blocks['submit']
            ]); ?>
            <?= $form->field($model, 'object_id')->dropDownList(BaseObject::getSelectArray()) ?>
            <?= $form->field($model, 'route') ?>
            <?= $form->field($model, 'name') ?>
            <?= $form->field($model, 'title') ?>
            <?= $form->field($model, 'h1') ?>
            <?= $form->field($model, 'meta_description')->textarea() ?>
            <?php BackendWidget::end(); ?>
        </article>
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin([
                'title' => Yii::t('app', 'Content block'),
                'icon' => 'cogs',
                'footer' => $this->blocks['submit']
            ]); ?>
            <?= $form->field($model, 'content_block_name') ?>
            <?= $form->field($model, 'announce')->widget(
                Yii::$app->getModule('core')->wysiwyg_class_name(),
                Yii::$app->getModule('core')->wysiwyg_params()
            ); ?>
            <?= $form->field($model, 'content')->widget(
                Yii::$app->getModule('core')->wysiwyg_class_name(),
                Yii::$app->getModule('core')->wysiwyg_params()
            ); ?>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
</section>
<input type="hidden" name="DynamicContent[apply_if_params]" id="apply_if_params">
<?php BackendWidget::begin([
    'title' => Yii::t('app', 'Match settings'),
    'icon' => 'cogs',
    'footer' => $this->blocks['submit']
]); ?>
<div id="properties">
    <?php
    $url = Url::to(['/shop/backend-category/autocomplete']);
    $category = $model->apply_if_last_category_id > 0
        ? Category::findById($model->apply_if_last_category_id)
        : null;
    ?>
    <?= $form->field($model, 'apply_if_last_category_id')->widget(
        Select2::class,
        [
            'options' => ['placeholder' => 'Search for a category ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(term,page) { return {search:term}; }'),
                    'results' => new JsExpression('function(data,page) { return {results:data.results}; }'),
                ],
            ],
            'initValueText' => null !== $category ? $category->name : '',
        ]
    );
    ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-2">
            <a href="#" class="btn btn-md btn-primary add-property">
                <?= Icon::show('plus') ?>
                <?= Yii::t('app', 'Add property') ?>
            </a>
            <br>
            <br>
        </div>
    </div>
</div>
<?php BackendWidget::end(); ?>
<?php
$event = new BackendEntityEditFormEvent($form, $model);
$this->trigger(DynamicContentController::BACKEND_DYNAMIC_CONTENT_EDIT_FORM, $event);
?>
<?php ActiveForm::end(); ?>
<section style="display: none" data-type="x-tmpl-underscore" id="parameter-template">
    <div class="row form-group parameter">
        <label class="col-md-2 control-label" for="PropertyValue_<%- index %>">
            <select class="property_id form-control">
                <option value="0">- <?= Yii::t('app', 'select') ?> -</option>
                <?php foreach ($static_values_properties as $prop) {
                    echo Html::tag('option', Html::encode($prop['property']->name), ['value' => $prop['property']->id]);
                }
                ?>
            </select>
        </label>
        <div class="col-md-10">
            <div class="input-group">
                <select id="PropertyValue_<%- index %>" class="form-control select">
                </select>
                <span class="input-group-btn">
                    <a class="btn btn-danger btn-remove">
                        <?= Icon::show('thrash-o') ?>
                        <?= Yii::t('app', 'Remove') ?>
                    </a>
                </span>
            </div>
        </div>
    </div>
</section>
