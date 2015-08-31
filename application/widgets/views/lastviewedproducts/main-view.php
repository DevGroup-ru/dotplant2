<?php

use yii\helpers\Url;

?>

<div class="last-viewed-products">
    <?=isset($title) ? "<h3>" . Yii::t('app', $title) . "</h3>" : ""?>
    <div class="container">
        <div class="row">
            <?php $counter = 0; ?>
            <?php foreach ($products as $product) { ?>
                <?php
                if (isset($elementNumber)) {
                    if ($elementNumber < ++ $counter) {
                        break;
                    }
                }
                ?>
                <div class="col-xs-3">
                    <?php
                    $url = Url::to(
                        [
                            '/shop/product/show',
                            'model' => $product,
                            'last_category_id' => $product->main_category_id,
                            'category_group_id' => $product->category->category_group_id,
                        ]
                    );
                    ?>
                    <a href="<?=$url?>" class="thumbnail">
                        <?=app\modules\image\widgets\ObjectImageWidget::widget(['model' => $product])?>
                        <div class='name'><?=$product->name?></div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>