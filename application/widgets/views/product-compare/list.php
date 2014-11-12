<?php
use kartik\helpers\Html;
use yii\helpers\Url;

?>
<?php

if (is_array($prods)) {
    echo '<div class="row">';
    $counter = 0;
    foreach ($prods as $prod) {
        if(isset($limit) && ++$counter > $limit) {
            break;
        }
        $url = Url::to(
            [
                'product/show',
                'model' => $prod,
                'last_category_id' => $prod->main_category_id,
                'category_group_id' => $prod->category->category_group_id,
            ]
        );

        $img = app\widgets\ImgSearch::widget(
            [
                'object_id'=>1,
                'object_model_id'=>$prod->id,
                'displayCountPictures'=>1,
                'viewFile' => 'img-thumbnail-list',
            ]
        );
        echo '<div class="col-md-4">';
        echo Html::beginForm('/product-compare/remove', 'get');
            echo Html::hiddenInput('backUrl', Yii::$app->request->url);
            echo Html::hiddenInput('id', $prod->id);
            echo '<a href="' . $url . '" alt="' . $prod->name . '" title="' . $prod->name . '">';
                echo $img;
            echo '</a>';
            echo Html::submitButton('',
                [
                    'class' => 'btn btn-default',
                    'style' => 'height:10px;width:10px;padding:0;background-color:red;',
                    'title' => Yii::t('app', 'Remove')
                ]
            );
        echo Html::endForm();
        echo '</div>';
    }
    echo '</div>';
}
?>
<div class="row">
    <div class="col-md-8">
        <?= Html::beginForm('/product-compare/add', 'get') ?>
        <?= Html::hiddenInput('id', $id) ?>
        <?= Html::hiddenInput('backUrl', Yii::$app->request->url) ?>
        <?= Html::button(Yii::t('shop', 'Add to compare'),
            [
                'id' => 'addToCompare',
                'class' => 'btn btn-primary',
                'style' => 'font-size: 14px;padding: 4px 10px;'
            ]
        ) ?>
        <?= Html::endForm() ?>
    </div>
    <?php if (is_array($prods) && count($prods) > 0) { ?>
    <div class="col-md-8">
        <?= Html::beginForm($comparePage, 'get')?>
        <?= Html::button(Yii::t('shop', 'Compare'),
            [
                'id' => 'do-compare',
                'class' => 'btn btn-primary',
                'style' => 'font-size: 14px;padding: 4px 10px;'
            ]
        ) ?>
        <?= Html::endForm() ?>
    </div>
    <?php } ?>
</div>