<?php
/** @var string $type */
/** @var string $header  */
/** @var integer $root_category_id */
/** @var integer $category_group_id */
/** @var boolean $display_header */
/** @var boolean $isInSidebar */

use yii\helpers\Html;
$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="categories-list ' . $sidebarClass . '">';

if ($display_header === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
<?php
}

if ($type === 'plain') {
    echo \app\widgets\PlainCategoriesWidget::widget([
        'root_category_id' => $root_category_id,
    ]);
} elseif ($type === 'tree') {
    echo \app\widgets\CategoriesWidget::widget([
        'omit_root' => true,
        'category_group_id' => $category_group_id,
    ]);
}

echo '</div>';