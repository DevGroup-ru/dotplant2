<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app;
use app\modules\shop\models\Currency;

class CurrencyController extends Controller
{

    public function actionUpdate()
    {
        $mainCurrency = Currency::getMainCurrency();

        if ($mainCurrency === null) {
            throw new \Exception("Main currency is not set");
        }

        $currencies = Currency::find()
            ->andWhere('currency_rate_provider_id != 0')
            ->andWhere('id != :main_id', [':main_id'=>$mainCurrency->id])
            ->all();


        $httpAdapter = new \Ivory\HttpAdapter\CurlHttpAdapter();

        foreach ($currencies as $currency) {
            /** @var app\modules\shop\models\CurrencyRateProvider $providerModel */
            $providerModel = $currency->rateProvider;
            if ($providerModel !== null) {
                try {
                    $provider = $providerModel->getImplementationInstance($httpAdapter);
                    if ($provider !== null) {
                        $swap = new \Swap\Swap($provider);
                        $rate = $swap->quote($currency->iso_code . '/' . $mainCurrency->iso_code)->getValue();
                        $currency->convert_rate = floatval($rate);
                        if ($currency->additional_rate > 0) {
                            // additional rate is in %
                            $currency->convert_rate *= (1 + $currency->additional_rate / 100);
                        }

                        if ($currency->additional_nominal !== 0) {
                            $currency->convert_rate += $currency->additional_nominal;
                        }
                        $currency->save();
                        echo $currency->iso_code . '/' . $mainCurrency->iso_code . ': ' . $rate . " == " . $currency->convert_rate . "\n";
                    }
                } catch (\Exception $e) {
                    echo "Error updating " . $currency->name . ': ' . $e->getMessage() . "\n\n";
                }
            }
        }
    }
}
