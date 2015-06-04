<?php

use app\models\Submission;
use yii\db\Migration;

class m150603_104416_submission_spam extends Migration
{
    public function up()
    {
        $this->update(
            Submission::tableName(),
            ['spam' => 1],
            ['spam' => [Yii::$app->formatter->asBoolean(1), 'Yes', 'Да']]
        );
        $this->update(
            Submission::tableName(),
            ['spam' => 0],
            'spam <> 1'
        );
        $this->alterColumn(Submission::tableName(), 'spam', 'TINYINT(1) UNSIGNED DEFAULT 0');
    }

    public function down()
    {
        $this->alterColumn(Submission::tableName(), 'spam', 'VARCHAR(25) DEFAULT NULL');
        $this->update(
            Submission::tableName(),
            ['spam' => Yii::$app->formatter->asBoolean(1)],
            ['spam' => 1]
        );
        $this->update(
            Submission::tableName(),
            ['spam' => Yii::$app->formatter->asBoolean(0)],
            ['spam' => 0]
        );
    }
}
