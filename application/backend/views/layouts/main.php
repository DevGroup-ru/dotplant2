<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use app\backend\assets\BackendAsset;
use yii\widgets\Breadcrumbs;
use kartik\helpers\Html;
use yii\helpers\Url;
use app\backend\widgets\flushcache\FlushCacheButton;
use kartik\icons\Icon;

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
                if (Yii::$app->user->can('administrate')):
                    $notificationsCount = \app\backend\models\Notification::find()
                        ->where(['user_id' => Yii::$app->user->id, 'viewed' => 0])
                        ->count();
            ?>
                <span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> <?= $notificationsCount ?> </b> </span>
                <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
                <div class="ajax-dropdown">
                    <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <!--<label class="btn btn-default">
                            <input type="radio" name="activity" id="ajax/notify/mail.html">
                            Msgs (0)
                        </label>-->
                        <label class="btn btn-default">
                            <input type="radio" name="activity" id="/backend/dashboard/notifications">
                            notify (<span class="notifications-count"><?= $notificationsCount ?></span>)
                        </label>
                        <!--<label class="btn btn-default">
                            <input type="radio" name="activity" id="ajax/notify/tasks.html">
                            Tasks (0)
                        </label>-->
                    </div>
                    <!-- notification content -->
                    <div class="ajax-notifications custom-scroll">
                        <div class="alert alert-transparent">
                            <h4>Click a button to show messages here</h4>
                            <?php //= Notification::widget(['onSuccess' => "function(data, container) { $('.message-count').text(data); }"]) ?>
                            ToDo: исправить отображение этого виджета
                        </div>
                        <i class="fa fa-lock fa-4x fa-border"></i>
                    </div>
                    <!-- end notification content -->
                    <!-- footer: refresh area -->
                    <span> Last updated on: 00/00/0000 00:00
                        <button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
                            <i class="fa fa-refresh"></i>
                        </button> </span>
                    <!-- end footer -->
                </div>
            <?php endif; ?>
        </div>
        <div class="pull-right">
            <div id="hide-menu" class="btn-header pull-right">
                <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
            </div>
            <div id="logout" class="btn-header transparent pull-right">
                <span> <a href="/logout" title="Sign Out" data-action="userLogout" data-logout-msg="You can improve your security further after logging out by closing this opened browser"><i class="fa fa-sign-out"></i></a> </span>
            </div>
            <!--<form action="search.html" class="header-search pull-right">
                <input id="search-fld"  type="text" name="param" placeholder="Find reports and more">
                <button type="submit">
                    <i class="fa fa-search"></i>
                </button>
                <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
            </form>-->
            <div id="fullscreen" class="btn-header transparent pull-right">
                <span> <a href="javascript:void(0);" data-action="launchFullscreen" title="Full Screen"><i class="fa fa-arrows-alt"></i></a> </span>
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
                        'items' => [
                            [
                                'label' => \Yii::t('app', 'Dashboard'),
                                'url' => ['/backend/dashboard/index'],
                                'icon'=>'dashboard',
                                'active' => Yii::$app->user->can('administrate'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Pages'),
                                'url' => ['/backend/page/index'],
                                'icon'=>'file-o',
                                'active' => Yii::$app->user->can('content manage'),
                            ],
                            [
                                'label' => Yii::t('shop', 'Shop'),
                                'icon'=>'shopping-cart',
                                'active' => Yii::$app->user->can('shop manage'),
                                'items' => [
                                    [
                                        'label' => \Yii::t('app', 'Categories'),
                                        'url' => ['/backend/category/index'],
                                        'icon'=>'tree',
                                    ],
                                    [
                                        'label' => \Yii::t('shop', 'Products'),
                                        'url' => ['/backend/product/index'],
                                        'icon'=>'list',
                                    ],
                                    [
                                        'label' => \Yii::t('shop', 'Orders'),
                                        'url' => ['/backend/order/index'],
                                        'icon'=>'list-alt',
                                    ],
                                    [
                                        'label' => \Yii::t('shop', 'Order statuses'),
                                        'url' => ['/backend/order-status/index'],
                                        'icon'=>'info-circle',
                                        'active' => Yii::$app->user->can('order status manage'),
                                    ],
                                    [
                                        'label' => \Yii::t('shop', 'Payment types'),
                                        'url' => ['/backend/payment-type/index'],
                                        'icon'=>'usd',
                                        'active' => Yii::$app->user->can('payment manage'),
                                    ],
                                    [
                                        'label' => \Yii::t('shop', 'Shipping options'),
                                        'url' => ['/backend/shipping-option/index'],
                                        'icon'=>'car',
                                        'active' => Yii::$app->user->can('shipping manage'),
                                    ],
                                ],
                            ],
                            [
                                'label' => Yii::t('shop', 'Properties'),
                                'icon'=>'cogs',
                                'items' => [
                                    [
                                        'label' => \Yii::t('app', 'Properties'),
                                        'url' => ['/backend/properties/index'],
                                        'icon'=>'cogs',
                                        'active' => Yii::$app->user->can('property manage'),
                                    ],
                                    [
                                        'label' => \Yii::t('app', 'Views'),
                                        'url' => ['/backend/view/index'],
                                        'icon'=>'desktop',
                                        'active' => Yii::$app->user->can('view manage'),
                                    ],
                                ],
                            ],
                            [
                                'label' => \Yii::t('app', 'Reviews'),
                                'url' => ['/backend/review/index'],
                                'icon'=>'comment',
                                'active' => Yii::$app->user->can('review manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Navigation'),
                                'url' => ['/backend/navigation/index'],
                                'icon'=>'navicon',
                                'active' => Yii::$app->user->can('navigation manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Forms'),
                                'url' => ['/backend/form/index'],
                                'icon'=>'list-ul',
                                'active' => Yii::$app->user->can('form manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Dynamic content'),
                                'url' => ['/backend/dynamic-content/index'],
                                'icon'=>'puzzle-piece',
                                'active' => Yii::$app->user->can('content manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Users'),
                                'url' => ['/backend/user/index'],
                                'icon'=>'users',
                                'active' => Yii::$app->user->can('user manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Rbac'),
                                'url' => ['/backend/rbac/index'],
                                'icon'=>'lock',
                                'active' => Yii::$app->user->can('user manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Tasks'),
                                'url' => ['/background/manage/index'],
                                'icon'=>'tasks',
                                'active' => Yii::$app->user->can('task manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Seo'),
                                'url' => ['/seo/manage/index'],
                                'icon' => 'search',
                                'active' => Yii::$app->user->can('seo manage'),
                            ],
                            [
                                'label' => \Yii::t('app', 'Api'),
                                'url' => ['/backend/api/index'],
                                'icon'=>'exchange',
                                'active' => Yii::$app->user->can('api manage')
                            ],
                            /*[
                                'label' => \Yii::t('app', 'Media'),
                                'url' => '#',
                                'icon' => 'image',
                                'active' => Yii::$app->user->can('media manage'),
                                'items' => [
                                    [
                                        'label' => \Yii::t('app', 'Add file'),
                                        'url' => '#',
                                        'options' => [
                                            'data-target' => '#add-attachment-modal',
                                            'data-toggle' => 'modal',
                                        ],
                                    ],
                                    [
                                        'label' => \Yii::t('app', 'Manager'),
                                        'url' => '#',
                                        'options' => [
                                            'data-target' => '#media-manager-modal',
                                            'data-toggle' => 'modal',
                                        ],
                                    ],
                                ],
                            ],*/
                            [
                                'label' => Yii::t('app', 'Error Monitor'),
                                'url' => '#',
                                'icon' => 'flash',
                                'active' => Yii::$app->user->can('monitoring manage'),
                                'items' => [
                                    [
                                        'label' => Yii::t('app', 'Monitor'),
                                        'url' => ['/backend/error-monitor/index'],
                                        'icon' =>'flash',
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Config'),
                                        'url' => ['/backend/error-monitor/config'],
                                        'icon' => 'gear',
                                    ]
                                ]
                            ],
                            [
                                'label' => Yii::t('app', 'Data'),
                                'url' => '#',
                                'icon' => 'database',
                                'active' => Yii::$app->user->can('data manage'),
                                'items' => [
                                    [
                                        'label' => Yii::t('app', 'Import'),
                                        'url' => ['/backend/data/import'],
                                        'icon' =>'sign-in'
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Export'),
                                        'url' => ['/backend/data/export'],
                                        'icon' =>'sign-out'
                                    ],
                                ]
                            ],
                            [
                                'label' => Yii::t('app', 'Email notify'),
                                'url' => '#',
                                'icon' => 'envelope-o',
                                'active' => Yii::$app->user->can('newsletter'),
                                'items' => [
                                    [
                                        'label' => Yii::t('app', 'Settings'),
                                        'url' => ['/backend/newsletter/config'],
                                        'icon' => 'gears',
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Email list'),
                                        'url' => ['/backend/newsletter/email-list'],
                                        'icon' => 'list-alt',
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Send now'),
                                        'url' => ['/backend/newsletter/newslist'],
                                        'icon' => 'at',
                                    ]
                                ]
                            ],
                            [
                                'label' => \Yii::t('app', 'Settings'),
                                'url' => '#',
                                'icon' => 'gears',
                                'active' => Yii::$app->user->can('setting manage'),
                                'items' => [
                                    [
                                        'label' => \Yii::t('app', 'Config'),
                                        'url' => ['/backend/config/index'],
                                        'icon' => 'gear',
                                    ],
                                    [
                                        'label' => \Yii::t('app', 'I18n'),
                                        'url' => ['/backend/i18n/index'],
                                        'icon' => 'language',
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Spam Form Checker'),
                                        'url' => ['/backend/spam-checker/index'],
                                        'icon' => 'send-o',
                                    ],
                                ],
                            ],
                        ],
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
