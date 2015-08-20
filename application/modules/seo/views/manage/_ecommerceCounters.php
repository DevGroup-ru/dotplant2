<!-- E-commerce counters -->
<script type="text/javascript">
    window.onload = function() {
        <?php if (1 === intval($config['google']['active'])): ?>
            <?php $_id = empty($config['google']['id']) ? '' : $config['google']['id'] . '.'; ?>
            try {
                ga('<?= $_id; ?>require', 'ecommerce');
                ga('<?= $_id; ?>ecommerce:addTransaction', {
                    'id': '<?= $order['id']; ?>',
                    'revenue': '<?= $order['total']; ?>',
                    'currency': 'RUB'
                });
                <?php foreach ($products as $p): ?>
                ga('<?= $_id; ?>ecommerce:addItem', {
                    'id': '<?= $order['id']; ?>',
                    'name': '<?= $p['name']; ?>',
                    'sku': '<?= $p['name']; ?>',
                    'category': '<?= $p['category']; ?>',
                    'price': '<?= $p['price']; ?>',
                    'quantity': '<?= $p['qnt']; ?>',
                    'currency': 'RUB'
                });
                <?php endforeach; ?>
                ga('<?= $_id; ?>ecommerce:send');
                ga('<?= $_id; ?>ecommerce:clear');
            } catch (error) {}
        <?php endif; ?>


        <?php if (1 === intval($config['yandex']['active']) && !empty($config['yandex']['id'])): ?>
        <?php $_id = $config['yandex']['id']; ?>
            try {
                <?= $_id; ?>.reachGoal('ORDER',
                    {
                        order_id: "<?= $order['id']; ?>",
                        order_price: <?= $order['total']; ?>,
                        currency: "RUR",
                        exchange_rate: 1,
                        goods: [
                            <?php foreach ($products as $p): ?>
                            {
                                id: "<?= $p['id']; ?>",
                                name: "<?= $p['name']; ?>",
                                price: <?= $p['price']; ?>,
                                quantity: <?= $p['qnt']; ?>
                            },
                            <?php endforeach; ?>
                        ]
                    }
                );
            } catch (error) {}
        <?php endif; ?>
    }
</script>
<!-- /E-commerce counters -->
