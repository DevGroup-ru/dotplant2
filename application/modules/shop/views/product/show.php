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
use app\modules\shop\widgets\AddToWishlistWidget;

$this->title = $this->blocks['title'];
$this->params['breadcrumbs'] = $breadcrumbs;
$listView = isset($_COOKIE['listViewType']) && $_COOKIE['listViewType'] == 'listView';

$propertiesShowedInAnnounce = false;
?>
<div class="row product-show" itemscope itemtype="http://schema.org/Product">
    <div class="col-md-6 col-sm-6 col-xs-12 col-lg-4">
        <div class="product-images">
            <div class="first-image">
                <?=
                ObjectImageWidget::widget(
                    [
                        'limit' => 1,
                        'model' => $model,
                    ]
                )
                ?>
            </div>
            <?php if (count($model->images)>1): ?>
            <div class="other-images">
                <?=
                ObjectImageWidget::widget(
                    [
                        'model' => $model,
                    ]
                )
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12 col-lg-8">
        <h1 itemprop="name">
            <?=Html::encode($model->h1)?>
        </h1>

        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-8">
                <div itemprop="description">
                    <?php
                    if (empty($this->blocks['announce'])) {
                        $propertiesShowedInAnnounce = true;
                        echo \app\properties\PropertiesWidget::widget(
                            [
                                'model' => $model,
                                'viewFile' => 'show-properties-widget',
                            ]
                        );
                    } else {
                        echo $this->blocks['announce'];
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-4">
                <div class="price-block">
                    <?php if ($model->price < $model->old_price): ?>
                        <div class="old">
                            <div class="price-name">
                                <?= Yii::t('app', 'Old price:') ?>
                            </div>
                            <div class="price">
                                <?=$model->nativeCurrencyPrice(true, false)?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="price-name">
                        <?= Yii::t('app', 'Price:') ?>
                        <div class="price">
                            <?=$model->nativeCurrencyPrice(false, true)?>
                        </div>
                    </div>

                </div>
                <div class="cta">
                    <a href="#" class="btn btn-add-to-cart" data-action="add-to-cart" data-id="<?=$model->id?>">
                        <?=Yii::t('app', 'Add to')?> <i class="fa fa-shopping-cart"></i>
                    </a>
                    <br/>
                    <a href='#' class="btn-add-to-compare" rel="nofollow" data-action="add-to-compare" data-id="<?=$model->id?>">
                        <?= Yii::t('app', 'Add to compare') ?>
                    </a>
                    <br/>
                    <a href='#' class="btn-add-to-wishlist" rel="nofollow" data-toggle="modal" data-target="#wishlist">
                        <?=Yii::t('app', 'Add to wishlist')?>
                    </a>
                    <?= AddToWishlistWidget::widget(['id' => $model->id]) ?>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php
            $tabs = [];
            if ($propertiesShowedInAnnounce === false) {
                $tabs[] = [
                    'label' => Yii::t('app', 'Properties'),
                    'content' =>
                        \app\properties\PropertiesWidget::widget(
                            [
                                'model' => $model,
                                'viewFile' => 'show-properties-widget',
                            ]
                        ),
                    'active' => true,
                ];
            }
            if (!empty($this->blocks['content'])) {
                $tabs[] = [
                    'label' => Yii::t('app', 'Description'),
                    'content' => $this->blocks['content'],
                    'options' => [
                        'class' => 'description-tab'
                    ]
                ];
            }
            $tabs[] = [
                'label' => Yii::t('app', 'Reviews'),
                'content' => \app\modules\review\widgets\ReviewsWidget::widget(
                    [
                        'model' => $model,
                        'formId' => 1,
                        'ratingGroupName' => 'First',
                        'additionalParams' => [
                            'model' => $model,
                        ],
                    ]
                )
            ];
        ?>

        <?= \yii\bootstrap\Tabs::widget([
            'items' => $tabs,
            'options' => [
                'class' => 'product-tabs',
            ]
        ]) ?>
    </div>
</div>


<?php
app\slider\sliders\slick\SlickAsset::register($this);
$js = <<<JS
$('.other-images').slick({
    speed: 300,
    variableWidth: true,
    arrows: true,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
    ]
});
$(".other-images").on('click', '.slick-slide', function(){
    var that = $(this),
        img = that.find('img');

    $(".first-image img").attr('src', img.attr('src'));

});
JS;
$this->registerJs($js);
