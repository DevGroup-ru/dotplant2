<?php

namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\core\models\Events;
use app\modules\shop\helpers\PriceHelper;
use app\modules\shop\events\OrderStageEvent;
use app\modules\shop\events\OrderStageLeafEvent;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderCode;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\ShopModule;
use yii\helpers\Url;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CartController
 * @package app\modules\shop\controllers
 * @property ShopModule $module
 */
class CartController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => DisableRobotIndexBehavior::className(),
            ]
        ];
    }

    /**
     * Get Order.
     * @param bool $create Create order if it does not exist
     * @param bool $throwException Throw exception if it does not exist
     * @return null|Order
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
            throw new BadRequestHttpException;
        }
        $order = $this->loadOrder(true);
        $result = [
            'errors' => [],
            'itemModalPreview' => '',
        ];
        foreach ($products as $product) {
            if (!isset($product['id']) || is_null($productModel = Product::findById($product['id']))) {
                $result['errors'][] = Yii::t('app', 'Product not found.');
                continue;
            }
            /** @var Product $productModel */
            $quantity = isset($product['quantity']) && (double) $product['quantity'] > 0
                ? (double) $product['quantity']
                : 1;
            $quantity = $productModel->measure->ceilQuantity($quantity);
            if ($this->module->allowToAddSameProduct
                || is_null($orderItem = OrderItem::findOne(['order_id' => $order->id, 'product_id' => $productModel->id, 'parent_id' => 0]))
            ) {
                $orderItem = new OrderItem;
                $orderItem->attributes = [
                    'parent_id' => $parentId,
                    'order_id' => $order->id,
                    'product_id' => $productModel->id,
                    'quantity' => $quantity,
                    'price_per_pcs' =>  PriceHelper::getProductPrice(
                        $productModel,
                        $order,
                        1,
                        SpecialPriceList::TYPE_CORE
                    ),
                ];
            } else {
                /** @var OrderItem $orderItem */
                $orderItem->quantity += $quantity;
            }
            if (!$orderItem->save()) {
                $result['errors'][] = Yii::t('app', 'Cannot save order item.');
            } else {
                // refresh order
                Order::clearStaticOrder();
                $order = $this->loadOrder(false);
            }
            if (isset($product['children'])) {
                $result = ArrayHelper::merge(
                    $result,
                    $this->addProductsToOrder($product['children'], $orderItem->id)
                );
            }
            if ($parentId === 0) {
                $result['itemModalPreview'] .= $this->renderPartial(
                    'item-modal-preview',
                    [
                        'order' => $order,
                        'orderItem' => $orderItem,
                        'product' => $product,
                    ]
                );
            }
        }
        if ($parentId === 0) {
            $order->calculate(true);
        }
        $mainCurrency = Currency::getMainCurrency();
        return ArrayHelper::merge(
            $result,
            [
                'itemsCount' => $order->items_count,
                'success' => true, // @todo Return true success value
                'totalPrice' => $mainCurrency->format($order->total_price),
            ]
        );
    }

    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!is_array(Yii::$app->request->post('products', null))) {
            throw new BadRequestHttpException;
        }
        $order = $this->loadOrder(true);
        if (is_null($order->stage) || $order->stage->immutable_by_user == 1) {
            throw new BadRequestHttpException;
        }
        return $this->addProductsToOrder(Yii::$app->request->post('products'));
    }

    public function actionChangeQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $quantity = Yii::$app->request->post('quantity');
        if (is_null($id) || is_null($quantity) || (double) $quantity <= 0) {
            throw new BadRequestHttpException;
        }
        $orderItem = $this->loadOrderItem($id);
        $order = $this->loadOrder();
        if (is_null($order->stage) || $order->stage->immutable_by_user == 1) {
            throw new BadRequestHttpException;
        }
        $orderItem->quantity = $orderItem->product->measure->ceilQuantity($quantity);
        // @todo Consider lock_product_price ?
        if ($orderItem->lock_product_price == 0) {
            $orderItem->price_per_pcs = PriceHelper::getProductPrice(
                $orderItem->product,
                $order,
                1,
                SpecialPriceList::TYPE_CORE
            );
        }
        $orderItem->save();
        $mainCurrency = Currency::getMainCurrency();
        if ($order->calculate(true)) {
            return [
                'success' => true,
                'itemsCount' => $order->items_count,
                'itemPrice' => $mainCurrency->format($orderItem->total_price),
                'totalPrice' => $mainCurrency->format($order->total_price),
                'calculatedQuantity' => $orderItem->quantity,
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
     * @return array
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->loadOrder();
        if (is_null($order->stage) || $order->stage->immutable_by_user == 1) {
            throw new BadRequestHttpException;
        }
        if ($this->loadOrderItem($id)->delete() && $order->calculate(true)) {
            $mainCurrency = Currency::getMainCurrency();
            return [
                'success' => true,
                'itemsCount' => $order->items_count,
                'totalPrice' => $mainCurrency->format($order->total_price),
                'itemModalPreview' => $this->renderPartial("item-modal-preview",
                    [
                        "order" => $order,
                        "orderItem" => null,
                        "product" => null
                    ]
                )
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('app', 'Cannot change additional params'),
            ];
        }
    }

    public function actionClear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->loadOrder();
        foreach ($order->items as $item) {
            $item->delete();
        }
        $order->calculate(true);
        return ['success' => true,];
    }

    public function actionIndex()
    {
        $model = $this->loadOrder(false, false);
        $orderCode = null;
        if (!is_null($model)) {
            $orderCode = OrderCode::find()
                ->where(
                    [
                        'order_id' => $model->id,
                        'status' => 1
                    ]
                )
                ->one();

            if ($orderCode === null) {
                $orderCode = new OrderCode();

                if (Yii::$app->request->isPost) {
                    $orderCode->load(Yii::$app->request->post());
                    $orderCode->order_id = $model->id;
                    if ($orderCode->save()) {
                        $this->refresh();
                    }
                }
            }
            $model->calculate();
        }
        return $this->render('index', ['model' => $model, 'orderCode' => $orderCode]);
    }

    /**
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionStage()
    {
        $order = $this->loadOrder(false, false);
        if (empty($order) || $order->getImmutability(Order::IMMUTABLE_USER)) {
            return $this->redirect(Url::to(['index']));
        }

        /** @var OrderStage $orderStage */
        $orderStage = $order->stage;
        $eventData = ['order' => $order];

        if (0 === intval($orderStage->is_in_cart)) {
            Yii::$app->session->remove('orderId');
            $order->in_cart = 0;
            $order->save();
            Order::clearStaticOrder();
        }
        if (1 === intval($orderStage->become_non_temporary)) {
            $order->temporary = 0;
            $order->save();
        }

