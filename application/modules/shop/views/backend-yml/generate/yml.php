<?php
/**
 * @var \yii\web\View $this
 * @var array $shop
 * @var array $offers
 * @var \yii\db\Query $categories
 */

    $categories = $shop['categories'];
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL; ?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="<?= date('Y-m-d H:i'); ?>">
    <shop>
        <name><?= htmlspecialchars($shop['name']); ?></name>
        <company><?= htmlspecialchars($shop['company']); ?></company>
        <url><?= htmlspecialchars($shop['url']); ?></url>
        <currencies>
            <currency id="<?= htmlspecialchars($shop['currency']); ?>" rate="1" plus="0"/>
        </currencies>
        <categories>
            <?php foreach($categories->each(300) as $category): ?>
            <category id="<?= $category['id']; ?>" <?= 0 != $category['parent_id'] ? 'parentId="' . $category['parent_id'] . '"' : '';?>>
                <?= htmlspecialchars(trim(strip_tags($category['name']))); ?>
            </category>
            <?php endforeach; ?>
        </categories>
        <local_delivery_cost><?= round(floatval($shop['local_delivery_cost']), 2); ?></local_delivery_cost>
        <store><?= $shop['store']; ?></store>
        <pickup><?= $shop['pickup']; ?></pickup>
        <delivery><?= $shop['delivery']; ?></delivery>
        <adult><?= $shop['adult']; ?></adult>
        <offers>
            <?php
                foreach($offers as $offer) {
                    echo $offer;
                }
            ?>
        </offers>
    </shop>
</yml_catalog>
