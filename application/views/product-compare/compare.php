<?php

/**
 * @var $error integer
 * @var $message string
 * @var $object \app\models\Object
 * @var $products \app\models\Product
 * @var $this \yii\web\View
 */

$this->title = Yii::t('app', 'Products comparison');

?>
<?php if (isset($error) && $error): ?>
    <?=$message?>
<?php else: ?>
    <div class="row">
        <div class="span9">
            <?=
            \yii\helpers\Html::a(
                Yii::t('app', 'Print version'),
                [
                    '/product-compare/print',
                ],
                [
                    'class' => 'btn pull-right'
                ]
            )
            ?>
        </div>
    </div>
    <div class="row center-block">
        <?php
        foreach ($products as $product):
            $url = \yii\helpers\Url::to(
                [
                    'product/show',
                    'model' => $product,
                    'last_category_id' => $product->main_category_id,
                    'category_group_id' => $product->category->category_group_id,
                ]
            );
            $img = app\widgets\ObjectImageWidget::widget(
                [
                    'model' => $product,
                    'limit' => 1,
                ]
            );
            ?>
            <div class="span3">
                <div class="row">
                    <div class="span3">
                        <a href="<?=$url?>">
                            <?=$product->name?>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="span3">
                        <?=$img?>
                    </div>
                </div>
                <div class="row">
                    <div class="span3">
                        <dl>
                            <dt><?=Yii::t('app', 'Price')?>:</dt>
                            <dd style="color:green;font-weight:bold; font-size:28px;">
                                <?=$product->nativeCurrencyPrice(false, false)?>

                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="row">
                    <div class="span3">
                        <?=
                        \app\properties\PropertiesWidget::widget(
                            [
                                'model' => $product,
                                'form' => null,
                                'viewFile' => 'show-properties-widget',
                            ]
                        );
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="span3">
                        <?=
                        \kartik\helpers\Html::a(
                            Yii::t('app', 'Delete'),
                            [
                                '/product-compare/remove',
                                'id' => $product->id,
                                'backUrl' => Yii::$app->request->url,
                            ],
                            [
                                'class' => 'btn btn-warning',
                            ]
                        )
                        ?>
                        <br />
                        <br />
                        <a class="btn" href="#" data-action="add-to-cart" data-id="<?=$product->id?>"><?=Yii::t(
                                'app',
                                'Add to'
                            )?> <i class="fa fa-shopping-cart"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <br />
    <div class="row">
        <div class="span9">
            <?=
            \kartik\helpers\Html::a(
                Yii::t('app', 'Remove all'),
                [
                    '/product-compare/remove-all',
                    'backUrl' => Yii::$app->request->url,
                ],
                [
                    'class' => 'btn btn-danger',
                ]
            )
            ?>
        </div>
    </div>
<?php endif; ?>
