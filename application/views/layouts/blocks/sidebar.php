<div id="sidebar" class="span3">
    <?=
        \yii\widgets\Menu::widget(
            [
                'id' => 'sideManu',
                'options' => [
                    'class' => 'nav nav-tabs nav-stacked',
                ],
                'items' => \app\models\Category::getMenuItems(),
            ]
        )
    ?>
    <?php if (isset($this->blocks['filters'])): ?>
        <h5><?= Yii::t('app', 'Filters') ?></h5>
        <?= $this->blocks['filters'] ?>
        <br />
    <?php endif; ?>
    <br/>
    <?php
        $product = \app\models\Product::findOne(['id' => 1]);
        if (!is_null($product)):
            $url = \yii\helpers\Url::to(
                [
                    'product/show',
                    'model' => $product,
                ]
            );
    ?>
        <div class="thumbnail">
            <a href="<?= $url ?>">
                <?=
                    \app\widgets\ImgSearch::widget(
                        [
                            'limit' => 1,
                            'objectId' => $product->object->id,
                            'objectModelId' => $product->id,
                        ]
                    )
                ?>
            </a>
            <div class="caption">
                <h5><a href="<?= $url ?>"><?= \yii\helpers\Html::encode($product->name) ?></a></h5>
                <p>
                    <?= $product->announce ?>
                </p>
                <h4 style="text-align:center">
                    <a class="btn" href="#" data-action="add-to-cart" data-id="<?= $product->id ?>"><?= Yii::t('shop', 'Add to') ?> <i class="icon-shopping-cart"></i></a>
                    <button class="btn btn-primary"><?= Yii::$app->formatter->asDecimal($product->price, 2) ?> <?= Yii::$app->params['currency'] ?></button>
                </h4>
            </div>
        </div>
    <?php endif; ?>
    <br/>
    <div class="thumbnail">
        <img src="/demo/images/payment_methods.png" title="Bootshop Payment Methods" alt="Payments Methods">
        <div class="caption">
            <h5><?= Yii::t('app', 'Payment methods') ?></h5>
        </div>
    </div>
</div>