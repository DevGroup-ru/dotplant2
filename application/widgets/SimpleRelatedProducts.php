<?php

namespace app\widgets;

use Yii;
use app;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * Simple Related Products widgets - finds related products and renders them using ProductsWidget
 *
 * Example usage in view file(products/show):
 *
 * ```
 *
 * <?= \app\widgets\SimpleRelatedProducts::widget([
 *    'model' => $model,
 *    'limit' => 4,
 *    'selected_category_id' => $model->getMainCategory()->id,
 *    'itemView' => '@app/web/theme/views/widgets/related-item',
 * ]) ?>
 *
 * ```
 *
 * @package app\widgets
 */
class SimpleRelatedProducts extends Widget
{
    /**
     * @var null|app\modules\shop\models\Product Product model instance
     */
    public $model = null;

    /**
     * @var int Count of related products to show
     */
    public $limit = 5;

    /**
     * @var null|integer Category ID related products should match(ie. parent category of current product)
     */
    public $selected_category_id = null;
    public $category_group_id = null;

    public $viewFile = null;

    public $itemView = null;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (null === $this->model) {
            throw new InvalidConfigException("SimpleRelatedProducts widget requires model instance to be passed.");
        }

        if (null === $this->category_group_id) {
            $this->category_group_id = $this->model->getMainCategory()->category_group_id;
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @return string
     */
    public function run()
    {
        parent::run();

        $additional_filters = [
            function(&$query, &$cacheKeyAppend) {
                $cacheKeyAppend.='SimpleRelatedProducts:'.$this->model->id;
            }
        ];

        $params = [
            'category_group_id' => $this->category_group_id,
            'selected_category_id' => $this->selected_category_id,
            'limit' => $this->limit,
            'force_limit' => true,
            'force_sorting' => '(product.id > ' . $this->model->id . ') DESC, id ASC',
            'additional_filters' => $additional_filters,
        ];

        if ($this->viewFile !== null) {
            $params['viewFile'] = $this->viewFile;
        }

        if ($this->itemView !== null) {
            $params['itemView'] = $this->itemView;
        }

        return ProductsWidget::widget($params);
    }
}