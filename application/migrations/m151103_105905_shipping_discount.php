<?php

use app\modules\shop\models\DiscountType;
use yii\db\Migration;

class m151103_105905_shipping_discount extends Migration
{
    public function up()
    {
        $this->createTable(
            'shipping_discount',
            [
                'id' => $this->primaryKey(),
                'shipping_option_id' => $this->integer()->notNull(),
                'discount_id' => $this->integer()->notNull()
            ]
        );

        $discountType = new DiscountType();
        $discountType->name = 'Shipping Discount';
        $discountType->class = 'app\modules\shop\models\ShippingDiscount';
        $discountType->active = 1;
        $discountType->checking_class = 'Order';
        $discountType->sort_order = 0;
        $discountType->add_view = '@app/modules/shop/views/backend-discount/_shipping_discount';

        $discountType->save();
    }

    public function down()
    {
        DiscountType::deleteAll(['name' => 'Shipping Discount']);
        $this->dropTable('shipping_discount');
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
