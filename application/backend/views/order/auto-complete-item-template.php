<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Product $product
 * @var integer $orderId
 */
$url = \yii\helpers\Url::to(
    [
        'add-product',
        'orderId' => $orderId,
        'productId' => $product->id,
    ]
);
?>
<li><a href="<?= $url ?>"><?= $product->name ?></a></li>