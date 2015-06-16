<?php
/** @var \yii\web\View $this */
/** @var \app\modules\page\models\Page $pages */
/** @var boolean $displayHeader */
/** @var boolean $isInSidebar */
/** @var boolean $display_date */
/** @var string $date_format */
/** @var integer $parent_id */
/** @var string $header */
use kartik\icons\Icon;
use yii\helpers\Url;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
?>
<div class="pages-list-widget <?= $sidebarClass ?>">
<?php
if ($displayHeader === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}
?>
    <ul class="pages-list">
        <?php foreach ($pages as $page): ?>
            <li>
                <?php if ($display_date): ?>
                <div class="page-date_added">
                    <?= date($date_format, strtotime($page->date_added)); ?>
                </div>
                <?php endif; ?>
                <a href="<?= Url::to(['/page/page/show', 'id'=>$page->id])?>" class="page-title">
                    <?= \yii\helpers\Html::encode($page->name) ?>
                </a>
                <div class="page-announce">
                    <?= $page->announce ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php if (!empty($more_pages_label)) : ?>
    <a href="<?= Url::to(['/page/page/list', 'id'=>$parent_id]); ?>" class="read-more-link">
        <?= $more_pages_label ?>
    </a>
<?php endif; ?>

</div>