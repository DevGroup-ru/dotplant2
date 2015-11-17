<?php
/**
 * @var \yii\web\View $this
 * @var array $_attr
 * @var array $values
 * @var array $_params
 */
?>
    <offer id="<?= $_attr['id']; ?>" available="<?= $_attr['available']; ?>">
        <?php foreach ($values as $key => $value): ?>
        <<?= $key; ?>><?= $value; ?></<?= $key; ?>>
        <?php endforeach; ?>
        <?php foreach ($_params as $key => $value): ?>
            <?php if (null === $value['unit']): ?>
            <param name="<?= $key; ?>"><?= $value['value']; ?></param>
            <?php else: ?>
            <param name="<?= $key; ?>" unit="<?= $value['unit']; ?>"><?= $value['value']; ?></param>
            <?php endif; ?>
        <?php endforeach; ?>
    </offer>
