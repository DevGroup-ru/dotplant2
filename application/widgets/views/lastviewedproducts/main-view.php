<?php

use yii\helpers\Url;

?>

<div class="last-viewed-products">
    <?= isset($title) ? "<h3>" . Yii::t('app', $title) . "</h3>" : "" ?>
    <div class="container">
        <div class="row">
            <?php $counter = 0; ?>
            <?php foreach ($products as $product) { ?>
                <?php
                if (isset($elementNumber)) {
                    if ($elementNumber < ++$counter) {
                        break;
                    }
                }
                ?>
                <div class="col-xs-3">
                    <?php
                    $url = Url::to(
                        [
                            'product/show',
                            'model' => $product,
                            'last_category_id' => $product->main_category_id,
                            'category_group_id' => $product->category->category_group_id,
                        ]
                    );
                    ?>
                    <a href="<?= $url ?>" class="thumbnail">
                        <?= app\widgets\ObjectImageWidget::widget(['object_id'=>1, 'object_model_id'=>$product->id, 'displayCountPictures'=>1]) ?>
                        <div class='name'><?= $product->name ?></div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>