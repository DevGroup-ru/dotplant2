<?php

use yii\db\Migration;

class m141118_151553_PlatronPayment extends Migration
{
    public function up()
    {

        $this->insert(
            \app\modules\shop\models\PaymentType::tableName(),
            [
                'name' => 'Platron',
                'class' => 'app\components\payment\PlatronPayment',
                'params' => \yii\helpers\Json::encode(
                    [
                        'merchantId' => '',
                        'secretKey' => '',
                        'strCurrency' => 'RUR',
                        'merchantUrl' => 'www.platron.ru',
                        'merchantScriptName' => 'payment.php'
                    ]
                ),
            ]
        );

    }

    public function down()
    {
        $this->delete(
            \app\modules\shop\models\PaymentType::tableName(),
            ['name' => 'Platron']
        );

        return true;
    }
}
