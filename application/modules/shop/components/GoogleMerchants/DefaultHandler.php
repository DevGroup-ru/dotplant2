<?php

namespace app\modules\shop\components\GoogleMerchants;


use app\models\Property;
use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\models\Category;
use app\modules\shop\models\GoogleFeed;
use app\modules\shop\models\Product;
use yii\console\Exception;
use yii\helpers\Url;


class DefaultHandler implements ModificationDataInterface
{

    protected static $breadcrumbsData = [];

    protected static $modelSetting = null;


    public static function processData(ModificationDataEvent $event)
    {
        if (!self::$modelSetting) {
            self::$modelSetting = new GoogleFeed();
            self::$modelSetting->loadConfig();
        }
        $event->dataArray = [
            'title' => self::getRelation($event->model, 'item_title', $event->model->name),
            'description' => self::getRelation($event->model, 'item_description', $event->model->announce),
            'link' => $event->sender->host . htmlspecialchars(Url::toRoute(['@product', 'model' => $event->model])),
            'g:id' => $event->model->id,
            'g:condition' => self::$modelSetting->item_condition,
            'g:product_type' => self::getProductType($event->model),
            'g:availability' => static::getAvailability($event->model)
        ];
        if ($manufacturer = self::getRelation($event->model, 'item_brand', false)) {
            $event->dataArray['g:brand'] = $manufacturer;
        }
        if ($gin = self::getRelation($event->model, 'item_gtin', false)) {
            $event->dataArray['g:gtin'] = $gin;
        }
        if ($mpn = self::getRelation($event->model, 'item_mpn', false)) {
            $event->dataArray['g:mpn'] = $mpn;
        }

        if ($item_google_product_category = self::getRelation($event->model, 'item_google_product_category', false)) {
            $event->dataArray['g:google_product_category'] = $item_google_product_category;
        }

        if (!empty($event->model->old_price) && $event->model->old_price > $event->model->price) {
            $event->dataArray['g:price'] = static::getPrice(
                $event->model,
                $event->sender->mainCurrency,
                $event->model->old_price
            );
            $event->dataArray['g:sale_price'] = static::getPrice(
                $event->model,
                $event->sender->mainCurrency,
                $event->model->price
            );
        } else {

            $event->dataArray['g:price'] = static::getPrice(
                $event->model,
                $event->sender->mainCurrency,
                $event->model->price
            );
        }
        $imageObject = $event->model->image;
        if ($imageObject) {
            $event->dataArray['g:image_link'] = htmlspecialchars(
                $event->sender->host . $event->model->image->getOriginalUrl()
            );
        }
        if ($event->model->parent_id !== 0) {
            $event->dataArray['g:item_group_id'] = $event->model->parent_id;
        }
    }


    protected static function getRelation($model, $relationName, $default_value = '')
    {
        $result = $default_value;
        $relation = self::$modelSetting->$relationName;

        if ($relation['type'] === 'field' && !empty($relation['key'])) {
            try {
                $result = $model->{$relation['key']};
            } catch (Exception $e) {
            }
        } elseif ($relation['type'] === 'property' && !empty($relation['key'])) {
            try {
                $propertyKey = Property::find()->select('key')->where(['id' => $relation['key']])->asArray()->scalar();
                $result = $model->property($propertyKey);
            } catch (Exception $e) {
            }
        } elseif ($relation['type'] === 'relation' && !empty($relation['key']) && !empty($relation['value'])) {
            $relModel = $model->{$relation['key']}();
            $result = $relModel->$relation['value'];
        }

        return $result;
    }


    protected static function getProductType(Product $model)
    {
        if (!isset(self::$breadcrumbsData[$model->main_category_id])) {
            $parentIds = $model->getMainCategory()->getParentIds();
            $breadcrumbs = [];
            foreach ($parentIds as $id) {
                $breadcrumbs[] = Category::find()->select(['name'])->where(['id' => $id])->asArray()->scalar();
            }
            $breadcrumbs[] = $model->getMainCategory()->name;
            self::$breadcrumbsData[$model->main_category_id] = $breadcrumbs;
        }
        return htmlspecialchars(implode(' > ', self::$breadcrumbsData[$model->main_category_id]));
    }

    protected static function getPrice(Product $model, $mainCurrency, $price)
    {
        return number_format(
            CurrencyHelper::convertCurrencies(
                $price,
                $model->currency,
                $mainCurrency
            ),
            2,
            '.',
            ''
        ) . ' ' . $mainCurrency->iso_code;
    }

    protected static function getAvailability(Product $model)
    {
        if ($model->unlimited_count === 1) {
            $inStock = 'in stock';
        } else {
            $inStock = 'out of stock';
            foreach ($model->getWarehousesState() as $warehouse) {
                if ($warehouse->in_warehouse > 0) {
                    $inStock = 'in stock';
                    break;
                }
            }
        }
        return $inStock;
    }
}
