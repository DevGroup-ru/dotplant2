<?php

/**
 * @var array $groups
 * @var \yii\web\View $this
 */

?>
<?php if (!is_null($groups)): ?>
    <?php foreach($groups as $group): ?>
        <div class="rating-show-widget">
            <span><?= $group['name'] ?></span>
            <input type="hidden" class="rating" data-filled="symbol symbol-filled" data-empty="symbol symbol-empty" readonly="readonly" value="<?= $group['rating']; ?>" /> (<?= $group['votes']; ?>)
        </div>
    <?php endforeach; ?>
    <?php else: ?>
    <p><?= Yii::t('app', 'There\'s no votes yet. Be first!') ?></p>
<?php endif; ?>
