<?php

use app\modules\shop\models\RelatedProduct;
use yii\db\Migration;

class m150129_125119_related_products extends Migration
{
    public function up()
    {
        $this->createTable(
            RelatedProduct::tableName(),
            [
                'product_id' => 'INT UNSIGNED NOT NULL',
                'related_product_id' => 'INT UNSIGNED NOT NULL',
                'PRIMARY KEY (`product_id`, `related_product_id`)',
            ],
            'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB'
        );
    }

    public function down()
    {
        $this->dropTable(RelatedProduct::tableName());
    }
}
