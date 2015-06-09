<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Product $product
 * @var integer $orderId
 * @var integer $parentId
 */
$url = \yii\helpers\Url::to(
    [
        'add-product',
        'orderId' => $orderId,
        'productId' => $product->id,
        'parentId' => $parentId,
    ]
);
?>
<li><a href="<?= $url ?>"><?= $product->name ?></a></li>