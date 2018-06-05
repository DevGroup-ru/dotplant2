<?php

namespace app\modules\shop\traits;


use app\models\BaseObject;
use app\modules\shop\models\Addon;

trait HasAddonTrait
{

    public function getBindedAddons()
    {
        $object_id = BaseObject::getForClass($this->className())->id;
        return $this->hasMany(Addon::className(), ['id' => 'addon_id'])
            ->viaTable('{{%addon_bindings}}', ['object_model_id' => 'id'], function($query) use ($object_id) {
                /** @var \yii\db\Query $query */
                $query->andWhere(['appliance_object_id'=>$object_id]);
                $query->orderBy(['sort_order' => SORT_ASC]);
                return $query;
            })
            ->innerJoin('{{%addon_bindings}}', 'addon_id=addon.id AND appliance_object_id=:aoid AND object_model_id=:oid',[
                ':aoid' => $object_id,
                ':oid' => $this->id,
            ])
            ->orderBy(['addon_bindings.sort_order' => SORT_ASC]);
    }
}