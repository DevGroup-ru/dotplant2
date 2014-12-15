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

use \app\models\UserPreferences;
use yii\helpers\Url;
use yii\helpers\Html;



$this->params['breadcrumbs'] = $breadcrumbs;
$listView = UserPreferences::preferences()->getAttributes()['listViewType'];
$this->beginBlock('filters');
echo app\widgets\filter\FilterWidget::widget(
    [
        'objectId' => $object->id,
        'currentSelections' => [
            'properties' => $values_by_property_id,
            'last_category_id' => $selected_category_id,
        ],
        'categoryGroupId' => $category_group_id,
        'title' => null,
    ]
);
$this->endBlock();

?>
<small class="pull-right"> <?= Yii::t('shop', '{n} products are available', ['n' => $pages ->totalCount]) ?> </small>
<h1> <?= $selected_category->h1 ?></h1>
<hr class="soft"/>
<?= $selected_category->announce ?>
<hr class="soft"/>
<form class="form-horizontal span6">
    <div class="control-group">
        <?= Html::activeLabel(UserPreferences::preferences(), 'productListingSortId', ['class'=>'control-label alignL']) ?>
        <?= Html::activeDropDownList(
                UserPreferences::preferences(),
                'productListingSortId',
                \yii\helpers\ArrayHelper::map(\app\models\ProductListingSort::enabledSorts(), 'id', 'name'),
                [
                    'data-userpreference' => 'productListingSortId',
                ]
        ) ?>
    </div>
    <div class="control-group">
        <?= Html::activeLabel(UserPreferences::preferences(), 'productsPerPage', ['class'=>'control-label alignL']) ?>
        <?= Html::activeDropDownList(
            UserPreferences::preferences(),
            'productsPerPage',
            [
                9 => 9,
                18 => 18,
                30 => 30,
            ],
            [
                'data-userpreference' => 'productsPerPage',
            ]
        ) ?>
    </div>
</form>

<div class="pull-right">
    <a href="#" data-dotplant-listViewType="listView"><span class="btn btn-large"><i class="icon-list"></i></span></a>
    <a href="#" data-dotplant-listViewType="blockView"><span class="btn btn-large btn-primary"><i class="icon-th-large"></i></span></a>
</div>
<br class="clr"/>

<div id="<?= ($listView === 'listView' ? 'listView' : 'blockView') ?>">
    <?php
    if ($listView === 'blockView') {
        echo '<ul class="thumbnails">';
    }
    ?>
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
        <?= $this->render(($listView === 'listView' ? 'item-row':'item'), ['product' => $product, 'url' => $url]) ?>
    <?php endforeach; ?>
    <?php
    if ($listView === 'blockView') {
        echo '</ul><hr class="soft">';
    }
    ?>
</div>

<!--            <a href="compair.html" class="btn btn-large pull-right">Compair Product</a>-->

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

<?php if (!isset($_GET['page']) && count($values_by_property_id) === 0): ?>
    <div class="content"><?= $selected_category->content ?></div>
<?php endif; ?>
<br class="clr"/>
