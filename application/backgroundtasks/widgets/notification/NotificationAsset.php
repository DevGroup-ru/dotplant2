<?php

namespace app\backgroundtasks\widgets\notification;

use yii\web\AssetBundle as AssetBundle;

/**
 * Class NotificationAsset
 * @package app\backgroundtasks\widgets\notification
 * @author evgen-d <flynn068@gmail.com>
 */
class NotificationAsset extends AssetBundle
{
    public $sourcePath = '@app/backgroundtasks/widgets/notification';
    public $js = [
        'js/notification.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
