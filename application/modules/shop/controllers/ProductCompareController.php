<?php

namespace app\modules\shop\controllers;

use app\models\Object;
use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\shop\helpers\ProductCompareHelper;
use app\modules\shop\models\Product;
use Yii;
use yii\caching\TagDependency;
use yii\web\Controller;

class ProductCompareController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => DisableRobotIndexBehavior::className(),
            ]
        ];
    }

    /**
     * @param $id
     * @param null|string $backUrl
     * @return bool|\yii\web\Response
     */
    public function actionAdd($id, $backUrl = null)
    {
        ProductCompareHelper::addProductToList($id);
        return $this->redirect($backUrl !== null ? $backUrl : Yii::$app->request->referrer);
    }

    /**
     * @param $id
     * @param null|string $backUrl
     * @return bool|\yii\web\Response
     */
    public function actionRemove($id, $backUrl = null)
    {
        ProductCompareHelper::removeProductFromList($id);
        return $this->redirect($backUrl !== null ? $backUrl : Yii::$app->request->referrer);
    }

    /**
     * @return string
     */
    public function actionCompare()
    {
        $products = ProductCompareHelper::getProductsList(true);
        $object = Object::getForClass(Product::className());
        return $this->render(
            'compare',
            [
                'error' => empty($products) || null === $object,
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
        $products = ProductCompareHelper::getProductsList(true);
        $object = Object::getForClass(Product::className());
        return $this->render(
            'print',
            [
                'error' => empty($products) || null === $object,
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
        ProductCompareHelper::clearProductList();
        return null !== $backUrl ? $this->redirect($backUrl, 302) : true;
    }
}
