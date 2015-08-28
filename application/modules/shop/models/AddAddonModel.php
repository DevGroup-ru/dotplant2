<?php

namespace app\modules\shop\models;

use Yii;
use yii\base\Model;

class AddAddonModel extends Model
{
    public $addon_id;

    public function rules()
    {
        return [
            ['addon_id', 'number', 'integerOnly'=>true],
            ['addon_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => Addon::className()],
        ];
    }
}