<div class="rating-widget">
    <input type="hidden" name="ObjectRating[group]" value="<?= $group['rating_group']; ?>" />
    <?php foreach ($items as $item): ?>
        <div class="rating-row">
            <span><?= $item['name']; ?></span>
            <input type="hidden" class="rating" data-filled="symbol symbol-filled" data-empty="symbol symbol-empty" name="ObjectRating[values][<?= $item['id']; ?>]" data-start="<?= $item['min_value']; ?>" data-stop="<?= $item['max_value']; ?>" data-step="<?= $item['step_value']; ?>" />
        </div>
    <?php endforeach; ?>
</div>