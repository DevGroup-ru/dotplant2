<?php

use yii\db\Schema;
use yii\db\Migration;

class m150514_101123_discount_type_add_view extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%discount_type}}',
            'add_view',
            Schema::TYPE_STRING
        );

        $this->update(
            '{{%discount_type}}',
            [
                'add_view' => '@app/modules/shop/views/backend-discount/_discount_code'
            ],
            [
                'class' => 'app\modules\shop\models\DiscountCode'
            ]
        );

        $this->update(
            '{{%discount_type}}',
            [
                'add_view' => '@app/modules/shop/views/backend-discount/_category_discount'
            ],
            [
                'class' => 'app\modules\shop\models\CategoryDiscount'
            ]
        );

        $this->update(
            '{{%discount_type}}',
            [
                'add_view' => '@app/modules/shop/views/backend-discount/_user_discount'
            ],
            [
                'class' => 'app\modules\shop\models\UserDiscount'
            ]
        );

        $this->update(
            '{{%discount_type}}',
            [
                'add_view' => '@app/modules/shop/views/backend-discount/_order_discount'
            ],
            [
                'class' => 'app\modules\shop\models\OrderDiscount'
            ]
        );

        $this->update(
            '{{%discount_type}}',
            [
                'add_view' => '@app/modules/shop/views/backend-discount/_product_discount'
            ],
            [
                'class' => 'app\modules\shop\models\ProductDiscount'
            ]
        );

    }

    public function down()
    {
        $this->dropColumn(
            '{{%discount_type}}',
            'add_view'
        );
    }

}
