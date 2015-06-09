<?php
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var \app\models\Search $model
 * @var \yii\web\View $this
 */
$this->title = Yii::t('app', 'Search') . ': ' . $model->q;
?>
    <h1 style="font-size: 24px; font-weight: normal;"><?=Yii::t('app', 'Search results')?></h1>
    <div id="pages-list">
        <div style="text-align: center;">

        </div>
    </div>
    <h3 style="font-size: 16px; font-weight: bold;"><?=Yii::t('app', 'Products and services')?></h3>
    <div id="products-list">
        <div style="text-align: center;">

        </div>
    </div>
<?php
$pageUrl = Json::encode(Url::to(['/page/page/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]));
$productUrl = Json::encode(Url::to(['/shop/product/search', \yii\helpers\Html::getInputName($model, 'q') => $model->q]));
$js = <<<JS
/*global $:false, bootbox, console, alert, document */
    "use strict";


    $(function () {
        function load(url, element) {
            $.ajax({
                url: url
            }).done(function (data) {
                element.empty().append($(data.view));
                if (data.totalCount > 0) {
                    $(".no-results").hide();
                }
            });
        }

        load($pageUrl, $('#pages-list'));
        load($productUrl, $('#products-list'));

        $('#products-list').on('click', '.pagination a', function () {
            load($(this).attr('href'), $('#products-list'));
            return false;
        });

        $('#pages-list').on('click', '.pagination a', function () {
            load($(this).attr('href'), $('#pages-list'));
            return false;
        });
    });
JS;
$this->registerJs($js);


$js = <<<JS
$(".product-item .product-image,.product-item .product-announce").click(function(){
    var that = $(this),
        parent = null;
    if (that.hasClass('product-image')) {
        parent = that.parent();
    } else {
        parent = that.parent().parent();
    }

    document.location = parent.find('a.product-name').attr('href');
    return false;
})
JS;
$this->registerJs($js);

