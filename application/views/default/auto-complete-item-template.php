<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Product $product
 */
if (!is_object($product->getMainCategory())) {
    return;
}
$url = \yii\helpers\Url::to(
    [
        '/shop/product/show',
        'model' => $product,
        'category_group_id' => $product->getMainCategory()->category_group_id,
    ]
);
?>
<li><a href="<?= $url ?>"><?= $product->name ?></a></li>