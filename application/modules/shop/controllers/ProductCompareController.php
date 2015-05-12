<?php

namespace app\modules\shop\controllers;

use app\models\Object;
use app\modules\shop\models\Product;
use Yii;
use yii\web\Controller;

class ProductCompareController extends Controller
{
    /**
     * @param $id
     * @param null|string $backUrl
     * @return bool|\yii\web\Response
     */
    public function actionAdd($id, $backUrl = null)
    {
        $comparisonProductList = null;
        if ($this->isExist($id)) {
            return null !== $backUrl ? $this->redirect($backUrl, 302) : false;
        }
        $comparisonProductList = Yii::$app->session->get('comparisonProductList');
        if (null == $comparisonProductList || !is_array($comparisonProductList)) {
            $comparisonProductList = [];
        }
        /** @var \app\modules\shop\ShopModule $module */
        $module = Yii::$app->modules['shop'];

        $maxProductsToCompare = $module->maxProductsToCompare;
        $comparisonProductList[] = $id;
        if (count($comparisonProductList) > $maxProductsToCompare) {
            $comparisonProductList = array_slice(
                $comparisonProductList,
                count($comparisonProductList) - $maxProductsToCompare,
                $maxProductsToCompare
            );
        }
        Yii::$app->session->set('comparisonProductList', $comparisonProductList);
        return null !== $backUrl ? $this->redirect($backUrl, 302) : true;
    }

    /**
     * @param $id
     * @param null|string $backUrl
     * @return bool|\yii\web\Response
     */
    public function actionRemove($id, $backUrl = null)
    {
        if (!$this->isExist($id)) {
            return null !== $backUrl ? $this->redirect($backUrl, 302) : false;
        }
        $comparisonProductList = Yii::$app->session->get('comparisonProductList');
        if (null == $comparisonProductList || !is_array($comparisonProductList)) {
            return false;
        }
        $removeArrayKey = array_search($id, $comparisonProductList);
        unset($comparisonProductList[$removeArrayKey]);
        Yii::$app->session->set('comparisonProductList', $comparisonProductList);
        return null !== $backUrl ? $this->redirect($backUrl, 302) : true;
    }

    /**
     * @return string
     */
    public function actionCompare()
    {
        $products = $this->getProductsFromSession();
        $object = Object::getForClass(Product::className());
        return $this->render(
            'compare',
            [
                'error' => count($products) == 0 || is_null($object),
                'message' => Yii::t('app', 'No products for comparing'),
                'object' => $object,
                'products' => $products,
            ]
        );
    }

    /**
     * @return string
     */
    public function actionPrint()
    {
        $this->layout = 'print';
        $products = $this->getProductsFromSession();
        $object = Object::getForClass(Product::className());
        return $this->render(
            'print',
            [
                'error' => count($products) == 0 || is_null($object),
                'message' => Yii::t('app', 'No products for comparing'),
                'object' => $object,
                'products' => $products,
            ]
        );
    }

    /**
     * @param null|string $backUrl
     * @return bool|\yii\web\Response
     */
    public function actionRemoveAll($backUrl = null)
    {
        Yii::$app->session->remove('comparisonProductList');
        return null !== $backUrl ? $this->redirect($backUrl, 302) : true;
    }

    /**
     * @return array
     */
    private function getProductsFromSession()
    {
        $prodElements = Yii::$app->session->get('comparisonProductList');
        if (null == $prodElements || !is_array($prodElements)) {
            return [];
        }
        $prods = [];
        foreach ($prodElements as $prodId) {
            $prod = Product::findById($prodId);
            if (null !== $prod) {
                $prods[] = $prod;
            }
        }
        return $prods;
    }

    /**
     * @param integer $id
     * @return bool
     */
    private function isExist($id)
    {
        $prodElement = Yii::$app->session->get('comparisonProductList');
        if (null == $prodElement || !is_array($prodElement)) {
            return false;
        }
        return in_array($id, $prodElement);
    }
}
