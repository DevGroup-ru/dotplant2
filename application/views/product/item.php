<?php

/**
 * @var $product \app\models\Product
 * @var $this \yii\web\View
 * @var $url string
 */

use app\widgets\ImgSearch;
use kartik\helpers\Html;

?>
<li class="span3">
    <div class="thumbnail">
        <a href="<?=$url?>">
            <?=
            ImgSearch::widget(
                [
                    'limit' => 1,
                    'objectId' => $product->object->id,
                    'objectModelId' => $product->id,
                ]
            )
            ?>
        </a>

        <div class="caption">
            <h5><a href="<?=$url?>"><?=Html::encode($product->name)?></a></h5>

            <p>
                <?=$product->announce?>
            </p>
            <h4 style="text-align:center">
                <a class="btn" href="#" data-action="add-to-cart" data-id="<?=$product->id?>"><?=Yii::t(
                        'shop',
                        'Add to'
                    )?> <i class="icon-shopping-cart"></i></a>
                <button class="btn btn-primary">
                    <?= $product->formattedPrice(null, false, false) ?>
                </button>
            </h4>
        </div>
    </div>
</li>
