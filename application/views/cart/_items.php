<?php

/**
 * @var array $items
 * @var float $totalQuantity Total Quantity
 * @var float $totalPrice Total price
 * @var boolean $immutable If we should hide controls like plus, minus, delete item
 * @var \yii\web\View $this
 */
use \app\models\Product;
use kartik\helpers\Html;

$mainCurrency = \app\models\Currency::getMainCurrency();

?>

<table class="table table-bordered" id="cart-table">
    <thead>
    <tr>
        <th></th>
        <th><?=Yii::t('app', 'Name')?></th>
        <th><?=Yii::t('app', 'Price')?></th>
        <th><?=Yii::t('app', 'Quantity')?></th>
        <th><?=Yii::t('app', 'Sum')?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td>
                <?=
                \app\modules\image\widgets\ObjectImageWidget::widget(
                    [
                        'limit' => 1,
                        'model' => $item,
                        'viewFile' => 'img-thumbnail',
                    ]
                )
                ?>
            </td>
            <td><?=$item->product->name?></td>
            <td><?=$item->product->formattedPrice(null, false, false)?></td>

            <td>
                <?php if ($immutable === true): ?>
                    <?=$item->quantity?>
                <?php else: ?>
                    <div class="input-append">
                        <input class="span1" style="max-width:34px" placeholder="1" size="16" type="text" data-type="quantity" data-id="<?=$item->product_id?>" value="<?=$item->quantity?>" />
                        <button class="btn btn-primary minus" type="button" data-action="change-quantity">
                            <i class="fa fa-minus"></i></button>
                        <button class="btn btn-primary plus" type="button" data-action="change-quantity">
                            <i class="fa fa-plus"></i></button>
                        <button class="btn btn-danger" type="button" data-action="delete" data-url="<?=\yii\helpers\Url::toRoute(
                            [
                                'cart/delete',
                                'id' => $item->product_id
                            ]
                        )?>"><i class="fa fa-trash-o"></i></button>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <span class="item-price">
                    <?=
                    $mainCurrency->format(
                        $item->product->convertedPrice() * $item->quantity
                    )
                    ?>
                </span>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (isset($shippingOption)): ?>
        <tr class="shipping-data">
            <td colspan="4"><?=Html::encode($shippingOption->name)?></td>
            <td><?=$mainCurrency->format($shippingOption->cost)?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td colspan="3"></td>
        <td><strong><span class="items-count"><?=$totalQuantity?></span></strong></td>
        <td>
            <span class="label label-info">
                <span class="total-price ">
                    <?=$mainCurrency->format($totalPrice)?>
                </span>

            </span>
        </td>
    </tr>
    </tbody>
</table>
<style>
    @media print {
        header, .header, footer, .footer, .quantity {
            display: none;
        }

        input[data-type=quantity] {
            border: none;
            width: 100px;
        }
    }
</style>