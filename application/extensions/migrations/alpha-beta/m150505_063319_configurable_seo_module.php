<?php

use app\modules\config\models\Configurable;
use yii\db\Migration;

class m150505_063319_configurable_seo_module extends Migration
{
    public function up()
    {
        $this->insert(
            Configurable::tableName(),
            [
                'module' => 'seo',
                'sort_order' => 12,
                'section_name' => 'SEO',
                'display_in_config' => 1,
            ]
        );
    }

    public function down()
    {
        $this->delete(Configurable::tableName(), ['module' => 'seo']);
    }
}
