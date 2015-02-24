<?php

/**
 * @var $product \app\models\Product
 * @var $this \yii\web\View
 * @var $url string
 */

use app\widgets\ImgSearch;
use kartik\helpers\Html;

?>
<div class="row">
    <div class="span2">
        <?=
            ImgSearch::widget(
                [
                    'limit' => 1,
                    'objectId' => $product->object->id,
                    'objectModelId' => $product->id,
                ]
            )
        ?>
    </div>
    <div class="span4">
        <h5><a href="<?= $url ?>"><?= Html::encode($product->name) ?></a></h5>
        <p>
            <?= $product->announce ?>
        </p>
        <a class="btn btn-small pull-right" href="<?= $url ?>"><?= Yii::t('shop', 'View details') ?></a>
        <br class="clr"/>
    </div>
    <div class="span3 alignR">
        <form class="form-horizontal qtyFrm">
            <h3><?= $product->formattedPrice(null, false, false) ?></h3>
            <!--<label class="checkbox">
                <input type="checkbox">  Adds product to compair
            </label>--><br/>

            <a href="#" class="btn btn-large btn-primary" data-action="add-to-cart" data-id="<?= $product->id ?>"><?= Yii::t('shop', 'Add to') ?> <i class="fa fa-shopping-cart"></i></a>
            <a href="<?= $url ?>" class="btn btn-large"><i class="icon-zoom-in"></i></a>

        </form>
    </div>
</div>
<hr class="soft"/>