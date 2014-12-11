<?php

use yii\db\Schema;
use yii\db\Migration;

class m141211_113001_product_storage extends Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE {{%product}}
              ADD `sku` VARCHAR(70) NOT NULL DEFAULT '' ,
              ADD `in_warehouse` INT NOT NULL DEFAULT '0' ,
              ADD `unlimited_count` TINYINT NOT NULL DEFAULT '1' ,
              ADD `reserved_count` INT NOT NULL DEFAULT '0' ,
              ADD INDEX (`sku`) ;
        ");
        Yii::$app->cache->flush();
    }

    public function down()
    {
        $this->execute("
        ALTER TABLE {{%product}}
          DROP `sku`,
          DROP `in_warehouse`,
          DROP `unlimited_count`,
          DROP `reserved_count`;
        ");
    }
}
