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
    public $values_by_property_id = [];
    public $selected_category_id = null;
    public $force_sorting = false;

    public $itemView = '@app/views/product/item';


    public function run()
    {
        parent::run();

        if (null === $this->category_group_id) {
            throw new InvalidConfigException("ProductsWidget.category_group_id should be set");
        }

        $products = Product::filteredProducts(
            $this->category_group_id,
            $this->values_by_property_id,
            $this->selected_category_id,
            $this->force_sorting,
            $this->limit,
            false,
            true
        );

        return $this->render(
            'products-widget',
            [
                'products' => $products,
                'itemView' => $this->itemView,
            ]
        );
    }
}
