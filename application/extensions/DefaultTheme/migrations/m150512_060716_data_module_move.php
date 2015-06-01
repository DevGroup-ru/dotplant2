<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\modules\config\models\Configurable;

class m150512_060716_data_module_move extends Migration
{
    public function up()
    {
        $this->insert(
            Configurable::tableName(),
            [
                'module' => 'data',
                'sort_order' => 16,
                'section_name' => 'Data import/export',
                'display_in_config' => 1,
            ]);
    }

    public function down()
    {
        $this->delete(Configurable::tableName(), ['module' => 'data']);
    }

}
