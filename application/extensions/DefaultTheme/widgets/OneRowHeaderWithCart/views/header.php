<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
use app\modules\shop\models\Wishlist;
/** @var yii\web\View $this */
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
                <span class="sr-only"><?= Yii::t('app', 'Close navigation') ?></span>
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
                <span class="dropdown">
                    <a href="<?= \yii\helpers\Url::toRoute(['/shop/cabinet']) ?>" class="link-cabinet" data-toggle="dropdown" data-hover="dropdown"><?= Html::encode(Yii::$app->user->identity->username) ?></a>!
                    <?= \yii\widgets\Menu::widget([
                        'items' => [
                            [
                                'label' => Yii::t('app', 'User profile'),
                                'url' => ['/user/user/profile'],
                                [
                                    'class' => 'user-profile-link',
                                ]
                            ],
                            [
                                'label' => Yii::t('app', 'Personal cabinet'),
                                'url' => ['/shop/cabinet'],
                                [
                                    'class' => 'shop-cabinet-link',
                                ]
                            ],
                            [
                                'label' => Yii::t('app', 'Orders list'),
                                'url' => ['/shop/orders/list'],
                                [
                                    'class' => 'shop-orders-list',
                                ]
                            ],
                            [
                                'label' => Yii::t('app', 'Logout'),
                                'url' => ['/user/user/logout'],
                                [
                                    'data-action' => 'post',
                                    'class' => 'logout-link',
                                ],
                            ]
                        ],
                        'options' => [
                            'class' => 'dropdown-menu personal-menu',
                        ],
                    ]) ?>
                </span>
            <?php endif; ?>
            
            <a href="<?= \yii\helpers\Url::toRoute(['/shop/cart']) ?>" class="btn btn-show-cart">
                <i class="fa fa-shopping-cart cart-icon"></i>
                <span class="badge items-count">
                    <?= $itemsCount ?>
                </span>
            </a>
            <a href="<?=Url::to(['/shop/product-compare/compare'])?>" class="btn btn-compare" title="<?=Yii::t('app', 'Compare products')?>">
                <i class="fa fa-tags"></i>
                <span class="badge items-count">
                    <?=count(Yii::$app->session->get('comparisonProductList')) ?>
                </span>
            </a>
            <a href="<?=Url::to(['/shop/wishlist'])?>" class="btn btn-wishlist">
                <i class="fa fa-heart"></i>
                <span class="badge items-count">
                    <?= Wishlist::countItems((!Yii::$app->user->isGuest ? Yii::$app->user->id : 0), Yii::$app->session->get('wishlists', [])) ?>
                </span>
            </a>
        </div>
        <div class="pull-right search-area">

            <?= \app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart\ExpandableSearchField::widget([
                'useFontAwesome' => $useFontAwesome,
            ]) ?>


        </div>
    </div>
</header>

<?php

if (Yii::$app->user->isGuest === false) {
    $js = <<<JS
$('.link-cabinet').dropdownHover();
JS;
    $this->registerJs($js);

}