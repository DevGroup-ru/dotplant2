<?php

namespace app\modules\shop\traits;


use app\models\Object;
use app\modules\shop\models\Addon;

trait HasAddonTrait
{

    public function getBindedAddons()
    {
        $object_id = Object::getForClass($this->className())->id;
        return $this->hasMany(Addon::className(), ['id' => 'addon_id'])
            ->viaTable('{{%addon_bindings}}', ['object_model_id' => 'id'], function($query) use ($object_id) {
                /** @var \yii\db\Query $query */
                $query->andWhere(['appliance_object_id'=>$object_id]);
                return $query;
            });
    }
}