<?php

namespace app\modules\shop\widgets;

use app\modules\shop\helpers\ProductCompareHelper;
use yii;
use yii\base\Widget;

class ProductCompare extends Widget
{
    public $viewFile = 'product-compare/list';
    public $fetchProducts = true;
    public $additional = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        $products = ProductCompareHelper::getProductsList(boolval($this->fetchProducts));
        return $this->render($this->viewFile, [
            'products' => $products,
            'additional' => $this->additional,
        ]);
    }
}