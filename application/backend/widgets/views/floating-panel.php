<?php
use yii\helpers\Html;
use kartik\icons\Icon;

?>

<nav id="dotplant-floating-panel" class="">
    <div class="container-fluid">
        <a href="/backend/" class="navbar-text">DotPlant2</a>
        <div class="navbar-text">

            <?= Yii::t('app', 'Logged as')?>:
            <?= Yii::$app->user->identity->username ?>
            <a href="/logout">
                <?= Icon::show('sign-out') ?>
            </a>
        </div>

        <ul class="nav navbar-nav">
            <li>
                <a href="/backend/">
                    <?= Icon::show('dashboard') ?>
                    <?= Yii::t('app', 'Backend') ?>
                </a>
            </li>
        </ul>

    </div>

</nav>