<?php
namespace app\modules\shop\assets;

use yii\web\AssetBundle;

class PacketPriceEditAsset extends AssetBundle
{
	public $sourcePath = '@app/modules/shop/assets';
	public $js = ['js/packet_price_edit.js'];
	public $css = ['css/price-edit-modal.css'];
	public $depends = [];
}
