<?php

namespace app\modules\shop\helpers;

use yii;
use app\modules\shop\models\Product;

class ProductCompareHelper
{
    const SESSION_COMPARE_LIST = 'comparisonProductList';

    /**
     * @param bool $fetchObjects
     * @return array|Product[]
     */
    public static function getProductsList($fetchObjects = false)
    {
        $list = Yii::$app->session->get(static::SESSION_COMPARE_LIST, []);
        if (true === $fetchObjects) {
            return array_reduce($list,
                function ($result, $item)
                {
                    if (null !== $model = Product::findById($item)) {
                        $result[$item] = $model;
                    }
                    return $result;
                }, []);
        }
        return $list;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function addProductToList($id)
    {
        $id = intval($id);
        if (null === Product::findById($id)) {
            return false;
        }

        $comparisonProductList = static::getProductsList();
        if (static::productInList($id, $comparisonProductList)) {
            return false;
        }

        /** @var \app\modules\shop\ShopModule $module */
        $module = Yii::$app->modules['shop'];

        if (count($comparisonProductList) > $module->maxProductsToCompare) {
            array_shift($comparisonProductList);
        }
        $comparisonProductList[$id] = $id;

        Yii::$app->session->set(static::SESSION_COMPARE_LIST, $comparisonProductList);
        yii\caching\TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        return true;
    }

    /**
     * @param int $id
     */
    public static function removeProductFromList($id)
    {
        $id = intval($id);
        yii\caching\TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        $comparisonProductList = static::getProductsList();
        if (isset($comparisonProductList[$id])) {
            unset($comparisonProductList[$id]);
        }
        Yii::$app->session->set(static::SESSION_COMPARE_LIST, $comparisonProductList);
    }

    /**
     *
     */
    public static function clearProductList()
    {
        yii\caching\TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        Yii::$app->session->remove(static::SESSION_COMPARE_LIST);
    }

    /**
     * @param int $id
     * @param array|null $list
     * @return bool
     */
    public static function productInList($id, $list = null)
    {
        $id = intval($id);
        $list = null === $list ? static::getProductsList() : $list;
        return isset($list[$id]);
    }

    /**
     * @return int
     */
    public static function listLength()
    {
        $list = static::getProductsList();
        return count($list);
    }
}