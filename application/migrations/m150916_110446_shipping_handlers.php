<?php

use app\modules\shop\models\ShippingOption;
use yii\db\Migration;
use yii\helpers\Json;

class m150916_110446_shipping_handlers extends Migration
{
    public function up()
    {
        $this->addColumn(
            ShippingOption::tableName(),
            'handler_class',
            'VARCHAR(255) NULL'
        );
        $this->addColumn(
            ShippingOption::tableName(),
            'handler_params',
            'TEXT'
        );
        /** @var ShippingOption[] $shippingOptions */
        $shippingOptions = ShippingOption::find()->all();
        foreach ($shippingOptions as $shippingOption) {
            $shippingOption->handler_params = Json::encode(
                [
                    'price' => $shippingOption->cost,
                ]
            );
            $shippingOption->handler_class = \app\modules\shop\components\FixedShippingCostHandler::className();
            $shippingOption->save(true, ['handler_class', 'handler_params']);
        }
        $this->dropColumn(ShippingOption::tableName(), 'cost');
    }

    public function down()
    {
        $this->addColumn(ShippingOption::tableName(), 'cost', 'FLOAT DEFAULT 0 AFTER `price_to`');
        /** @var ShippingOption[] $shippingOptions */
        $shippingOptions = ShippingOption::findAll(
            [
                'handler_class' => \app\modules\shop\components\FixedShippingCostHandler::className(),
            ]
        );
        foreach ($shippingOptions as $shippingOption) {
            $params = Json::decode($shippingOption->handler_params);
            $shippingOption->cost = $params['price'];
            $shippingOption->save(true, ['cost']);
        }
        $this->dropColumn(ShippingOption::tableName(), 'handler_params');
        $this->dropColumn(ShippingOption::tableName(), 'handler_class');
    }
}
