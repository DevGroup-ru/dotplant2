<?php

/**
 * @var $order \app\models\Order
 * @var $shippingOptions \app\models\ShippingOption[]
 * @var $this \yii\web\View
 */

use app\models\Property;
use app\models\PropertyGroup;

$this->title = Yii::t('shop', 'Shipping options');

?>
<h1><?= $this->title ?></h1>
<?php
    $form = \kartik\widgets\ActiveForm::begin(
        [
            'id' => 'shipping-option-form',
            'action' => ['/cart/shipping-option', 'id' => $order->id],
            'enableClientValidation' => false,
        ]
    );
?>
    <div class="row">
        <div class="span4 well">
            <?php foreach (PropertyGroup::getForObjectId($order->object->id) as $group): ?>
                <?php if ($group->hidden_group_title == 0): ?>
                    <h4><?= $group->name; ?></h4>
                <?php endif; ?>
                <?php $properties = Property::getForGroupId($group->id); ?>
                <?php foreach ($properties as $property): ?>
                    <?= $property->handler($form, $order->abstractModel, [], 'frontend_edit_view'); ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <div id="shipping_options" class="span4 well">
            <?=
            $form->field($order, 'shipping_option_id')
                ->radioList(\yii\helpers\ArrayHelper::map($shippingOptions, 'id', 'name'));
            ?>
        </div>
    </div>
    <?php if (!is_null($cart)): ?>
        <?=
        $this->render(
            '_items',
            [
                'items' => $cart->toOrderItems(),
                'immutable' => true,
                'totalQuantity' => $cart->items_count,
                'totalPrice' => $cart->total_price,
            ]
        );
        ?>
    <?php else: ?>
        <?=
        $this->render(
            '_items',
            [
                'items' => $order->items,
                'immutable' => false,
                'totalQuantity' => $order->items_count,
                'totalPrice' => $order->fullPrice,
                'shippingOption' => $order->shippingOption,
            ]
        );
        ?>
    <?php endif; ?>
    <?= \kartik\helpers\Html::submitButton(Yii::t('shop', 'Payment'), ['class' => 'btn btn-primary pull-right']); ?>
    <?=
        \kartik\helpers\Html::a(
            Yii::t('shop', 'Print'),
            '#',
            [
                'class' => 'btn btn-default',
                'id' => 'print-page',
            ]
        )
    ?>
<?php \kartik\widgets\ActiveForm::end(); ?>
<script>
    $('#shipping_options input:radio').change(function(){
        Order.getDeliveryPrice( $(this).val())
    });
</script>
