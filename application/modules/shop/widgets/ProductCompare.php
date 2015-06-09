<?php

namespace app\modules\shop\widgets;

use yii;
use yii\base\Widget;

class ProductCompare extends Widget
{
    public $viewFile = 'product-compare/list';
    public $urlComparePage = '/shop/product-compare/compare';

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        $comparisonProductList = Yii::$app->session->get('comparisonProductList');
    }
}