<?php

namespace app\modules\shop\components\GoogleMerchants;


use app\modules\shop\helpers\CurrencyHelper;
use yii\helpers\Url;


class DefaultHandler implements ModificationDataInterface
{
    public static function processData(ModificationDataEvent $event)
    {
        $url = htmlspecialchars(Url::toRoute(['@product', 'model' => $event->model]));
        $image = htmlspecialchars($event->model->image->getOriginalUrl());
        if ($event->model->unlimited_count === 1) {
            $inStock = 'in stock';
        } else {
            $inStock = 'out of stock';
            foreach ($event->model->getWarehousesState() as $warehouse) {
                if ($warehouse->in_warehouse > 0) {
                    $inStock = 'in stock';
                    break;
                }
            }
        }

        $price = number_format(
                CurrencyHelper::convertCurrencies($event->model->price, $event->model->currency,
                    $event->sender->mainCurrency),
                2,
                '.',
                ''
            ) . ' ' . $event->sender->mainCurrency->iso_code;

        $event->data = [
            'title' => $event->model->name,
            'description' => $event->model->announce,
            'link' => $event->sender->host . $url,
            'g:id' => $event->model->id,
            'g:image_link' => $image,
            'g:condition' => 'new',
            'g:availability' => $inStock,
            'g:price' => $price
        ];
        if ($event->model->parent_id !== 0) {
            $event->data['g:item_group_id'] = $event->model->parent_id;
        }

    }
}