<div><?= Yii::t('shop', 'Hello') ?>, <?= $user?>!</div>
<h2><?= Yii::t('shop', 'Last news') ?>:</h2>
<?php foreach ($news as $info) { ?>
    <h3><?= $info['name'] ?></h3>
    <div><?= $info['announce'] ?></div>
    <div><?= $info['date_added'] ?></div>
    <hr/>
<?php } ?>