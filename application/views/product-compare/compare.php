<?php

if (isset($error) && $error == 1) {
    echo $message;
} else {

    ?>

    <div class="row">
        <div class="col-md-2 col-md-offset-10">
            <?= \kartik\helpers\Html::beginForm('/product-compare/print', 'get') ?>
            <?= \yii\helpers\Html::submitButton(Yii::t('shop', 'Print version'),
                [
                    'class' => 'btn btn-xs btn-primary'
                ]
            ) ?>
            <?= \yii\helpers\Html::endForm() ?>
        </div>
    </div>

    <div class="row center-block">
    <?php

    foreach ($prods as $prod) {
        $url = \yii\helpers\Url::to(
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
        ?>

        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <a href="<?= $url ?>">
                        <?= $prod->name ?>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $img ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <dt><?= Yii::t('app', 'Price') ?>:</dt>
                    <dd style="color:green;font-weight:bold; font-size:28px;"><?= $prod->price ?></dd>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?=
                    \app\properties\PropertiesWidget::widget(
                        [
                            'model' => $prod,
                            'form' => null,
                            'viewFile' => 'show-properties-widget',
                        ]
                    );
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a href="#" class="btn btn-lg btn-primary btn-red" onclick="Shop.addToCart(<?= $prod->id ?>, '1');">
                        <?= Yii::t('shop', 'Add to cart') ?>
                    </a>
                </div>
            </div>
        </div>

        <?php
    }
    ?>
    </div> <!-- div class="row" -->
    <div class="row">
        <div class="col-md-2 col-md-offset-10">
            <?= \kartik\helpers\Html::beginForm('/product-compare/remove-all', 'get') ?>
            <?= \kartik\helpers\Html::hiddenInput('backUrl', Yii::$app->request->url) ?>
            <?= \yii\helpers\Html::submitButton(Yii::t('shop', 'Remove all'),
                [
                    'class' => 'btn btn-primary'
                ]
            ) ?>
            <?= \yii\helpers\Html::endForm() ?>
        </div>
    </div>
    <?php
}

echo \app\widgets\form\Form::widget(['formId' => 3, 'isModal' => true, 'id' => 'order']);