<?php

namespace app\controllers;

use app\models\Config;
use Yii;
use app\models\Product;
use yii\web\Controller;

class ProductCompareController extends Controller
{

    /**
     * @param $id
     * @param null $backUrl
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

        $maxProductsToCompare = Config::getValue('shop.maxProductsToCompare', 3);
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
     * @param null $backUrl
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
        $prods = $this->getProductsFromSession();
        if (count($prods) == 0) {
            return $this->render(
                'compare',
                [
                    'error' => 1,
                    'message' => Yii::t('shop', 'No products for comparing')
                ]
            );
        }

        return $this->render(
            'compare',
            [
                'error' => 0,
                'message' => '',
                'prods' => $prods
            ]
        );
    }

    public function actionPrint()
    {
        $this->layout = 'print';

        $prods = $this->getProductsFromSession();
        if (count($prods) == 0) {
            return $this->render(
                'print',
                [
                    'error' => 1,
                    'message' => Yii::t('shop', 'No products for comparing')
                ]
            );
        }

        return $this->render(
            'print',
            [
                'error' => 0,
                'message' => '',
                'prods' => $prods
            ]
        );
    }

    public function actionRemoveAll($backUrl = null)
    {
        Yii::$app->session->remove('comparisonProductList');

        return null !== $backUrl ? $this->redirect($backUrl, 302) : true;
    }

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
     * @param $id
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
