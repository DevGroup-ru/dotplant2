<?php
namespace app\modules\seo\handlers;

use app\components\Controller;
use app\modules\core\events\ViewEvent;
use app\modules\seo\assets\GoogleAnalyticsAssets;
use app\modules\shop\controllers\CartController;
use app\modules\shop\events\CartActionEvent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\Product;
use app\modules\shop\helpers\CurrencyHelper;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\web\View;

class GoogleEcommerceHandler extends BaseObject
{
    /** @var Currency $currency */
    static protected $currency = null;

    /**
     *
     */
    static public function installHandlers(ActionEvent $event)
    {
        $currency = \Yii::$app->getModule('seo')->analytics['ecGoogle']['currency'];
        if (AnalyticsHandler::CURRENCY_MAIN === intval($currency)) {
            static::$currency = CurrencyHelper::getMainCurrency();
        } elseif (AnalyticsHandler::CURRENCY_USER === intval($currency)) {
            static::$currency = CurrencyHelper::getUserCurrency();
        } else {
            static::$currency = CurrencyHelper::findCurrencyByIso($currency);
        }

        $route = implode('/', [
            $event->action->controller->module->id,
            $event->action->controller->id,
            $event->action->id
        ]);

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_ADD,
            [self::className(), 'handleCartAdd'],
            false
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_REMOVE,
            [self::className(), 'handleRemoveFromCart'],
            false
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_QUANTITY,
            [self::className(), 'handleChangeQuantity'],
            false
        );

        Event::on(
            CartController::className(),
            CartController::EVENT_ACTION_CLEAR,
            [self::className(), 'handleClearCart'],
            false
        );

        Event::on(
            Controller::className(),
            Controller::EVENT_PRE_DECORATOR,
            [self::className(), 'handleProductShow']
        );

        if ('shop/cart/index' === $route) {
            self::handleCartIndex();
        }

        GoogleAnalyticsAssets::register(\Yii::$app->getView());
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleCartAdd(CartActionEvent $event)
    {
        $result = $event->getEventData();
        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [];

        $ga['currency'] = $currency->iso_code;
        $ga['products'] = array_reduce($event->getProducts(), function($res, $item) use ($currency) {
            /** @var Product $item */
            $quantity = $item['quantity'];
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertCurrencies($item->price, $item->currency, $currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecGoogle'] = $ga;
        $event->setEventData($result);
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleRemoveFromCart(CartActionEvent $event)
    {
        $result = $event->getEventData();
        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [];

        $ga['currency'] = $currency->iso_code;
        $ga['products'] = array_reduce($event->getProducts(), function($res, $item) use ($currency) {
            /** @var Product $item */
            $quantity = $item['quantity'];
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertCurrencies($item->price, $item->currency, $currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecGoogle'] = $ga;
        $event->setEventData($result);
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleChangeQuantity(CartActionEvent $event)
    {
        $result = $event->getEventData();
        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [];

        $ga['currency'] = $currency->iso_code;
        $ga['products'] = array_reduce($event->getProducts(), function($res, $item) use ($currency) {
            /** @var Product $item */
            $quantity = $item['quantity'];
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertCurrencies($item->price, $item->currency, $currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecGoogle'] = $ga;
        $event->setEventData($result);
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleClearCart(CartActionEvent $event)
    {
        $result = $event->getEventData();
        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [];

        $ga['currency'] = $currency->iso_code;
        $ga['products'] = array_reduce($event->getProducts(), function($res, $item) use ($currency) {
            /** @var Product $item */
            $quantity = $item['quantity'];
            $item = $item['model'];

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => self::getCategories($item),
                'price' => CurrencyHelper::convertCurrencies($item->price, $item->currency, $currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecGoogle'] = $ga;
        $event->setEventData($result);
    }

    /**
     * @param ViewEvent $event
     */
    static public function handleProductShow(ViewEvent $event)
    {
        if ('shop/product/show' !== trim(\Yii::$app->requestedRoute, '/')) {
            return ;
        }

        /** @var Product $model */
        $model = isset($event->params['model']) ? $event->params['model'] : null;
        if (false === $model instanceof Product) {
            return ;
        }

        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [
            'action' => 'detail',
            'currency' => $currency->iso_code,
            'products' => [
                'id' => $model->id,
                'name' => $model->name,
                'category' => self::getCategories($model),
                'price' => CurrencyHelper::convertCurrencies($model->price, $model->currency, $currency),
                'quantity' => null === $model->measure ? 1 : $model->measure->nominal,
            ]
        ];

        $js = 'window.DotPlantParams = window.DotPlantParams || {};';
        $js .= 'window.DotPlantParams.ecGoogle = ' . Json::encode($ga) . ';';
        \Yii::$app->getView()->registerJs($js, View::POS_BEGIN);
    }

    /**
     *
     */
    static public function handleCartIndex()
    {
        if (null === $order = Order::getOrder()) {
            return ;
        }

        /** @var Currency $currency */
        $currency = static::$currency;

        $ga = [
            'action' => 'action',
            'type' => 'checkout',
            'step' => 1,
            'currency' => $currency->iso_code,
            'products' => [],
        ];

        foreach ($order->items as $item) {
            $ga['products'][] = [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'category' => self::getCategories($item->product),
                'price' => CurrencyHelper::convertCurrencies($item->product->price, $item->product->currency, $currency),
                'quantity' => $item->quantity,
            ];
        }


        $js = 'window.DotPlantParams = window.DotPlantParams || {};';
        $js .= 'window.DotPlantParams.ecGoogle = ' . Json::encode($ga) . ';';
        \Yii::$app->getView()->registerJs($js, View::POS_BEGIN);
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
