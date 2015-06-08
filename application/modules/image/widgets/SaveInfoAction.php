<?php

namespace app\modules\image\widgets;

use app\modules\image\models\Image;
use yii\base\Action;

class SaveInfoAction extends Action
{
    public function run()
    {
        $descriptions = \Yii::$app->request->post('description', []);
        $sortOrder = (array) \Yii::$app->request->post('id', []);
        foreach ($descriptions as $id => $description) {
            Image::updateAll(
                [
                    'image_description' => $description,
                ],
                'id = :id',
                [
                    ':id' => $id,
                ]
            );
        }

        if (count($sortOrder)>0) {
            $priorities = [];
            $start = 0;
            $ids = [];
            foreach ($sortOrder as $tid) {
                $priorities[intval($tid)] = $start++;
                $ids [] = intval($tid);
            }
            $case = 'CASE `id`';
            foreach ($priorities as $k => $v) {
                $case .= ' when "' . $k . '" then "' . $v . '"';
            }
            $case .= ' END';
            $sql = "UPDATE "
                . Image::tableName()
                . " SET sort_order = "
                . $case
                . " WHERE id IN(" . implode(', ', $ids)
                . ")";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }
}
