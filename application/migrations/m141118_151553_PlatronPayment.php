<?php

use yii\db\Schema;
use yii\db\Migration;

class m141118_151553_PlatronPayment extends Migration
{
    public function up()
    {

        $this->insert(
            \app\models\PaymentType::tableName(),
            [
                'name' => 'Platron',
                'class' => 'app\components\payment\PlatronPayment',
                'params' => \yii\helpers\Json::encode(
                    [
                        'merchant_id' => '',
                        'secret_key' => '',
                        'strCurrency' => 'RUR',
                        'merchantUrl' => 'www.platron.ru/payment.php',
                    ]
                ),
            ]
            );

    }

    public function down()
    {
        echo "m141118_151553_PlatronPayment cannot be reverted.\n";

        $this->delete(
            \app\models\PaymentType::tableName(),
            ['name'=> 'Platron']
        );

        return false;
    }
}
