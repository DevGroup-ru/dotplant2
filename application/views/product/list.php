<?php

/**
 * @var $breadcrumbs array
 * @var $category_group_id integer
 * @var $object \app\models\Object
 * @var $pages \yii\data\Pagination
 * @var $products \app\models\Product[]
 * @var $selected_category \app\models\Category
 * @var $selected_category_id integer
 * @var $selected_category_ids integer[]
 * @var $selections
 * @var $this yii\web\View
 * @var $title_append string
 * @var $values_by_property_id
 */

use yii\helpers\Url;

$this->title = $selected_category->title;
$this->params['breadcrumbs'] = $breadcrumbs;
$listView = isset($_COOKIE['listViewType']) && $_COOKIE['listViewType'] == 'listView';

?>
<small class="pull-right"> <?= Yii::t('shop', '{n} products are available', ['n' => $pages ->totalCount]) ?> </small>
<h1> <?= $selected_category->h1 ?></h1>
<hr class="soft"/>
<?= $selected_category->announce ?>
<hr class="soft"/>
<form class="form-horizontal span6">
    <div class="control-group">
        <label class="control-label alignL"><?= Yii::t('shop', 'Sort by') ?></label>
        <select>
            <option><?= Yii::t('app', 'Date') ?></option>
        </select>
    </div>
</form>

<div id="myTab" class="pull-right">
    <a href="#listView" data-toggle="tab"><span class="btn btn-large"><i class="icon-list"></i></span></a>
    <a href="#blockView" data-toggle="tab"><span class="btn btn-large btn-primary"><i class="icon-th-large"></i></span></a>
</div>
<br class="clr"/>
<div class="tab-content">
    <div class="tab-pane <?=  $listView ? 'active' : '' ?>" id="listView">
        <?php foreach ($products as $product): ?>
            <?php
                $url = Url::to(
                    [
                        'product/show',
                        'model' => $product,
                        'properties' => $values_by_property_id,
                        'category_group_id' => $category_group_id,
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
                            'properties' => $values_by_property_id,
                            'category_group_id' => $category_group_id,
                        ]
                    );
                ?>
                <?= $this->render('item', ['product' => $product, 'url' => $url]) ?>
            <?php endforeach; ?>
        </ul>
        <hr class="soft"/>
    </div>
</div>
<!--            <a href="compair.html" class="btn btn-large pull-right">Compair Product</a>-->
<div class="content"><?= $selected_category->content ?></div>
<div class="pagination">
    <?php if ($pages->pageCount > 1):
        $_GET = $selections;
    ?>
        <?=
            yii\widgets\LinkPager::widget([
                'pagination' => $pages,
            ]);
        ?>
    <?php endif; ?>
</div>
<br class="clr"/>
<script>
    function setCookie(name, value){
        var valueEscaped = escape(value);
        var expiresDate = new Date();
        expiresDate.setTime(expiresDate.getTime() + 365 * 24 * 60 * 60 * 1000);
        var expires = expiresDate.toGMTString();
        var newCookie = name + "=" + valueEscaped + "; path=/; expires=" + expires;
        if (valueEscaped.length <= 4000) document.cookie = newCookie + ";";
    }
    jQuery('#myTab a').click(function() {
        var $link = jQuery(this);
        setCookie('listViewType', $link.attr('href').substr(1));
        return true;
    });
</script>