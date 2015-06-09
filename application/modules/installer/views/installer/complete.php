<?php

use yii\helpers\Html;

?>
<h1>
    <?= Yii::t('app', 'Installation complete') ?>
</h1>

<div class="text-center">
    <?= Yii::t('app', 'Now you can proceed to your site') ?>
</div>

<div class="text-center">
    <?= Html::a(
        Yii::t('app', 'Open site frontend'),
        '/',
        [
            'class' => 'btn btn-success',
        ]
    )?>
    <?= Html::a(
        Yii::t('app', 'Open backend'),
        '/backend/',
        [
            'class' => 'btn btn-primary btn-lg',
        ]
    )?>
</div>