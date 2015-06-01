<?php

use yii\db\Schema;
use yii\db\Migration;

class m150402_061959_config_filterOnlyByParentProduct extends Migration
{
    public function up()
    {
        $parent = \app\models\Config::find()->where(['key' => 'shop', 'parent_id' => 0])->asArray()->one();

        if (empty($parent)) {
            return false;
        }

        $this->insert(
            \app\models\Config::tableName(),
            [
                'parent_id' => $parent['id'],
                'name' => 'Filter only by parent products',
                'key' => 'filterOnlyByParentProduct',
                'value' => 1,
                'path' => 'shop.filterOnlyByParentProduct',
                'preload' => 1
            ]
        );
    }

    public function down()
    {
        $parent = \app\models\Config::find()->where(['key' => 'shop', 'parent_id' => 0])->asArray()->one();

        if (empty($parent)) {
            return false;
        }

        $this->delete(
            \app\models\Config::tableName(),
            [
                'parent_id' => $parent['id'],
                'key' => 'filterOnlyByParentProduct',
            ]
        );

        return true;
    }
}
?>