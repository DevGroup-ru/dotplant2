<?php

use yii\db\Migration;

class m170408_060716_disctype_add_customview extends Migration
{
    public function up()
    {
	$this->addColumn( \app\modules\shop\models\DiscountType::tableName(),'custom_view','VARCHAR(255) DEFAULT NULL AFTER `sort_order`');
	return true;
    }

    public function down()
    {
	$this->dropColumn( \app\modules\shop\models\DiscountType::tableName(),'custom_view');
        return true;
    }
}
