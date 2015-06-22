<?php
    /**
     * @var \yii\web\View $this
     * @var \app\modules\page\models\Page[] $pagelist
     * @var \yii\data\Pagination $pages
     */
?>
<?php if (count($pagelist) > 0): ?>
    <div class="tab-pane active" id="blockView">
        <ul>
            <?php foreach($pagelist as $page): ?>
                <li><a href="<?= \yii\helpers\Url::to(['/page/page/show', 'id' => $page->id]) ?>"><?= $page->breadcrumbs_label; ?></a></li>
            <?php endforeach; ?>
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
    <p class="no-results"><?= Yii::t('app', 'No results found') ?></p>
<?php endif; ?>