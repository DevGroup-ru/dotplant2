<?php

namespace app\traits;

use yii;

/**
 * Trait for sorting models, that use app\behaviors\Sortable
 */
trait SortModels
{
    /**
     * Sort records by their id list.
     * Note: you must manually call refresh() on needed models
     * @param array $ids array of records id in needed order(ie. [4, 3, 1, 2])
     * @param string $field Field that stores sort order
     * @return bool
     * @throws yii\db\Exception
     */
    public static function sortModels($ids, $field = 'sort_order')
    {
        $priorities = [];
        $start=0;
        $ids_sorted = $ids;
        sort($ids_sorted);
        foreach ($ids as $id) {
            $priorities[$id] = $ids_sorted[$start++];
        }
        $sql = "UPDATE "
            . static::tableName()
            . " SET $field = "
            . static::generateCase($priorities)
            . " WHERE id IN(" . implode(', ', $ids)
            . ")";
        return Yii::$app->db->createCommand(
            $sql
        )->execute() > 0;
    }

    public static function generateCase($priorities)
    {
        $result = 'CASE `id`';
        foreach ($priorities as $k => $v) {
            $result .= ' when "' . $k . '" then "' . $v . '"';
        }
        return $result . ' END';
    }

    public static function moveIdBefore($id, $id_before, $field = 'sort_order')
    {
        //! @todo Переписать, чтобы не использовать ActiveRecord, а обходиться обычными запросами в базу(за один запрос можно получить sort_order для двух ID)
        $current_model = static::findOne($id);
        $another_model = static::findOne($id_before);
        return $current_model->moveBefore($another_model, $field);
    }

    public static function moveIdAfter($id, $id_after, $field = 'sort_order')
    {
        //! @todo Переписать, чтобы не использовать ActiveRecord, а обходиться обычными запросами в базу(за один запрос можно получить sort_order для двух ID)
        $current_model = static::findOne($id);
        $another_model = static::findOne($id_after);
        return $current_model->moveAfter($another_model, $field);
    }
}
