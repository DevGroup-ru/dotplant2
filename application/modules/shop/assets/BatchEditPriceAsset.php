<?php
namespace app\modules\shop\assets;

use yii\web\AssetBundle;

class BatchEditPriceAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/shop/assets';
    public $js = ['js/batch-edit-price.js'];
    public $css = ['css/batch-edit-price.css'];
    public $depends = ['yii\web\JqueryAsset'];
}
