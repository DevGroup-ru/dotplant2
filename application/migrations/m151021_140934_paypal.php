<?php

use yii\db\Migration;

class m151021_140934_paypal extends Migration
{
    public function up()
    {
        $this->insert(
            \app\modules\shop\models\PaymentType::tableName(),
            [
                'name' => 'PayPal',
                'class' => 'app\components\payment\PayPalPayment',
                'params' => '{"clientId":"","clientSecret":"","currency":"","transactionDescription":"","sandbox":false}',
                'logo' => '',
                'commission' => 0,
                'active' => 0,
                'payment_available' => 0,
                'sort' => 0,
            ]
        );
    }

    public function down()
    {
        echo "m151021_140934_paypal cannot be reverted.\n";
        return false;
    }
}
