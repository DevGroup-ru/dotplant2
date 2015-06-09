<?php
/** @var \yii\web\View $this */
/** @var \app\modules\page\models\Page $pages */
/** @var boolean $display_header */
/** @var boolean $isInSidebar */
/** @var boolean $display_date */
/** @var string $date_format */
/** @var string $header */
use kartik\icons\Icon;
use yii\helpers\Url;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
?>
<div class="pages-list-widget <?= $sidebarClass ?>">
<?php
if ($display_header === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}
?>
    <ul class="pages-list">
        <?php foreach ($pages as $model): ?>
            <li>
                <?php if ($display_date): ?>
                <div class="page-date_added">
                    <?= date($date_format, strtotime($model->date_added)); ?>
                </div>
                <?php endif; ?>
                <a href="<?= Url::to(['/page/page/show', 'id'=>$model->id])?>" class="page-title">
                    <?= \yii\helpers\Html::encode($model->name) ?>
                </a>
                <div class="page-announce">
                    <?= $model->announce ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php if (!empty($more_pages_label)) : ?>
    <a href="<?= Url::to(['/page/page/list', 'id'=>$model->id]); ?>" class="read-more-link">
        <?= $more_pages_label ?>
    </a>
<?php endif; ?>

</div>