//        if (null !== Yii::$app->session->get('OrderStageReach')) {
        /** @var Events $eventClass */
        $eventClass = Events::findByName($orderStage->event_name);
        if (!empty($eventClass) && is_subclass_of($eventClass->event_class_name, OrderStageEvent::className())) {
            /** @var OrderStageEvent $event */
            $event = new $eventClass->event_class_name;
            $event->setEventData($eventData);
            EventTriggeringHelper::triggerSpecialEvent($event);
            $eventData = $event->eventData();

            if (!empty($eventData['__redirect'])) {
                return $this->redirect($eventData['__redirect']);
            }
        }
        Yii::$app->session->remove('OrderStageReach');
//        }

        return $this->render(
            'stage',
            [
                'order' => $order,
                'stage' => $orderStage,
                'eventData' => $eventData,
            ]
        );
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

        if (null !== Yii::$app->request->get('previous') && 1 !== intval($orderStageLeaf->stageFrom->immutable_by_user)) {
            $order->order_stage_id = $orderStageLeaf->stageFrom->id;
            $order->save();
        } else {
            /** @var Events $eventClassName */
            $eventClassName = Events::findByName($orderStageLeaf->event_name);
            if (!empty($eventClassName) && is_subclass_of($eventClassName->event_class_name, OrderStageLeafEvent::className())) {
                /** @var OrderStageLeafEvent $event */
                $event = new $eventClassName->event_class_name;
                EventTriggeringHelper::triggerSpecialEvent($event);
                if ($event->getStatus()) {
                    $order->order_stage_id = $order->order_stage_id == $orderStageLeaf->stage_to_id
                        ? $orderStageLeaf->stage_from_id
                        : $orderStageLeaf->stage_to_id;
                    $order->save();

                    Yii::$app->session->set('OrderStageReach', true);
                }
            }
        }

        return $this->redirect(Url::to(['stage']));
    }
}
