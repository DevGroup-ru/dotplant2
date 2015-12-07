<?php

namespace app\modules\shop\components\GoogleMerchants;


use app\modules\shop\models\Product;
use yii\base\Event;

/**
 * Class ModificationDataEvent
 * @package app\modules\shop\components\GoogleMerchants
 *
 * @property Product $model
 * @property Array $data
 */
class ModificationDataEvent extends Event
{
    public $model;
    public $dataArray = [];
}