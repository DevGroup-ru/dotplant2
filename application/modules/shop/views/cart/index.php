<?php
/**
 * @var \app\modules\shop\models\Order $model
 * @var \app\modules\shop\models\OrderCode $orderCode
 * @var \yii\web\View $this
 */

$this->title = Yii::t('app', 'Cart');

?>
    <h1><?= Yii::t('app', 'Cart') ?></h1>
<?php if (!is_null($model) && $model->items_count > 0): ?>
    <?= $this->render('items', ['model' => $model, 'items' => $model->items]) ?>
    <div class="pull-right">

        <?php if ($orderCode->isNewRecord): ?>
            <?php $form = \kartik\form\ActiveForm::begin(
                [
                    'type' => \kartik\form\ActiveForm::TYPE_INLINE,
                ]
            );
            ?>
            <?= $form->errorSummary($orderCode) ; ?>
            <?= $form->field($orderCode, 'code') ?>
            <?= \kartik\helpers\Html::submitButton(Yii::t('app', 'Apply code'), ['class' => 'btn btn-primary']); ?>
            <?= \kartik\helpers\Html::endForm(); ?>
        <?php else: ?>
            <?= $orderCode->discountCode->code ?>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
    <?= \yii\helpers\Html::a(Yii::t('app', 'Begin order'), ['/shop/cart/stage'], ['class' => 'btn btn-success']); ?>

<?php else: ?>
    <p><?= Yii::t('app', 'Your cart is empty') ?></p>
<?php endif; ?>
<?php
// @todo Move this code to main.js
$this->beginBlock('cart-js')
?>
    jQuery('input[data-type=quantity]').blur(function() {
    var $input = jQuery(this);
    var quantity = parseFloat($input.val());
    var nominal = parseFloat($input.attr('data-nominal'));
    if (isNaN(quantity) || quantity < nominal) {
    quantity = nominal;
    }
    Shop.changeAmount($input.data('id'), quantity, function(data) {
    if (data.success) {
    jQuery('#cart-table .total-price, #cart-info-widget .total-price').text(data.totalPrice);
    jQuery('#cart-table .items-count, #cart-info-widget .items-count').text(data.itemsCount);
    $input.parents('tr').eq(0).find('.item-price').text(data.itemPrice);
    $input.val(quantity);
    }
    });
    });
    jQuery('#cart-table [data-action="change-quantity"]').click(function() {
    var $this = jQuery(this);
    var $input = $this.parents('td').eq(0).find('input[data-type=quantity]');
    var quantity = parseFloat($input.val());
    var nominal = parseFloat($input.attr('data-nominal'));
    if (isNaN(quantity)) {
    quantity = nominal;
    }
    if ($this.hasClass('plus')) {
    quantity += nominal;
    } else {
    if (quantity > nominal) {
    quantity -= nominal;
    }
    }
    Shop.changeAmount($input.data('id'), quantity, function(data) {
    if (data.success) {
    jQuery('#cart-table .total-price, #cart-info-widget .total-price').text(data.totalPrice);
    jQuery('#cart-table .items-count, #cart-info-widget .items-count').text(data.itemsCount);
    $input.parents('tr').eq(0).find('.item-price').text(data.itemPrice);
    $input.val(quantity);
    }
    });
    return false;
    });
<?php $this->endBlock(); ?>
<?php $this->registerJs($this->blocks['cart-js']); ?>