<?php

namespace app\backend\assets;

use yii\web\AssetBundle;

class BackendAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'http://st-1.dotplant.ru/css/smartadmin-production.min.css',
        'http://st-2.dotplant.ru/css/smartadmin-production-plugins.min.css',
        'css/jstree-themes/default/style.min.css',
    ];
    public $js = [
        'js/admin.js',
        'http://st-3.dotplant.ru/js/lib/jstree.min.js',
        'http://st-4.dotplant.ru/js/plugin/SmartNotification_jarvis.uglify.js',
        'http://st-2.dotplant.ru/js/plugin/msie-fix/jquery.mb.browser.min.js',
        'http://st-3.dotplant.ru/js/app.min.js',
        'http://st-4.dotplant.ru/js/plugin/fullcalendar/jquery.fullcalendar.min.js',
        'http://st-1.dotplant.ru/js/lib/underscore-min.js',
        'http://st-2.dotplant.ru/js/lib/bootbox.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
