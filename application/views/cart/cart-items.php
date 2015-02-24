<?php

/**
 * @var \app\models\Cart $cart
 * @var \yii\web\View $this
 */

$hideControls = isset($hideControls) && $hideControls;

?>
<table class="table table-bordered" id="cart-table">
    <thead>
    <tr>
        <th></th>
        <th><?=Yii::t('shop', 'Name')?></th>
        <th><?=Yii::t('shop', 'Price')?></th>
        <th><?=Yii::t('shop', 'Quantity')?></th>
        <th><?=Yii::t('shop', 'Sum')?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($cart->items as $productId => $cartOptions): ?>
        <tr>
            <td>
                <?=
                \app\widgets\ImgSearch::widget([
                        'limit' => 1,
                        'objectId' => \app\models\Object::getForClass(\app\models\Product::className())->id,
                        'objectModelId' => $productId,
                        'viewFile' => 'img-thumbnail',
                    ])
                ?>
            </td>
            <td><?=$cart->products[$productId]->name?></td>
            <td><?= $cart->products[$productId]->formattedPrice(null, false, false) ?></td>

            <td>
                <?php if ($hideControls): ?>
                    <?=$cartOptions['quantity']?>
                <?php else: ?>
                    <div class="input-append">
                        <input class="span1" style="max-width:34px" placeholder="1" size="16" type="text" data-type="quantity" data-id="<?=$productId?>" value="<?=$cartOptions['quantity']?>" />
                        <button class="btn btn-primary minus" type="button" data-action="change-quantity">
                            <i class="fa fa-minus"></i></button>
                        <button class="btn btn-primary plus" type="button" data-action="change-quantity">
                            <i class="fa fa-plus"></i></button>
                        <button class="btn btn-danger" type="button" data-action="delete" data-url="<?=\yii\helpers\Url::toRoute([
                            'cart/delete',
                            'id' => $productId
                        ])?>"><i class="fa fa-trash-o"></i></button>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <span class="item-price"><?=Yii::$app->formatter->asDecimal(($cart->products[$productId]->price * $cartOptions['quantity']), 2)?></span> <?=Yii::$app->params['currency']?>
            </td>
        </tr>
    <?php endforeach; ?>
    <tr style="display: none" class="shipping-data">
        <td class="name" colspan="4"></td>
        <td class="price"></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td><strong><span class="items-count"><?=$cart->items_count?></span></strong></td>
        <td>
            <span class="label label-info">
                <span class="total-price "><?=Yii::$app->formatter->asDecimal($cart->total_price, 2)?></span>
                <?=Yii::$app->params['currency']?>
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