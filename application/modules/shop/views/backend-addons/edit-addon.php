<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\AddonCategory $model
 */
/** @var \app\modules\shop\models\AddonCategory $addonCategory */
use app\backend\widgets\BackendWidget;
use kartik\widgets\ActiveForm;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = Yii::t('app', 'Addon edit');
$this->params['breadcrumbs'][] = [
    'url' => 'index',
    'label' => Yii::t('app', 'Addon categories')
];
$this->params['breadcrumbs'][] = [
    'url' => ['view-category', 'id' => $addonCategory->id],
    'label' => Yii::t('app', 'Addons for category "{category}"', ['category' => Html::encode($addonCategory->name)])
];
$this->params['breadcrumbs'][] = $this->title;

?>


<?=app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(
    [
        'id' => 'addon-form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'action'=>'edit-addon?addon_category_id='.$addonCategory->id.($model->isNewRecord?'':'&id='.$model->id)
    ]
); ?>

<?php $this->beginBlock('submit'); ?>
    <?= \app\backend\components\Helper::saveButtons($model) ?>
<?php $this->endBlock(); ?>


<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Addon'), 'icon' => 'cart-plus', 'footer' => $this->blocks['submit']]
            ); ?>

            <?=$form->field($model, 'name')?>

            <?= $form->field($model, 'price',[
                'addon' => [
                    'append' => [
                        'content' => Html::activeDropDownList($model, 'currency_id', app\modules\shop\models\Currency::getSelection()),
                    ],
                ],
            ])?>

            <?= $form->field($model, 'price_is_multiplier')->widget(\kartik\switchinput\SwitchInput::className()) ?>

            <?= $form->field($model, 'add_to_order')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($model, 'can_change_quantity')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?=
            $form->field($model, 'measure_id')
                ->dropDownList(
                    \app\components\Helper::getModelMap(\app\modules\shop\models\Measure::className(), 'id', 'name')
                );
            ?>


            <?php BackendWidget::end(); ?>

        </article>




    </div>
</section>

<?php ActiveForm::end(); ?>

