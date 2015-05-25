<?php

use yii\db\Schema;
use yii\db\Migration;
use app\modules\review\models\Review;

class m150514_114054_alterReview extends Migration
{
    public function up()
    {
          $this->createTable(
            Review::tableName(),
            [
                'id' => Schema::TYPE_PK,
                'submission_id' => Schema::TYPE_INTEGER. ' NOT NULL',
                'object_id' => Schema::TYPE_INTEGER. ' NOT NULL',
                'object_model_id' => Schema::TYPE_INTEGER. ' NOT NULL',
                'author_email' => Schema::TYPE_STRING. ' NOT NULL',
                'review_text' => Schema::TYPE_TEXT. ' NOT NULL',
                'rating_id' => Schema::TYPE_INTEGER. ' NOT NULL',
                'status' => 'enum(\'NEW\',\'APPROVED\',\'NOT APPROVED\') DEFAULT \'NEW\'',
                'KEY `ix-review-object_id-object_model_id-status` (`submission_id`, `object_model_id`, `status`)',
            ],
            'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
        );

    }

    public function down()
    {
        $this->dropTable(Review::tableName());
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
