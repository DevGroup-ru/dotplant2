<?php

namespace app\widgets;

use app\models\Product;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;


class ProductsWidget extends Widget
{
    public $limit = 3;
    public $category_group_id = null;

    public function run()
    {
        parent::run();

        if (null === $this->category_group_id) {
            throw new InvalidConfigException("ProductsWidget.category_group_id should be set");
        }

        return $this->render(
            'products-widget',
            [
                'elementNumber' => $this->limit,
                'products' => $products
            ]
        );
    }
}
