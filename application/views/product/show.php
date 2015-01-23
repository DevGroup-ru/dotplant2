<?php

/**
 * @var $breadcrumbs array
 * @var $category_group_id integer
 * @var $model \app\models\Product
 * @var $object \app\models\Object
 * @var $selected_category \app\models\Category
 * @var $selected_category_id integer
 * @var $selected_category_ids integer[]
 * @var $this yii\web\View
 * @var $values_by_property_id integer
 */

use app\models\Image;
use app\models\Product;
use app\widgets\ImgSearch;
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
            <a href="<?= $images[0]->image_src ?>">
                <img src="<?= Yii::$app->request->baseUrl.$images[0]->thumbnail_src ?>" alt="<?= $images[0]->image_description ?>" />
            </a>
        <?php endif; ?>
        <div id="differentview" class="moreOptopm carousel slide">
            <div class="carousel-inner">
                <div class="item active">
                    <?=
                        ImgSearch::widget(
                            [
                                'limit' => 3,
                                'offset' => 1,
                                'objectId' => $model->object->id,
                                'objectModelId' => $model->id,
                                'viewFile' => 'img-thumbnail',
                            ]
                        )
                    ?>
                </div>
                <div class="item">
                    <?=
                        ImgSearch::widget(
                            [
                                'offset' => 4,
                                'objectId' => $model->object->id,
                                'objectModelId' => $model->id,
                                'viewFile' => 'img-thumbnail',
                            ]
                        )
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="span6">
        <h1 itemprop="name"><?= Html::encode($model->h1) ?></h1>
        <hr class="soft">
        <form class="form-horizontal qtyFrm" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <div class="control-group">
                <label class="control-label"><span itemprop="price"><?= Yii::$app->formatter->asDecimal($model->price, 2) ?> <?= Yii::$app->params['currency'] ?></span></label>
                <div class="controls">
<!--                    <input type="number" class="span1" placeholder="Qty.">-->
                    <div class="pull-right" style="text-align: right;">
                        <?=
                        \kartik\helpers\Html::a(
                            Yii::t(
                                'shop',
                                'Add to compare'
                            ),
                            [
                                '/product-compare/add',
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
                        <button type="submit" class="btn btn-large btn-primary" data-action="add-to-cart" data-id="<?= $model->id ?>"><?= Yii::t('shop', 'Add to') ?> <i class=" icon-shopping-cart"></i></button>
                    </div>
                </div>
            </div>
        </form>

        <hr class="soft">
<!--        <h4>100 items in stock</h4>-->
<!--        <hr class="soft clr">-->
        <div itemprop="description">
            <?= $this->blocks['announce'] ?>
        </div>
<!--        <a class="btn btn-small pull-right" href="#detail">More Details</a>-->
<!--        <br class="clr">-->
        <hr class="soft">
    </div>
    <div class="span9">
    <ul id="productDetail" class="nav nav-tabs">
        <li class="active"><a href="#home" data-toggle="tab"><?= Yii::t('shop', 'Product details') ?></a></li>
        <li class=""><a href="#profile" data-toggle="tab"><?= Yii::t('shop', 'Related products') ?></a></li>
        <li class=""><a href="#properties" data-toggle="tab"><?= Yii::t('shop', 'Properties') ?></a></li>
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
        <?= $this->blocks['content'] ?>
    </div>
    <div class="tab-pane fade" id="profile">
    <div id="myTab" class="pull-right">
        <a href="#listView" data-toggle="tab"><span class="btn btn-large"><i class="icon-list"></i></span></a>
        <a href="#blockView" data-toggle="tab"><span class="btn btn-large btn-primary"><i class="icon-th-large"></i></span></a>
    </div>
    <br class="clr">
    <hr class="soft">
    <div class="tab-content">
        <div class="tab-pane <?=  $listView ? 'active' : '' ?>" id="listView">
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
                <?= $this->render('item-row', ['product' => $product, 'url' => $url]) ?>
            <?php endforeach; ?>
        </div>
        <div class="tab-pane <?=  !$listView ? 'active' : '' ?>" id="blockView">
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
                    <?= $this->render('item', ['product' => $product, 'url' => $url]) ?>
                <?php endforeach; ?>
            </ul>
            <hr class="soft"/>
        </div>
    </div>
    </div>
    </div>
</div>