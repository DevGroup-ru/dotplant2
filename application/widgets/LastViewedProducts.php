<?php

namespace app\widgets;

use app\modules\shop\models\Product;
use Yii;
use yii\base\Widget;

class LastViewedProducts extends Widget
{
    public $elementNumber = 3;
    public $title = "Recently Viewed Products";
    public $viewFile = 'lastviewedproducts\main-view';

    /**
     * @inheritdoc
     * @return string
     */
    public function run()
    {
        parent::run();
        if (!Yii::$app->session->has('lastViewedProdsList')) {
            return "<!-- Can't render - session is not contains a products  -->";
        }
        $productsInSession = Yii::$app->session->get('lastViewedProdsList');
        if (is_array(!$productsInSession)) {
            return "<!-- Can't render - session is not contains a products array -->";
        }
        $products = [];
        foreach ($productsInSession as $elem) {
            $prod = Product::findById($elem['product_id']);
            if (null !== $prod) {
                $products[] = $prod;
            }
        }
        return $this->render(
            $this->viewFile,
            [
                'title' => $this->title,
                'elementNumber' => $this->elementNumber,
                'products' => $products
            ]
        );
    }
}
