<?php

/**
 * @var $breadcrumbs array
 * @var $category_group_id integer
 * @var $model \app\modules\shop\models\Product
 * @var $object \app\models\Object
 * @var $selected_category \app\modules\shop\models\Category
 * @var $selected_category_id integer
 * @var $selected_category_ids integer[]
 * @var $this yii\web\View
 * @var $values_by_property_id integer
 */

use app\modules\image\models\Image;
use app\modules\shop\models\Product;
use app\modules\image\widgets\ObjectImageWidget;
use kartik\helpers\Html;
use yii\helpers\Url;

$this->title = $this->blocks['title'];
$this->params['breadcrumbs'] = $breadcrumbs;
$listView = isset($_COOKIE['listViewType']) && $_COOKIE['listViewType'] == 'listView';
$products = Product::find()->where(['active' => 1])->limit(3)->all();
$images = Image::getForModel($model->object->id, $model->id);

?>
<div class="row" itemscope itemtype="http://schema.org/Product">
    <div id="gallery" class="span3">
        <?php if (count($images) > 0): ?>
            <a href="<?=$images[0]->src?>">
                <img src="<?=$images[0]->getThumbnail('80x80')?>" alt="<?=$images[0]->image_description?>" />
            </a>
        <?php endif; ?>
        <div id="differentview" class="moreOptopm carousel slide">
            <div class="carousel-inner">
                <div class="item active">
                    <?=
                    ObjectImageWidget::widget(
                        [
                            'limit' => 3,
                            'offset' => 1,
                            'model' => $model,
                        ]
                    )
                    ?>
                </div>
                <div class="item">
                    <?=
                    ObjectImageWidget::widget(
                        [
                            'offset' => 4,
                            'model' => $model,
                        ]
                    )
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="span6">
        <h1 itemprop="name"><?=Html::encode($model->h1)?></h1>
        <hr class="soft">
        <form class="form-horizontal qtyFrm">
            <div class="control-group">
                <label class="control-label">
                    <?php if ($model->price < $model->old_price): ?>
                        <small class="text-muted">
                            <del>
                                <?=$model->formattedPrice(null, true, false)?>
                            </del>
                        </small>
                        <br>
                    <?php endif; ?>
                    <?=$model->formattedPrice()?>
                    <?php if ($model->currency_id !== \app\modules\shop\models\Currency::getMainCurrency()->id): ?>
                        <small class="text-muted">
                            <?=$model->nativeCurrencyPrice(false, false)?>
                        </small>
                    <?php endif; ?>

                </label>

                <div class="controls">
                    <div class="pull-right" style="text-align: right;">
                        <?=
                        \kartik\helpers\Html::a(
                            Yii::t(
                                'app',
                                'Add to compare'
                            ),
                            [
                                '/shop/product-compare/add',
                                'id' => $model->id,
                                'backUrl' => Yii::$app->request->url,
                            ],
                            [
                                'class' => 'btn',
                            ]
                        )
                        ?>
                        <br />
                        <br />
                        <button type="submit" class="btn btn-large btn-primary" data-action="add-to-cart" data-id="<?=$model->id?>">
                            <?=Yii::t('app', 'Add to')?> <i class="fa fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <hr class="soft">
        <div itemprop="description">
            <?=$this->blocks['announce']?>
        </div>

        <hr class="soft">
    </div>
    <div class="span9">
        <ul id="productDetail" class="nav nav-tabs">
            <li class="active"><a href="#home" data-toggle="tab"><?=Yii::t('app', 'Product details')?></a></li>
            <li class=""><a href="#profile" data-toggle="tab"><?=Yii::t('app', 'Related products')?></a></li>
            <li class=""><a href="#properties" data-toggle="tab"><?=Yii::t('app', 'Properties')?></a></li>
        </ul>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade" id="properties">
                <?=
                \app\properties\PropertiesWidget::widget(
                    [
                        'model' => $model,
                        'viewFile' => 'show-properties-widget',
                    ]
                )
                ?>
            </div>
            <div class="tab-pane fade active in" id="home">
                <?=$this->blocks['content']?>
            </div>
            <div class="tab-pane fade" id="profile">
                <div id="myTab" class="pull-right">
                    <a href="#listView" data-toggle="tab"><span class="btn btn-large"><i class="fa fa-list"></i></span></a>
                    <a href="#blockView" data-toggle="tab"><span class="btn btn-large btn-primary"><i class="fa fa-th-large"></i></span></a>
                </div>
                <br class="clr">
                <hr class="soft">
                <div class="tab-content">
                    <div class="tab-pane <?=$listView ? 'active' : ''?>" id="listView">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $url = Url::to(
                                [
                                    'product/show',
                                    'model' => $product,
                                    'category_group_id' => 1,
                                ]
                            );
                            ?>
                            <?=$this->render('item-row', ['product' => $product, 'url' => $url])?>
                        <?php endforeach; ?>
                    </div>
                    <div class="tab-pane <?=!$listView ? 'active' : ''?>" id="blockView">
                        <ul class="thumbnails">
                            <?php foreach ($products as $product): ?>
                                <?php
                                $url = Url::to(
                                    [
                                        'product/show',
                                        'model' => $product,
                                        'category_group_id' => 1,
                                    ]
                                );
                                ?>
                                <?=$this->render('item', ['product' => $product, 'url' => $url])?>
                            <?php endforeach; ?>
                        </ul>
                        <hr class="soft" />
                    </div>
                </div>
            </div>
        </div>
    </div>
<?=
\app\modules\review\widgets\ReviewsWidget::widget(
    [
        'model' => $model,
        'formId' => 2,
        'ratingGroupName' => 'One',
        'additionalParams' => [
            'model' => $model,
        ],
    ]
);
