<?php
/**
 * Created by PhpStorm.
 * User: bethrezen
 * Date: 12.05.15
 * Time: 14:56
 */

namespace app\backend\assets;


use yii\web\AssetBundle;

class LaddaAsset extends AssetBundle
{
    public $sourcePath = '@bower/ladda/dist';
    public $css = [
        'ladda-themeless.min.css',
    ];
    public $js = [
        'spin.min.js',
        'ladda.min.js',
        'ladda.jquery.min.js',

    ];

}