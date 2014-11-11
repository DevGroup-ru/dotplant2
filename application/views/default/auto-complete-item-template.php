<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Product $product
 */
$url = \yii\helpers\Url::to(
    [
        'product/show',
        'model' => $product,
        'last_category_id' => $product->main_category_id,
        'category_group_id' => $product->category->category_group_id,
    ]
);
?>
<li><a href="<?= $url ?>"><?= $product->name ?></a></li>