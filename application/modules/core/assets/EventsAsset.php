<?php

namespace app\modules\core\assets;

use yii\web\AssetBundle;

/**
 * DotPlant2 asset bundle for js-triggered events.
 *
 *
 * @package app\modules\core\assets
 */
class EventsAsset extends AssetBundle
{

    public $sourcePath = '@app/modules/core/assets/events';
    public $css = [

    ];
    public $js = [
        'js/DotPlant2Events.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'app\modules\core\assets\JqueryInviewAsset',
    ];
}
