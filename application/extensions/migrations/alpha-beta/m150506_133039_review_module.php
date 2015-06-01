<?php

use app\modules\config\models\Configurable;
use yii\db\Migration;

class m150506_133039_review_module extends Migration
{
    public function up()
    {
        $this->insert(
            Configurable::tableName(),
            [
                'module' => 'review',
                'sort_order' => 14,
                'section_name' => 'Reviews',
            ]
        );
    }

    public function down()
    {
        $this->delete(Configurable::tableName(), ['module' => 'review']);
    }
}
