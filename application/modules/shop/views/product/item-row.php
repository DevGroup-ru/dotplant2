<?php

/**
 * @var $product \app\modules\shop\models\Product
 * @var $this \yii\web\View
 * @var $url string
 */

use app\modules\image\widgets\ObjectImageWidget;
use kartik\helpers\Html;

?>
<div class="row">
    <div class="span2">
        <?=
        ObjectImageWidget::widget(
            [
                'limit' => 1,
                'model' => $product,
            ]
        )
        ?>
    </div>
    <div class="span4">
        <h5><a href="<?=$url?>"><?=Html::encode($product->name)?></a></h5>

        <p>
            <?=$product->announce?>
        </p>
        <a class="btn btn-small pull-right" href="<?=$url?>"><?=Yii::t('app', 'View details')?></a>
        <br class="clr" />
    </div>
    <div class="span3 alignR">
        <form class="form-horizontal qtyFrm">
            <h3><?=$product->formattedPrice(null, false, false)?></h3>
            <!--<label class="checkbox">
                <input type="checkbox">  Adds product to compair
            </label>--><br />

            <a href="#" class="btn btn-large btn-primary" data-action="add-to-cart" data-id="<?=$product->id?>"><?=Yii::t(
                    'app',
                    'Add to'
                )?> <i class="fa fa-shopping-cart"></i></a>
            <a href="<?=$url?>" class="btn btn-large"><i class="icon-zoom-in"></i></a>

        </form>
    </div>
</div>
<hr class="soft" />
