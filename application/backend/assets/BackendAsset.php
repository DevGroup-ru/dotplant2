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
    public $sourcePath = '@app/backend/assets/backend';
    public $css = [
        '//st-1.dotplant.ru/css/smartadmin-production.min.css',
        '//st-2.dotplant.ru/css/smartadmin-production-plugins.min.css',
        'css/admin.css',
    ];
    public $js = [
        'js/admin.js',
        'js/DialogActions.js',
        'js/select2sortable.js',
        '//st-4.dotplant.ru/js/plugin/SmartNotification_jarvis.uglify.min.js',
        '//st-2.dotplant.ru/js/plugin/msie-fix/jquery.mb.browser.min.js',
        '//st-3.dotplant.ru/js/app.min.js',
        '//st-4.dotplant.ru/js/plugin/fullcalendar/jquery.fullcalendar.min.js',
        '//cdn.jsdelivr.net/lodash/3.6.0/lodash.min.js',
        '//cdn.jsdelivr.net/bootbox/4.3.0/bootbox.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'devgroup\JsTreeWidget\JsTreeAssetBundle',
        'yii\validators\ValidationAsset',
        'yii\widgets\ActiveFormAsset',
        'yii\jui\JuiAsset',
        '\kartik\icons\FontAwesomeAsset',
        'app\backend\assets\LaddaAsset',
    ];
}
