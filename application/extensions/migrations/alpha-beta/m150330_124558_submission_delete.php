<?php

use app\models\Submission;
use yii\db\Migration;

class m150330_124558_submission_delete extends Migration
{
    public function up()
    {
        $this->addColumn(Submission::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
    }

    public function down()
    {
        $this->dropColumn(Submission::tableName(), 'is_deleted');
    }
}
