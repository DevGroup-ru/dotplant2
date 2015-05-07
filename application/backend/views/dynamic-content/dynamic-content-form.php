<?php
/**
 * @var \yii\web\View $this
 */

use app\backend\widgets\BackendWidget;
use app\backend\widgets\Select2;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Dynamic content edit');
$this->params['breadcrumbs'][] = [
    'url' => ['/backend/dynamic-content/index'],
    'label' => Yii::t('app', 'Dynamic content')
];
$this->params['breadcrumbs'][] = $this->title;
$action = isset($model->id) ? 'edit?id=' . $model->id : 'edit';


$this->registerJs('
     var static_values_properties = '. Json::encode($static_values_properties) .';
     var current_selections = '.( empty($model->apply_if_params)?"{}":$model->apply_if_params ).';',
    \yii\web\View::POS_HEAD,
    'propertyData'
);
\app\backend\assets\PropertyAsset::register($this);


?>


<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(
    ['id' => 'dynamic-content-form', 'type' => ActiveForm::TYPE_VERTICAL, 'action' => $action]
); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/dynamic-content/index', 'id' => $model->id]),
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
</div>
<?php $this->endBlock('submit'); ?>


<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Dynamic Content'), 'icon' => 'cogs', 'footer' => $this->blocks['submit']]
            ); ?>
            <?=$form->field($model, 'object_id')->dropDownList(app\models\Object::getSelectArray())?>
            <?=$form->field($model, 'route')?>
            <?=$form->field($model, 'name')?>
            <?=$form->field($model, 'title')?>
            <?=$form->field($model, 'h1')?>
            <?=$form->field($model, 'meta_description')->textarea()?>

            <?php BackendWidget::end(); ?>

        </article>


        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Content block'), 'icon' => 'cogs', 'footer' => $this->blocks['submit']]
            ); ?>
            <?=$form->field($model, 'content_block_name')?>

            <?=$form->field($model, 'content')->widget(
                ImperaviWidget::className(),
                [
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
                ]
            );?>

            <?php BackendWidget::end(); ?>
        </article>

    </div>
</section>

<input type="hidden" name="DynamicContent[apply_if_params]" id="apply_if_params">


<?php BackendWidget::begin(
    ['title' => Yii::t('app', 'Match settings'), 'icon' => 'cogs', 'footer' => $this->blocks['submit']]
); ?>
<div id="properties">
    <?php
    $url = Url::to(['/backend/category/autocomplete']);
    $initScript = <<< SCRIPT
    function (element, callback) {
        var id=$(element).val();
        if (id !== "") {
            $.ajax("{$url}?id=" + id, {
                dataType: "json"
            }).done(function(data) { callback(data.results);});
        }
    }
SCRIPT;

    ?>
    <?=$form->field($model, 'apply_if_last_category_id')->widget(
        Select2::classname(),
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
                'initSelection' => new JsExpression($initScript)
            ],
        ]
    );
    ?>
    <div class="row">
        <div class="col-md-10 col-md-offset-2">
            <a href="#" class="btn btn-md btn-primary add-property">
                <?=Icon::show('plus')?>
                <?=Yii::t('app', 'Add property')?>
            </a>
            <br>
            <br>
        </div>
    </div>
</div>
<?php BackendWidget::end(); ?>
<?php ActiveForm::end(); ?>


<section style="display: none" data-type="x-tmpl-underscore" id="parameter-template">
    <div class="row form-group parameter">
        <label class="col-md-2 control-label" for="PropertyValue_<%- index %>">
            <select class="property_id">
                <option value="0">- <?=Yii::t('app', 'select')?> -</option>
                <?php foreach ($static_values_properties as $prop) {
        echo "<option value=\"" . $prop['property']->id . "\">" . Html::encode($prop['property']->name) . "</option>";
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
                        <?=Icon::show('thrash-o')?>
                        <?=Yii::t('app', 'Remove')?>
                    </a>
                </span>
            </div>
        </div>
    </div>

</section>
