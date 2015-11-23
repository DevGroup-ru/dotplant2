<?php

use app\modules\shop\models\CurrencyRateProvider;
use app\components\swap\provider\CurrencyRateMultiProvider;
use yii\db\Migration;
use yii\helpers\Json;

class m151123_122215_multi_provider extends Migration
{
    public function up()
    {
        $this->insert(
            CurrencyRateProvider::tableName(),
            [
                'name' => 'Currency rate multi provider',
                'class_name' => CurrencyRateMultiProvider::class,
                'params' => Json::encode(
                    [
                        'mainProvider' => 1,
                        'secondProvider' => 2,
                        'criticalDifference' => 20,
                    ]
                )
            ]
        );
    }

    public function down()
    {
        $this->delete(CurrencyRateProvider::tableName(), ['class_name' => CurrencyRateMultiProvider::class]);
    }
}
