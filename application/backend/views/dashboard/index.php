<?php

/**
 * @var $this yii\web\View
 */

use yii\bootstrap\Alert;

?>
<h1><?= Yii::t('app', 'Welcome to DotPlant2 administration panel!') ?></h1>

<?= \app\backend\widgets\DoublesFinder\DoublesFinder::widget() ?>

<?php
Alert::begin(
    [
        'closeButton' => false,
        'options' => [
            'class' => 'alert-info',
        ],
    ]
);
?>

<p><?= Yii::t('app', 'Path to web root') ?>: <code><?= Yii::getAlias('@webroot'); ?></code>.</p>

<?php Alert::end() ?>
