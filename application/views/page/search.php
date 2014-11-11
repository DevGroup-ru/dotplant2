<?php
    /**
     * @var \app\models\Page[] $pages
     */
?>
<?php if (count($pages) > 0): ?>
    <ul>
        <?php foreach($pages as $page): ?>
            <li><a href="<?= \yii\helpers\Url::to(['page/show', 'id' => $page->id]) ?>"><?= $page->breadcrumbs_label; ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="no-results"><?= Yii::t('shop', 'No results found') ?></p>
<?php endif; ?>