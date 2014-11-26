<?php

namespace app\widgets;

use app\models\Product;
use Yii;
use yii\base\Widget;
use yii\web\NotFoundHttpException;

class ProductCompareListWidget extends Widget
{
    public $comparePage = '/product-compare/compare';
    public $limit = 3;

    public function run()
    {
        parent::run();
        $model_id = Yii::$app->request->get('model_id');
        if (null === $model_id) {
            return "<!-- Can't render the widget - model_id not found in request -->";
        }
        $cacheKey = "Product: " . $model_id;
        $product = Yii::$app->cache->get($cacheKey);
        if ($product === false) {
            $product = Product::findById($model_id);
            Yii::$app->cache->set(
                $cacheKey,
                $product,
                86400
            );
        }
        if (null === $product) {
            throw new NotFoundHttpException;
        }
        $comparisonProductList = Yii::$app->session->get('comparisonProductList');
        $prods = [];
        if (is_array($comparisonProductList)) {
            foreach ($comparisonProductList as $id) {
                $prod = Product::findById($id);
                if (null !== $prod) {
                    $prods[] = $prod;
                }
            }
        }
        return $this->render(
            'product-compare/list.php',
            [
                'id' => $product->id,
                'prods' => $prods,
                'comparePage' => $this->comparePage,
                'limit' => $this->limit
            ]
        );
    }
}
