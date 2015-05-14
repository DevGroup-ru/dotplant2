<div id="sidebar" class="col-md-3">
    <?php if ($this->beginCache(
        'SidebarMenu',
        [
            'duration' => 86400,
            'dependency' => new yii\caching\TagDependency(
                [
                    'tags' => \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(
                        \app\modules\shop\models\Category::className()
                    )
                ]
            )
        ]
    )
    ): ?>
        <?=\yii\widgets\Menu::widget(
            [
                'id' => 'sideMenu',
                'options' => [
                    'class' => 'nav nav-pills nav-stacked',
                ],
                'items' => \app\modules\shop\models\Category::getMenuItems(1),
            ]
        )?>
        <?php $this->endCache();
    endif; ?>
    <?php if (isset($this->blocks['filters'])): ?>
        <h5><?=Yii::t('app', 'Filters')?></h5>
        <?=$this->blocks['filters']?>
        <br />
    <?php endif; ?>
    <br />
    <?php
    $product = \app\modules\shop\models\Product::findOne(['id' => 1]);
    if (!is_null($product)):
        $url = \yii\helpers\Url::to(
            [
                'product/show',
                'model' => $product,
            ]
        );
        ?>
        <div class="thumbnail">
            <a href="<?=$url?>">
                <?=
                \app\modules\image\widgets\ObjectImageWidget::widget(
                    [
                        'limit' => 1,
                        'model' => $product,
                    ]
                )
                ?>
            </a>

            <div class="caption">
                <h5><a href="<?=$url?>"><?=\yii\helpers\Html::encode($product->name)?></a></h5>

                <p>
                    <?=$product->announce?>
                </p>
                <h4 style="text-align:center">
                    <a class="btn" href="#" data-action="add-to-cart" data-id="<?=$product->id?>"><?=Yii::t(
                            'app',
                            'Add to'
                        )?> <i class="icon-shopping-cart"></i></a>
                    <button class="btn btn-primary"><?=Yii::$app->formatter->asDecimal(
                            $product->price,
                            2
                        )?> <?=Yii::$app->params['currency']?></button>
                </h4>
            </div>
        </div>
    <?php endif; ?>
    <br />

    <div class="thumbnail">
        <img src="/demo/images/payment_methods.png" title="Bootshop Payment Methods" alt="Payments Methods">

        <div class="caption">
            <h5><?=Yii::t('app', 'Payment methods')?></h5>
        </div>
    </div>
</div>