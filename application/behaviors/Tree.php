<?php

namespace app\behaviors;

use yii\base\Behavior;

/**
 * Class Tree
 * @package app\behaviors
 * @property \yii\db\ActiveRecord $owner
 */
class Tree extends Behavior
{
    public $idAttribute = 'id';
    public $parentIdAttribute = 'parent_id';

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->owner->hasOne($this->owner->className(), [$this->idAttribute => $this->parentIdAttribute]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->owner->hasMany($this->owner->className(), [$this->parentIdAttribute => $this->idAttribute]);
    }
}
