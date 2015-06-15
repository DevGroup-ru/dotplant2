<?php
/** @var string $type */
/** @var string $header  */
/** @var integer $rootCategoryId */
/** @var integer $categoryGroupId */
/** @var boolean $displayHeader */
/** @var boolean $isInSidebar */

use yii\helpers\Html;
$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="categories-list ' . $sidebarClass . '">';

if ($displayHeader === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
<?php
}

if ($type === 'plain') {
    echo \app\widgets\PlainCategoriesWidget::widget([
        'root_category_id' => $rootCategoryId,
    ]);
} elseif ($type === 'tree') {
    echo \app\modules\shop\widgets\CategoriesList::widget([
        'rootCategory' => $rootCategoryId,
    ]);
}

echo '</div>';