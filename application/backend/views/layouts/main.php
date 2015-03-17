<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use app\backend\assets\BackendAsset;
use app\backend\widgets\flushcache\FlushCacheButton;
use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

BackendAsset::register($this);
Icon::map($this);

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php if(YII_DEBUG): ?>
        <link rel="stylesheet" href="/css/holmes.min.css" media="screen,projection,print,handheld" type="text/css">
    <?php endif; ?>
    <?php $this->head(); ?>
    <link href="/css/admin.css" media="screen, projection, print" rel="stylesheet" type="text/css" />
</head>
<body class="fixed-header fixed-ribbon">
    <?php $this->beginBody(); ?>
    <header id="header">
        <div id="logo-group">
            <span id="logo" style="width: 115px;"> <!-- <img src="/img/logo.png">  -->DotPlant<sup>2</sup> </span>
            <?php
                echo \app\backend\widgets\Notification::widget();
            ?>
        </div>
        <div class="pull-right">
            <div id="hide-menu" class="btn-header pull-right">
                <span> <a href="javascript:void(0);" data-action="toggleMenu" title="<?= Yii::t('app', 'Collapse Menu') ?>"><i class="fa fa-reorder"></i></a> </span>
            </div>
            <div id="logout" class="btn-header transparent pull-right">
                <span> <a href="/logout" title="<?= Yii::t('app', 'Logout') ?>" data-action="userLogout" data-logout-msg="<?= Yii::t('app', 'Are you sure you want to exit') ?>"><i class="fa fa-sign-out"></i></a> </span>
            </div>
            <!--<form action="search.html" class="header-search pull-right">
                <input id="search-fld"  type="text" name="param" placeholder="Find reports and more">
                <button type="submit">
                    <i class="fa fa-search"></i>
                </button>
                <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
            </form>-->
            <div id="fullscreen" class="btn-header transparent pull-right">
                <span> <a href="javascript:void(0);" data-action="launchFullscreen" title="<?= Yii::t('app', 'Full Screen') ?>"><i class="fa fa-arrows-alt"></i></a> </span>
            </div>
            <?php if(Yii::$app->user->can('cache manage')): ?>
                <div id="flush-cache" class="btn-header transparent pull-right">
                    <span>
                        <?= FlushCacheButton::widget(
                            [
                                'url' => Url::to(['/backend/dashboard/flush-cache']),
                                'htmlOptions' => ['class' => ''],
                                'label' => Icon::show('eraser'),
                                'onSuccess' => 'function(data) {
                                    jQuery.smallBox({
                                        title : "' . Yii::t('app', 'Flush cache information') . '",
                                        content : data,
                                        color : "#5384AF",
                                        icon : "fa fa-info"
                                    });
                                }',
                            ]
                        ) ?>
                    </span>
                </div>
            <?php endif; ?>
            <div class="dropdown btn-header transparent pull-right">
                <span><a data-toggle="dropdown" href="#"><i class="fa fa-trash-o"></i></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= Url::toRoute('/backend/trash/clean'); ?>"><?= Yii::t('shop', 'Clear the cart') ?></a></li>
                    </ul>
                </span>
            </div>
            <div class="btn-header transparent pull-right">
                <span> <a href="/" title="<?= Yii::t('app', 'Go to site') ?>"><i class="fa fa-newspaper-o"></i></a> </span>
            </div>
        </div>
    </header>
    <aside id="left-panel">
            <div class="login-info">
                <span>  
                    
                    <a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
                        <img src="<?= Yii::$app->user->identity->gravatar() ?>" alt="me" class="online" />
                        <span>
                            <?= Yii::$app->user->identity->username ?>
                        </span>
                        <i class="fa fa-angle-down"></i>
                    </a> 
                    
                </span>
            </div>
            <nav>
                <?=
                    app\backend\widgets\Menu::widget([
                        'items' => app\backend\models\BackendMenu::getAllMenu(),
                    ]);
                ?>
            </nav>
            <span class="minifyme" data-action="minifyMenu"> 
                <i class="fa fa-arrow-circle-left hit"></i> 
            </span>
        </aside>
    <div id="main" role="main">
        <div id="ribbon">
            <?= Breadcrumbs::widget([
                'homeLink' => [
                    'label' => Yii::t('app', 'Dashboard'),
                    'url' => Url::to(['/backend'])
                ],
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
        </div>
        <div id="content">
            <?= \app\widgets\Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
    <div class="page-footer">
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <span class="txt-color-white">© DevGroup.ru <?= date("Y") ?></span>
            </div>
            <div class="col-xs-6 col-sm-6 text-right hidden-xs">
                <span class="txt-color-white">
                    Admin theme made on base of <a href="https://wrapbootstrap.com/theme/smartadmin-responsive-webapp-WB0573SK0?ref=dotplant_ru_cms">SmartAdmin</a>
                </span>
                </div>
            </div>
        </div>
    </div>
    <?php $this->endBody(); ?>
    <script type="text/javascript">
        $(function(){
            $('[data-toggle="popover"]').popover({
                container: 'body'
            });
            $('[data-toggle="tooltip"]').tooltip();
            jQuery('[data-action="delete"]').on('click', function(e){
                if (confirm('<?= Yii::t('app', 'Are you sure you want to delete this object?') ?>')) {
                    jQuery.ajax({
                        'type' : 'post',
                        'url' : jQuery(this).attr('href')
                    });
                }
                window.location.reload();
                return false;
            });
        });
    </script>
</body>
</html>
<?php $this->endPage(); ?>
