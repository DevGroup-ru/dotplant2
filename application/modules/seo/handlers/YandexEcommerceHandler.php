<?php
namespace app\modules\seo\handlers;

use app\modules\seo\assets\YandexAnalyticsAssets;
use app\modules\shop\controllers\CartController;
use app\modules\shop\events\CartActionEvent;
use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\models\Product;
use yii\base\Event;
use yii\base\Object;

class YandexEcommerceHandler extends Object
{
    /**
     *
     */
    static public function installHandlers()
    {
        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_ADD,
            [self::className(), 'handleCartAdd']
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_REMOVE,
            [self::className(), 'handleRemoveFromCart']
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_QUANTITY,
            [self::className(), 'handleChangeQuantity']
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_CLEAR,
            [self::className(), 'handleClearCart']
        );

        YandexAnalyticsAssets::register(\Yii::$app->getView());
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleCartAdd(CartActionEvent $event)
    {
        $result = $event->getEventData();

        $ya = [];

        $ya['currency'] = CurrencyHelper::getMainCurrency()->iso_code;
        $ya['products'] = array_reduce($event->getProducts(), function($res, $item) {
            $quantity = $item['quantity'];
            /** @var Product $item */
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertToMainCurrency($item->price, $item->currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecYandex'] = $ya;
        $event->setEventData($result);
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleRemoveFromCart(CartActionEvent $event)
    {
        $result = $event->getEventData();

        $ya = [];

        $ya['currency'] = CurrencyHelper::getMainCurrency()->iso_code;
        $ya['products'] = array_reduce($event->getProducts(), function($res, $item) {
            $quantity = $item['quantity'];
            /** @var Product $item */
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertToMainCurrency($item->price, $item->currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecYandex'] = $ya;
        $event->setEventData($result);
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleChangeQuantity(CartActionEvent $event)
    {

    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleClearCart(CartActionEvent $event)
    {

    }

    /**
     * @param Product $model
     * @return string
     */
    static private function getCategories(Product $model)
    {
        $categories = [];
        $category = $model->category;
        while (null !== $category) {
            array_unshift($categories, $category->name);
            $category = $category->parent;
        }
        return implode('/', array_slice($categories, 0, 5));
    }
}
