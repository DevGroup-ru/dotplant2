<?php
namespace app\modules\seo\handlers;

use app\modules\seo\assets\GoogleAnalyticsAssets;
use app\modules\shop\controllers\CartController;
use app\modules\shop\events\CartActionEvent;
use app\modules\shop\models\Product;
use app\modules\shop\helpers\CurrencyHelper;
use yii\base\Event;
use yii\base\Object;

class GoogleEcommerceHandler extends Object
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

        GoogleAnalyticsAssets::register(\Yii::$app->getView());
    }

    /**
     * @param CartActionEvent $event
     */
    static public function handleCartAdd(CartActionEvent $event)
    {
        $result = $event->getEventData();

        $ga = [];

        $ga['currency'] = CurrencyHelper::getMainCurrency()->iso_code;
        $ga['products'] = array_reduce($event->getProducts(), function($res, $item) {
            $quantity = $item['quantity'];
            $item = $item['model'];

            /** @var Product $item */
            $categories = [];
            $category = $item->category;
            while (null !== $category) {
                $categories[] = $category->name;
                $category = $category->parent;
            }
            $category = implode('/', array_slice($categories, 0, 5));

            $res[] = [
                'id' => $item->id,
                'name' => $item->name,
                'category' => $category,
                'price' => CurrencyHelper::convertToMainCurrency($item->price, $item->currency),
                'quantity' => $quantity,
            ];
            return $res;
        }, []);

        $result['ecGoogle'] = $ga;
        $event->setEventData($result);
    }
}
