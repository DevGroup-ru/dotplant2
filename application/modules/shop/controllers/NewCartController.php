<?php

namespace app\modules\shop\controllers;

use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\core\models\Events;
use app\modules\shop\events\OrderStageEvent;
use app\modules\shop\events\OrderStageLeafEvent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use app\modules\shop\models\Product;
use app\modules\shop\ShopModule;
use kartik\helpers\Html;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class NewCartController
 * @package app\modules\shop\controllers
 * @property ShopModule $module
 */
class NewCartController extends Controller
{
    /**
     * Get Order.
     * @param bool $create Create order if it does not exist
     * @param bool $throwException Throw exception if it does not exist
     * @return Order
     * @throws NotFoundHttpException
     */
    protected function loadOrder($create = false, $throwException = true)
    {
        $model = Order::getOrder($create);
        if (is_null($model) && $throwException) {
            throw new NotFoundHttpException;
        }
        return $model;
    }

    /**
     * Get OrderItem.
     * @param int $id
     * @param bool $checkOrderAttachment
     * @return OrderItem
     * @throws NotFoundHttpException
     */
    protected function loadOrderItem($id, $checkOrderAttachment = true)
    {
        /** @var OrderItem $orderItemModel */
        $orderModel = $checkOrderAttachment ? $this->loadOrder() : null;
        $orderItemModel = OrderItem::findOne($id);
        if (is_null($orderItemModel)
            || ($checkOrderAttachment && (is_null($orderModel) || $orderItemModel->order_id != $orderModel->id))
        ) {
            throw new NotFoundHttpException;
        }
        return $orderItemModel;
    }

    protected function addProductsToOrder($products, $parentId = 0)
    {
        if (!is_array($products)) {
            return []; // @todo Say about error
        }
        $order = $this->loadOrder(true);
        $result = [];
        foreach ($products as $product) {
            if (!isset($product['id']) || null === $productModel = Product::findById($product['id'])) {
                // @todo Say about error
                continue;
            }
            /** @var Product $productModel */
            $quantity = isset($product['quantity']) && (float) $product['quantity'] > 0
                ? (float) $product['quantity']
                : 1;
            if ($this->module->allowToAddSameProduct
                || is_null($orderItem = OrderItem::findOne(['order_id' => $order->id, 'product_id' => $productModel->id]))
            ) {
                $orderItem = new OrderItem;
                $totalPriceWithoutDiscount = $productModel->price * $quantity;
                $orderItem->attributes = [
                    'parent_id' => $parentId,
                    'order_id' => $order->id,
                    'product_id' => $productModel->id,
                    'quantity' => $quantity,
                    'price_per_pcs' => $productModel->price,
                    'total_price_without_discount' => $totalPriceWithoutDiscount,
                    'total_price' => $totalPriceWithoutDiscount, // @todo Need to implement discount. It has been calculated without discount now
                ];
            } else {
                /** @var OrderItem $orderItem */
                $orderItem->quantity += $quantity;
                $totalPriceWithoutDiscount = $productModel->price * $orderItem->quantity;
                $orderItem->total_price_without_discount = $totalPriceWithoutDiscount;
                $orderItem->total_price = $totalPriceWithoutDiscount; // @todo Need to implement discount. It has been calculated without discount now
            }
            $orderItem->save();
            if (isset($product['children'])) {
                $this->addProductsToOrder($product['children'], $orderItem->id); // @todo Merge result array
            }
        }
        return $result;
    }

