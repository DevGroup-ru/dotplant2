<h1><?= Yii::t('app', 'Error report') ?> <?= date(DateTime::RFC1123, time()) ?></h1>

<ul>
<?php foreach ($info as $url => $details) { ?>
    <li><?= $url ?><ul>
    <?php foreach($details as $date => $content) { ?>
        <li><?= $date ?>
            <ul>
                <?php foreach($content as $key => $value) { ?>
                    <li><?= $key ?>: <?= $value ?></li>
                <?php } ?>
            </ul>
        </li>
    <?php } ?>
    </ul></li>
<?php } ?>
</ul>