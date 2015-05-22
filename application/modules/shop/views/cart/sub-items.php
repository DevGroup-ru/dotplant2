<?php
/**
 * @var \app\modules\shop\models\Currency $mainCurrency
 * @var \app\modules\shop\models\Order $model
 * @var \app\modules\shop\models\OrderItem[] $items
 * @var \yii\web\View $this
 */

use app\modules\shop\models\Product;
use kartik\helpers\Html;

?>
<?php foreach($items as $item): ?>
    <tr class="warning" style="color: #666; font-size: 12px;">
        <td style="padding-left: 40px;">
            <?=
            \app\modules\image\widgets\ObjectImageWidget::widget([
                'limit' => 1,
                'model' => $item->product,
            ])
            ?>
        </td>
        <td>
            <?= Html::encode($item->product->name) ?>
        </td>
        <td><?= $item->product->formattedPrice(null, false, false) ?></td>
        <td>
            <?php if ($immutable === true): ?>
                <?= $item->quantity ?>
            <?php else: ?>
                <div class="form-inline">
                    <div class="form-group">
                        <div class="btn-group">
                            <input class="form-control input-sm" style="float: left; margin-right: -2px; max-width: 74px;" placeholder="1" size="16" type="text" data-type="quantity" data-id="<?= $item->id ?>" value="<?= $item->quantity ?>" ata-nominal="<?= $item->product->measure->nominal ?>" />
                            <button class="btn btn-primary btn-sm minus" type="button" data-action="change-quantity">
                                <i class="fa fa-minus"></i></button>
                            <button class="btn btn-primary btn-sm plus" type="button" data-action="change-quantity">
                                <i class="fa fa-plus"></i></button>
                            <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-url="<?= \yii\helpers\Url::toRoute([
                                'delete',
                                'id' => $item->id
                            ]) ?>"><i class="fa fa-trash-o"></i></button>
                        </div>
                    </div>
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
