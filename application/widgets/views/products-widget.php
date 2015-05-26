<?php
use yii\helpers\Url;

?>
<?php foreach ($products['products'] as $i => $product): ?>

    <?=
        $this->render(
            $itemView,
            [
                'product' => $product,
                'url' => Url::toRoute(
                    [
                        '/shop/product/show',
                        'model' => $product,
                        'category_group_id' => $category_group_id,
                    ]
                )
            ]
        )
    ?>
<?php endforeach; ?>
