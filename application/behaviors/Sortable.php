<?php

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class Sortable extends Behavior
{
    public $attribute = 'sort_order';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }

    public function afterInsert()
    {
        $model = $this->owner;
        $model->setAttribute($this->attribute, $model->id);
        $model->save(false, [$this->attribute]);
        return true;
    }

    /**
     * Move current model before another specified model (ie. `$model->moveBefore($another_model);`)
     * Note: you must manually call refresh() on needed models
     * @param \yii\base\Model $another_model Model taht would be after current
     */
    public function moveBefore($another_model)
    {
        $field = $this->attribute;
        // shift sort_order field for another_model and below
        Yii::$app->db->createCommand(
            "UPDATE " .
            $this->owner->tableName() .
            " SET $field = $field + 1 WHERE $field >=
            :another_model_sort_order AND $field <= :current_model_sort_order_lower",
            [
                ':another_model_sort_order' => $another_model->getAttribute($field),
                ':current_model_sort_order_lower' => $this->owner->getAttribute($field),
            ]
        )->execute();
        // change sort_order of our record
        $this->owner->setAttribute($field, $another_model->getAttribute($field));
        $this->owner->save(false, [$field]);
    }

    /**
     * Move current model after another specified model (ie. `$model->moveAfter($another_model);`)
     * Note: you must manually call refresh() on needed models
     * @param \yii\base\Model $another_model Model taht would be before current model
     */
    public function moveAfter($another_model)
    {
        $field = $this->attribute;
        //! @todo Переписать эти два запроса на один, использующий CASE WHEN THEN
        // shift sort_order field for current model and below
        Yii::$app->db->createCommand(
            "UPDATE " . $this->owner->tableName() . " SET $field = $field + 1 WHERE $field >= :model_sort_order",
            [
                ':model_sort_order' => intval($another_model->getAttribute($field)) + 1,
            ]
        )->execute();
        // shift sort_order field for records upper current
        Yii::$app->db->createCommand(
            "UPDATE " .
            $this->owner->tableName() .
            " SET $field = $field - 1 WHERE $field <= :model_sort_order_lower AND $field >= :model_sort_order_upper",
            [
                ':model_sort_order_lower' => intval($another_model->getAttribute($field)) + 1,
                ':model_sort_order_upper' => intval($this->owner->getAttribute($field)),
            ]
        )->execute();
        // change sort_order of our record
        $this->owner->setAttribute($field, $another_model->getAttribute($field));
        $this->owner->save(false, [$field]);
    }

    /**
     * Move current model upper
     * Note: you must manually call refresh() on needed models
     */
    public function moveUp()
    {
        /** @var $ownerClassName ActiveRecord */
        $ownerClassName = $this->owner->className();
        $previous = $ownerClassName::find()
            ->select(['id', $this->attribute])
            ->where(['<', $this->attribute, $this->owner->{$this->attribute}])
            ->orderBy($this->attribute)
            ->one();
        if ($previous === false) {
            return false;
        }
        return $this->owner->sortModels([$this->owner->id, $previous['id']], $this->attribute);
    }

    /**
     * Move current model down
     * Note: you must manually call refresh() on needed models
     */
    public function moveDown()
    {
        /** @var $ownerClassName ActiveRecord */
        $ownerClassName = $this->owner->className();
        $next = $ownerClassName::find()
            ->select(['id', $this->attribute])
            ->where(['>', $this->attribute, $this->owner->{$this->attribute}])
            ->orderBy($this->attribute)
            ->one();
        if ($next === false) {
            return false;
        }
        return $this->owner->sortModels([$next['id'], $this->owner->id], $this->attribute);
    }
}
