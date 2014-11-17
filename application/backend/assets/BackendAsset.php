<?php

namespace app\backend\assets;

use yii\web\AssetBundle;

/**
 * Backend asset class defining common needed assets for admin panel.
 *
 *
 * > **Note:** 
 * > Backend uses commercial bootstrap 3 theme - SmartAdmin. 
 * > Therefore theme assets(js, css, images, etc.) are loaded in backend 
 * > from third-party static domains(st-[1-4].dotplant.ru). 
 * > 
 * > Please consider buying SmartAdmin license
 * > at https://wrapbootstrap.com/theme/smartadmin-responsive-webapp-WB0573SK0?ref=dotplant_ru_cms) 
 * > for your project if you want to modify original files or relocate them to another domain.
 * > 
 * > Until you don't change this assets location you don't need to buy separate license.
 * 
 */
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
