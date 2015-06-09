<?php

/**
 * @var $product \app\modules\shop\models\Product
 * @var $this \yii\web\View
 * @var $url string
 */

use app\modules\image\widgets\ObjectImageWidget;
use kartik\helpers\Html;

?>
<div class="col-md-6 col-lg-4 col-sm-6 col-xs-12">
    <div class="product-item">

        <div class="product-image">
            <?=
            ObjectImageWidget::widget(
                [
                    'limit' => 1,
                    'model' => $product,
                ]
            )
            ?>
        </div>
        <div class="product">
            <a href="<?=$url?>" class="product-name">
                <?= Html::encode($product->name) ?>
            </a>
            <div class="product-price">
                <?=$product->formattedPrice(null, false, false)?>
            </div>
        </div>
        <div class="product-info">
            <div class="product-announce">
                <?=$product->announce?>
            </div>
            <div class="cta">
                <a class="btn btn-add-to-cart" href="#" data-action="add-to-cart" data-id="<?=$product->id?>">
                    <?= Yii::t('app', 'Add to') ?>
                    <i class="fa fa-shopping-cart"></i>
                </a>
            </div>
        </div>
    </div>
</div>
