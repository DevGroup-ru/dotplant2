<?php

/**
 * @var $model \app\models\Search
 * @var $products \app\models\Product[]
 * @var $this \yii\web\View
 */

?>
<?php if (count($products) > 0): ?>
    <div class="tab-pane  active" id="blockView">
        <ul class="thumbnails">
            <?php
                foreach ($products as $product) {
                    $mainCat = $product->getMainCategory();
                    if (!is_object($product)) {
                        echo "<!-- product is not object -->";
                        continue;
                    }
                    if (!is_object($mainCat)) {
                        echo "<!-- main cat is not object for prodcut id : " . $product->id . ' -->';
                        continue;
                    }

                    $url = \yii\helpers\Url::to(
                        [
                            'product/show',
                            'model' => $product,
                            'category_group_id' => $mainCat->category_group_id,
                        ]
                    );
                    echo $this->render(
                        'item',
                        [
                            'product' => $product,
                            'url' => $url,
                        ]
                    );
                }
            ?>
        </ul>
    </div>
    <?php if ($pages->pageCount > 1): ?>
        <div class="pagination">
            <?=
                \app\widgets\LinkPager::widget(
                    [
                        'firstPageLabel' => '&laquo;&laquo;',
                        'lastPageLabel' => '&raquo;&raquo;',
                        'pagination' => $pages,
                    ]
                );
            ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p class="no-results"><?= Yii::t('shop', 'No results found') ?></p>
<?php endif; ?>