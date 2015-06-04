<?php

namespace app\modules\shop\helpers;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class LastViewedProducts is helper class for storing in session list of viewed products
 * @package app\components
 */
class LastViewedProducts
{

    public static function handleProductShowed($event)
    {
        if (isset($event->product_id)) {
            self::saveToSession($event->product_id);
        }
    }
    /**
     * Stores product id into session variable. Takes care of maxLastViewedProducts configurable value
     * @param string|integer $product_id Id of product to store
     */
    public static function saveToSession($product_id)
    {
        $product_id = intval($product_id);
        /** @var \app\modules\shop\ShopModule $module */

        $module = Yii::$app->modules['shop'];

        $maxLastViewedProducts = $module->maxLastViewedProducts;
        if (null !== $maxLastViewedProducts && intval($maxLastViewedProducts) > 0) {
            $newLastViewedProd[] = [
                'product_id' => $product_id,
                'timestamp' => time(),
            ];
            $lastProdsList = $newLastViewedProd;
            if (Yii::$app->session->has('lastViewedProdsList')) {
                $storedProdList = Yii::$app->session->get('lastViewedProdsList');
                if (is_array($storedProdList)) {
                    if (static::productIdExists($storedProdList, $product_id)) {
                        $lastProdsList = $storedProdList;
                    } else {
                        $lastProdsList = ArrayHelper::merge($storedProdList, $newLastViewedProd);
                    }
                }
            }
            if (count($lastProdsList) > $maxLastViewedProducts) {
                $lastProdsList = array_slice(
                    $lastProdsList,
                    count($lastProdsList) - $maxLastViewedProducts,
                    $maxLastViewedProducts
                );
            }
            Yii::$app->session->set('lastViewedProdsList', $lastProdsList);
        }
    }

    /**
     * Checks if specified product id already in stored array
     * @param array $array
     * @param integer $id
     * @return bool
     */
    private static function productIdExists($array, $id)
    {
        $id = intval($id);

        foreach ($array as $value) {
            if ($value['product_id'] === $id) {
                return true;
            }
        }
        return false;
    }
}
