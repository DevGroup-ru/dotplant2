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
        <div class="span4 well">
            <?=
            $form->field($order, 'shipping_option_id')
                ->radioList(\yii\helpers\ArrayHelper::map($shippingOptions, 'id', 'name'));
            ?>
        </div>
    </div>
    <?php if (!is_null($cart)): ?>
        <?= $this->render('cart-items', ['cart' => $cart, 'hideControls' => true]); ?>
    <?php else: ?>
        <?= $this->render('order-items', ['order' => $order]); ?>
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
<?php \kartik\widgets\ActiveForm::end();
