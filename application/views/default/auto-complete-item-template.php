<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Product $product
 */
$url = \yii\helpers\Url::to(
    [
        'product/show',
        'model' => $product,
        'category_group_id' => $product->category->category_group_id,
    ]
);
?>
<li><a href="<?= $url ?>"><?= $product->name ?></a></li>