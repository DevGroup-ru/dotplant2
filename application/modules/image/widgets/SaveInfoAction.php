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
            foreach ($sortOrder as $tid) {
                $priorities[intval($tid)] = $start++;
            }
            $case = 'CASE `id`';
            foreach ($priorities as $k => $v) {
                $case .= ' when "' . intval($k) . '" then "' . intval($v) . '"';
            }
            $case .= ' END';
            $sql = "UPDATE "
                . Image::tableName()
                . " SET sort_order = "
                . $case
                . " WHERE id IN(" . implode(', ', $sortOrder)
                . ")";
            \Yii::$app->db->createCommand($sql)->execute();
        }
    }
}
