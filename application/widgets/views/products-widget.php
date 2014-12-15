<?php
use yii\helpers\Url;

?>
<?php foreach ($products['products'] as $i=>$product): ?>

    <?=
        $this->render(
            $itemView,
            [
                'product' => $product,
                'url' => Url::toRoute(
                    [
                        'product/show',
                        'model' => $product,
                    ]
                )
            ]
        )
    ?>
<?php endforeach; ?>