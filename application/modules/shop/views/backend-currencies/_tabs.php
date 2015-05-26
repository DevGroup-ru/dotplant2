<?php
/** @var boolean $currencies If currencies is the current route */
?>

<ul class="nav nav-tabs panel-title">
    <li role="presentation"<?= $currencies?' class="active"':''?>>
        <a href="<?= \yii\helpers\Url::toRoute(['/shop/backend-currencies/index']) ?>">
            <?= Yii::t('app', 'Currencies') ?>
        </a>
    </li>
    <li role="presentation"<?= $currencies?'':' class="active"'?>>
        <a href="<?= \yii\helpers\Url::toRoute(['/shop/backend-currency-rate-provider/index']) ?>">
            <?= Yii::t('app', 'Currency rate providers') ?>
        </a>
    </li>
</ul>
