<?php

namespace app\behaviors;

use app\modules\image\models\Image;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\models\ViewObject;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * Class CleanRelations behavior.
 * @package app\behaviors
 */
class CleanRelations extends Behavior
{
    /**
     * Get events list.
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'cleanUpRelations',
        ];
    }

    /**
     * Delete relations
     * @return bool
     */
    public function cleanUpRelations()
    {
        if (!is_subclass_of($this->owner, '\yii\db\ActiveRecord')) {
            return false;
        }
        if (isset($this->owner->is_deleted) && (0 === intval($this->owner->is_deleted))) {
            return false;
        }
        if (null === $object = Object::getForClass($this->owner->className())) {
            return false;
        }
        $whereDelete = [
            'object_id' => $object->id,
            'object_model_id' => $this->owner->id
        ];
        ObjectPropertyGroup::deleteAll($whereDelete);
        ObjectStaticValues::deleteAll($whereDelete);
        Image::deleteAll($whereDelete);
        ViewObject::deleteAll($whereDelete);
        try {
            Yii::$app->db->createCommand()->delete(
                $object->categories_table_name,
                [
                    'object_model_id' => $this->owner->id,
                ]
            )->execute();
            Yii::$app->db->createCommand()->delete(
                $object->column_properties_table_name,
                [
                    'object_model_id' => $this->owner->id,
                ]
            )->execute();
            Yii::$app->db->createCommand()->delete(
                $object->eav_table_name,
                [
                    'object_model_id' => $this->owner->id,
                ]
            )->execute();
        } catch (Exception $e) {
            // do nothing
        }
        return true;
    }
}
