<?php

namespace app\components;

use Yii;
use app\models\Config;
use yii\helpers\ArrayHelper;

class LastViewedProducts
{
    public function saveToSession($product_id)
    {
        $lastViewedProdsStoreQuantity = Config::getValue("shop.lastViewedProdsStoreQuantity");
        if (null !== $lastViewedProdsStoreQuantity && intval($lastViewedProdsStoreQuantity) > 0) {
            $newLastViewedProd[] = [
                'product_id' => $product_id,
                'timestamp' => time(),
            ];
            $lastProdsList = $newLastViewedProd;
            if (Yii::$app->session->has('lastViewedProdsList')) {
                $storedProdList = Yii::$app->session->get('lastViewedProdsList');
                if (is_array($storedProdList)) {
                    if ($this->isExistProdId($storedProdList, $product_id)) {
                        $lastProdsList = $storedProdList;
                    } else {
                        $lastProdsList = ArrayHelper::merge($storedProdList, $newLastViewedProd);
                    }
                }
            }
            if (count($lastProdsList) > $lastViewedProdsStoreQuantity) {
                $lastProdsList = array_slice(
                    $lastProdsList,
                    count($lastProdsList) - $lastViewedProdsStoreQuantity,
                    $lastViewedProdsStoreQuantity
                );
            }
            Yii::$app->session->set('lastViewedProdsList', $lastProdsList);
        }
    }

    private function isExistProdId($array, $id)
    {
        foreach ($array as $value) {
            if ($value['product_id'] == $id) {
                return true;
            }
        }
        return false;
    }
}
