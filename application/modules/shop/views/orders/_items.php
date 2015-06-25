<?php
/**
 * @var \app\modules\shop\models\Order $model
 * @var \app\modules\shop\models\OrderItem[] $items
 * @var \yii\web\View $this
 */

use app\modules\shop\models\Product;
use kartik\helpers\Html;

$immutable = isset($immutable) && $immutable;
$mainCurrency = \app\modules\shop\models\Currency::getMainCurrency();
$subItems = [];
foreach ($items as $i => $item) {
    if ($item->parent_id != 0) {
        if (isset($subItems[$item->parent_id])) {
            $subItems[$item->parent_id][] = $item;
        } else {
            $subItems[$item->parent_id] = [$item];
        }
        unset($items[$i]);
    }
}

?>
<table class="table table-bordered table-hover" id="cart-table">
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
            <td class="product-image">
                <?=
                \app\modules\image\widgets\ObjectImageWidget::widget([
                    'limit' => 1,
                    'model' => $item->product,
                    'thumbnailOnDemand' => true,
                    'thumbnailWidth' => 140,
                    'thumbnailHeight' => 140,
                ])
                ?>
            </td>
            <td>
                <?= Html::a(
                        Html::encode($item->product->name),
                        \yii\helpers\Url::to([
                            '/shop/product/show',
                            'model' => $item->product,
                            'category_group_id' => $item->product->category->category_group_id,
                        ])
                ); ?>
            </td>
            <td>
                <?=
                $mainCurrency->format(
                    $item->price_per_pcs
                )
                ?>
            </td>
            <td><?= $item->quantity ?></td>
            <td>
                <span class="item-price">
                    <?php
                    if ($item->discount_amount > 0) {
                        echo Html::tag('span',
                            $mainCurrency->format(
                                $item->total_price_without_discount
                            ),
                            [
                                'style' => 'text-decoration: line-through;'
                            ]
                        ).'<br>';
                    }
                    ?>

                    <?=
                    $mainCurrency->format(
                        $item->total_price
                    )
                    ?>
                </span>
            </td>
        </tr>
        <?php if (isset($subItems[$item->product_id])): ?>
            <?=
                $this->render(
                    'sub-items',
                    [
                        'mainCurrency' => $mainCurrency,
                        'model' => $model,
                        'immutable' => $immutable,
                        'items' => $subItems[$item->product_id],
                    ]
                )
            ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php foreach($model->specialPriceObjects as $object): ?>
        <tr class="shipping-data">
            <td colspan="4"><?= $object->name ?></td>
            <td><?= $mainCurrency->format($object->price) ?></td>
        </tr>
    <?php endforeach; ?>


    <tr>
        <td colspan="3"></td>
        <td><strong><span class="items-count"><?= $model->items_count ?></span></strong></td>
        <td>
            <span class="label label-info">
                <span class="total-price ">
                    <?= $mainCurrency->format($model->total_price) ?>
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