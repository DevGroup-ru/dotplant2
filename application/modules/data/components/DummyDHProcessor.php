<?php
namespace app\modules\data\components;

use Yii;
use yii\base\Component;

class DummyDHProcessor extends Component
{
    public static function processHeader($dh = [])
    {
	return $dh;
    }
}