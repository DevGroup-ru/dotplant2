<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\DeliveryInformation $deliveryInformation
 * @var \app\modules\shop\models\OrderDeliveryInformation|\app\properties\HasProperties $orderDeliveryInformation
 * @var \app\properties\AbstractModel $abstractModel
 */

use app\components\Helper;
use app\modules\shop\models\ShippingOption;
use yii\helpers\Html;

/** @var ShippingOption[] $shippingOptions */
$shippingOptions = ShippingOption::find()
    ->where(['active' => 1])
    ->orderBy('sort DESC, id ASC')
    ->all();

?>
<div class="col-md-6 col-md-offset-3">
    <div class="row">
    <?= $form->field($deliveryInformation, 'country_id')->dropDownList(Helper::getModelMap(\app\models\Country::className(), 'id', 'name')); ?>
    <?= $form->field($deliveryInformation, 'city_id')->dropDownList(Helper::getModelMap(\app\models\City::className(), 'id', 'name')); ?>
    <?= $form->field($deliveryInformation, 'zip_code'); ?>
    <?= $form->field($deliveryInformation, 'address')->textarea(); ?>

    <?= $form->field($orderDeliveryInformation, 'shipping_option_id')
        ->dropDownList(\yii\helpers\ArrayHelper::map($shippingOptions, 'id', 'name')); ?>
    <div id="shipping-option-forms">
    <?php
        foreach ($shippingOptions as $shippingOption) {
            echo Html::tag(
                'div',
                $shippingOption->getHandler()->getCartForm($form, $order),
                ['class' => 'hidden', 'data-id' => $shippingOption->id]
            );
        }
    ?>
    </div>
    <?php
        $abstractModel = $orderDeliveryInformation->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr);
        }
    ?>
    </div>
</div>
<?php
$shippingOptionSelectId = '#' . Html::getInputId($deliveryInformation, 'shipping_option_id');
$js = <<<JS
function showShippingForm(id)
{
    var \$forms = jQuery('#shipping-option-forms');
    \$forms.find('>div').addClass('hidden').filter('[data-id="' + id + '"]').removeClass('hidden');
}
showShippingForm(1);
jQuery('#orderdeliveryinformation-shipping_option_id').change(function() {
    showShippingForm(jQuery(this).val());
});
jQuery('#shop-stage').submit(function() {
    jQuery('#shipping-option-forms .hidden').remove();
    return true;
});
JS;
$this->registerJs($js);