    public function actionChangeQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $quantity = Yii::$app->request->post('quantity');
        if (is_null($id) || is_null($quantity) || (double) Yii::$app->request->post('quantity') <= 0) {
            throw new BadRequestHttpException;
        }
        $orderItem = $this->loadOrderItem($id);
        $order = $this->loadOrder();
        if (is_null($order->stage) || $order->stage->immutable_by_user == 1) {
            throw new BadRequestHttpException;
        }
        $orderItem->quantity = $quantity;
        // @todo Consider lock_product_price ?
        if ($orderItem->lock_product_price == 0) {
            $orderItem->price_per_pcs = $orderItem->product->price;
        }
        $totalPriceWithoutDiscount = $orderItem->price_per_pcs * $orderItem->quantity;
        $orderItem->total_price_without_discount = $totalPriceWithoutDiscount;
        $orderItem->total_price = $totalPriceWithoutDiscount; // @todo Need to implement discount. It has been calculated without discount now
        $orderItem->save();
        $mainCurrency = Currency::getMainCurrency();
        if ($order->calculate(true)) {
            return [
                'success' => true,
                'itemsCount' => $order->items_count,
                'itemPrice' => $mainCurrency->format($orderItem->total_price),
                'totalPrice' => $mainCurrency->format($order->total_price),
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('app', 'Cannot change quantity'),
            ];
        }
    }

    /**
     * Delete OrderItem action.
     * @param int $id
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        // @todo Throw exception if order stage isn't a forming.
        $this->loadOrderItem($id)->delete();
        $this->loadOrder()->calculate(true);
    }

    public function actionTest()
    {
        echo Html::beginForm(['/shop/new-cart/add']);
        echo Html::textInput('products[0][id]', 5);
        echo Html::textInput('products[0][children][0][id]', 3);
        echo Html::submitButton();
        echo Html::endForm();
    }

    public function actionAdd()
    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!is_array(Yii::$app->request->post('products', null))) {
            throw new BadRequestHttpException;
        }
        $result = $this->addProductsToOrder(Yii::$app->request->post('products'));
    }

    public function actionIndex()
    {
        $model = $this->loadOrder(false, false);
        if (!is_null($model)) {
            $model->calculate();
        }
        return $this->render('index', ['model' => $model]);
    }

    /**
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionStage()
    {
        $order = $this->loadOrder(false, false);
        if (empty($order)) {
            return $this->redirect(Url::to(['index']));
        }

        /** @var OrderStage $orderStage */
        $orderStage = $order->stage;

        if (null !== Yii::$app->session->get('OrderStageReach')) {
            /** @var Events $eventClass */
            $eventClass = Events::findByName($orderStage->event_name);
            if (!empty($eventClass) && is_subclass_of($eventClass->event_class_name, OrderStageEvent::className())) {
                /** @var OrderStageEvent $event */
                $event = new $eventClass->event_class_name;
                EventTriggeringHelper::triggerSpecialEvent($event);
            }
            Yii::$app->session->remove('OrderStageReach');
        }

        return $this->render('stage', [
            'stage' => $orderStage,
        ]);
    }

    /**
     * @param null $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionStageLeaf($id = null)
    {
        if (empty($id)) {
            return $this->redirect(Url::to(['stage']));
        }
        /** @var OrderStageLeaf $orderStageLeaf */
        $orderStageLeaf = OrderStageLeaf::findOne(['id' => $id]);
        if (empty($orderStageLeaf)) {
            return $this->redirect(Url::to(['stage']));
        }

        $order = $this->loadOrder(false, false);
        if (empty($order)) {
            return $this->redirect(Url::to(['index']));
        }

        $orderStage = $order->stage;
        if ($orderStage->id !== $orderStageLeaf->stage_from_id && $orderStage->id !== $orderStageLeaf->stage_to_id) {
            return $this->redirect(Url::to(['stage']));
        }

        /** @var Events $eventClassName */
        $eventClassName = Events::findByName($orderStageLeaf->event_name);
        if (!empty($eventClassName) && is_subclass_of($eventClassName->event_class_name, OrderStageLeafEvent::className())) {
            /** @var OrderStageLeafEvent $event */
            $event = new $eventClassName->event_class_name;
            EventTriggeringHelper::triggerSpecialEvent($event);
            if ($event->getStatus()) {
                $order->order_stage_id = $order->order_stage_id == $orderStageLeaf->stage_to_id ? $orderStageLeaf->stage_from_id : $orderStageLeaf->stage_to_id;
                $order->save();

                Yii::$app->session->set('OrderStageReach', true);

                return $this->redirect(Url::to(['stage']));
            }
        } else {
            return $this->redirect(Url::to(['stage']));
        }
    }
}
?>