<?php

namespace app\modules\shop\components\GoogleMerchants;


use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use yii\helpers\Url;


class DefaultHandler implements ModificationDataInterface
{
    public static function processData(ModificationDataEvent $event)
    {
        $imageObject = $event->model->image;
        if (!$imageObject) {
            return;
        }
        $event->data = [
            'title' => $event->model->name,
            'description' => $event->model->announce,
            'link' => $event->sender->host . htmlspecialchars(Url::toRoute(['@product', 'model' => $event->model])),
            'g:id' => $event->model->id,
            'g:image_link' => htmlspecialchars(
                $event->sender->host . $event->model->image->getOriginalUrl()
            ),
            'g:condition' => 'new',
            'g:product_type' => self::getProductType($event->model),
            'g:availability' => static::getAvailability($event->model),
            'g:price' => static::getPrice($event->model, $event->sender->mainCurrency)
        ];
        if ($event->model->parent_id !== 0) {
            $event->data['g:item_group_id'] = $event->model->parent_id;
        }
    }


    protected static function getProductType(Product $model)
    {
        $parentIds = $model->getMainCategory()->getParentIds();

        $breadcrumbs = [];

        foreach ($parentIds as $id) {
            $breadcrumbs[] = Category::find()->select(['name'])->where(['id' => $id])->asArray()->scalar();
        }
        $breadcrumbs[] = $model->getMainCategory()->name;

        return htmlspecialchars(implode(' > ', $breadcrumbs));


    }

    protected static function getPrice(Product $model, $mainCurrency)
    {
        return number_format(
            CurrencyHelper::convertCurrencies($model->price, $model->currency,
                $mainCurrency),
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