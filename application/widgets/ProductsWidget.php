<?php

namespace app\widgets;

use app\modules\shop\models\Product;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * ProductsWidget displays products by specified filtration criteria
 * @package app\widgets
 */
class ProductsWidget extends Widget
{
    public $limit = 3;
    public $category_group_id = null;
    public $values_by_property_id = [];
    public $selected_category_id = null;
    public $force_sorting = false;
    public $force_limit = true;
    public $additional_filters = [];

    public $itemView = '@app/modules/shop/views/product/item';
    public $viewFile = 'products-widget';

    /**
     * @inheritdoc
     * @return string
     * @throws InvalidConfigException
     * @throws \yii\web\ServerErrorHttpException
     */
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
            $this->force_limit,
            $this->additional_filters
        );

        return $this->render(
            $this->viewFile,
            [
                'products' => $products,
                'itemView' => $this->itemView,
                'values_by_property_id' => $this->values_by_property_id,
                'category_group_id' => $this->category_group_id,
                'selected_category_id' => $this->selected_category_id,
            ]
        );
    }
}
