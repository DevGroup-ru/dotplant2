<?php

use yii\helpers\Html;
/**
 * @var \app\modules\shop\models\Order $order
 */
/** @var bool $collapseOnSmallScreen */
/** @var bool $useFontAwesome */
/** @var \app\extensions\DefaultTheme\Module $theme */

$mainCurrency = \app\modules\shop\models\Currency::getMainCurrency();

if (is_null($order)) {
    $itemsCount = 0;
} else {
    $itemsCount = $order->items_count;
}

$navStyles = '';

?>

<header class="header one-row-header-with-cart">
    <div class="container">
        <a href="/" class="pull-left logo">
            <img src="<?= Html::encode($theme->logotypePath) ?>" alt="<?= Html::encode($theme->siteName) ?>"/>
        </a>
        <?php if ($collapseOnSmallScreen === true): ?>
            <a href="#" rel="nofollow" class="collapsed nav-sm-open" data-toggle="collapse" data-target="#header-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <?php if ($useFontAwesome):?>
                    <i class="fa fa-bars"></i>
                <?php else: ?>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        <nav class="nav<?=$collapseOnSmallScreen?' collapse ':''?>"<?=$collapseOnSmallScreen?' id="header-navbar-collapse"':''?>>
            <?php if ($collapseOnSmallScreen === true): ?>
            <a href="#" rel="nofollow" class="collapsed nav-sm-close" data-toggle="collapse" data-target="#header-navbar-collapse">
                <span class="sr-only">Close navigation</span>
                <?php if ($useFontAwesome): ?>
                    <i class="fa fa-times"></i>
                <?php else: ?>
                    <span class="icon-times"></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <?=
            \app\widgets\navigation\NavigationWidget::widget([

            ])
            ?>

        </nav>
        <div class="pull-right personal-area">
            
            <?php if (Yii::$app->user->isGuest === true): ?>

                <a href="<?= \yii\helpers\Url::toRoute(['/user/user/signup']) ?>" class="btn btn-signup hidden-xs">
                    <?= Yii::t('app', 'Sign up') ?>
                </a>
                <a href="<?= \yii\helpers\Url::toRoute(['/user/user/login']) ?>" class="btn btn-login">
                    <?= Yii::t('app', 'Login') ?>
                </a>
            
            <?php else: ?>
                <?= Yii::t('app', 'Hi') ?>,
                <a href="<?= \yii\helpers\Url::toRoute(['/shop/cabinet']) ?>" class="link-cabinet"><?= Html::encode(Yii::$app->user->identity->username) ?></a>!
            
            <?php endif; ?>
            
            <a href="<?= \yii\helpers\Url::toRoute(['/shop/cart']) ?>" class="btn btn-show-cart">
                <i class="fa fa-shopping-cart cart-icon"></i>
                <span class="badge">
                    <?= $itemsCount ?>
                </span>
            </a>
        </div>
    </div>
</header>
