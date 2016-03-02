<?php
namespace app\modules\shop\widgets;

use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;
use app\modules\shop\models\Currency;


class PriceSliderRangeWidget extends SliderRangeWidget
{

    public $attributeName = 'Цена';

    public $minAttribute = 'price_min';
    public $maxAttribute = 'price_max';
    public $changeFlagAttribute = 'price_change_flag';

    public $categoryId;


    public function init()
    {
        $cacheKey = 'priceRangeCategory' . $this->categoryId;

        if (!$data = Yii::$app->cache->get($cacheKey)) {
            $dataMin = (new Query())->select('product.price as min_price, product.currency_id AS min_currency')
                ->from(['product', 'product_category'])
                ->where('product.id = product_category.object_model_id')
                ->andWhere(
                    [
                        'product.active' => 1,
                        'product_category.category_id' => $this->categoryId
                    ]
                )->orderBy('product.price ASC')
                ->one();

            $dataMax = (new Query())->select('product.price as max_price, product.currency_id AS max_currency')
                ->from(['product', 'product_category'])
                ->where('product.id = product_category.object_model_id')
                ->andWhere(
                        [
                            'product.active' => 1,
                            'product_category.category_id' => $this->categoryId
                        ]
                )->orderBy('product.price DESC')
                ->one();

            if (is_array($dataMax) && is_array($dataMin)) {
                $data = ArrayHelper::merge($dataMax, $dataMin);
            }

            if ($data) {
                $data['min_price'] = (int) $data['min_price'];
                $data['max_price'] = (int) $data['max_price'];
            }

        }
        if ($data && isset($data['min_price']) && isset($data['max_price'])) {
            $currency = Currency::getMainCurrency();
            if ($data['min_currency'] !== $currency->id) {
                $foreignCurrency = Currency::findById($data['min_currency']);
                $this->minValue = round($data['min_price'] / $foreignCurrency->convert_nominal * $foreignCurrency->convert_rate);
            } else {
                $this->minValue = $data['min_price'];
            }
            if ($data['max_currency'] !== $currency->id) {
                $foreignCurrency = Currency::findById($data['max_currency']);
                $this->maxValue = round($data['max_price'] / $foreignCurrency->convert_nominal * $foreignCurrency->convert_rate);
            } else {
                $this->maxValue = $data['max_price'];
            }

            $get = ArrayHelper::merge(Yii::$app->request->get(), Yii::$app->request->post());

            if (isset($get[$this->minAttribute]) && is_numeric($get[$this->minAttribute])) {
                $this->changeFlagDefaultValue = 1;
                $this->minValueNow = $get[$this->minAttribute];
            } else {
                $this->minValueNow = $this->minValue;
            }

            if (isset($get[$this->maxAttribute]) && is_numeric($get[$this->maxAttribute])) {
                $this->changeFlagDefaultValue = 1;
                $this->maxValueNow = $get[$this->maxAttribute];
            } else {
                $this->maxValueNow = $this->maxValue;
            }
        }
        return parent::init();

    }


}