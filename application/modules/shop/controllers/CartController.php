<?php
namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\core\models\Events;
use app\modules\shop\events\CartActionEvent;
use app\modules\shop\handlers\CartHandler;
use app\modules\shop\helpers\PriceHelper;
use app\modules\shop\events\OrderStageEvent;
use app\modules\shop\events\OrderStageLeafEvent;
use app\modules\shop\models\Addon;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderCode;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\OrderStage;
use app\modules\shop\models\OrderStageLeaf;
use app\modules\shop\models\Product;
use app\modules\shop\models\SpecialPriceList;
use app\modules\shop\models\UserPreferences;
use app\modules\shop\ShopModule;
use yii\base\Event;
use yii\helpers\Url;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\shop\helpers\CurrencyHelper;

/**
 * Class CartController
 * @package app\modules\shop\controllers
 * @property ShopModule $module
 */
class CartController extends Controller
{
    const EVENT_ACTION_ADD = 'shopCartActionAdd';
    const EVENT_ACTION_REMOVE = 'shopCartActionRemove';
    const EVENT_ACTION_QUANTITY = 'shopCartActionQuantity';
    const EVENT_ACTION_CLEAR = 'shopCartActionClear';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => DisableRobotIndexBehavior::className(),
                'setSameOrigin' => false
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

    /**
     * @param Order $order
     * @param array $products
     * @param $result
     * @param int $parentId
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    protected function addProductsToOrder(Order $order, $products = [], $result, $parentId = 0)
    {
        $parentId = intval($parentId);
        if ($parentId !== 0) {
            // if parent id is set - order item should exist in this order!
            $parentOrderItem = OrderItem::findOne(['order_id' => $order->id, 'id' => $parentId]);
            if ($parentOrderItem === null) {
                throw new BadRequestHttpException;
            }
        }

        foreach ($products as $product) {
            $productModel = $addonModel = null;

            if (!isset($product['id'])) {
                if (isset($product['addon_id'])) {
                    $addonModel = Addon::findById($product['addon_id']);
                }
            } else {
                $productModel = Product::findById($product['id']);
            }

            if ($addonModel === null && $productModel === null) {
                $result['errors'][] = Yii::t('app', 'Product not found.');
                continue;
            }

            /** @var Product $productModel */
            $quantity = isset($product['quantity']) && (double) $product['quantity'] > 0
                ? (double) $product['quantity']
                : 1;

            $condition = ['order_id' => $order->id, 'parent_id' => 0];
            if ($productModel !== null) {
                $condition['product_id'] = $productModel->id;
                $thisItemModel = $productModel;
                $quantity = $productModel->measure->ceilQuantity($quantity);
            } else {
                $condition['addon_id'] = $addonModel->id;
                $thisItemModel = $addonModel;
                if (!$addonModel->can_change_quantity) {
                    $quantity = 1;
                }
            }

            $orderItem = OrderItem::findOne($condition);
            if ($this->module->allowToAddSameProduct || null === $orderItem) {
                $orderItem = new OrderItem;
                $orderItem->attributes = [
                    'parent_id' => $parentId,
                    'order_id' => $order->id,
                    'quantity' => $quantity,
                    'price_per_pcs' => PriceHelper::getProductPrice(
                        $thisItemModel,
                        $order,
                        1,
                        SpecialPriceList::TYPE_CORE
                    ),
                ];
                if (empty($product['customName']) === false) {
                    $orderItem->custom_name = $product['customName'];
                }
                if ($productModel !== null) {
                    $orderItem->product_id = $thisItemModel->id;
                } else {
                    $orderItem->addon_id = $thisItemModel->id;
                }
            } else {
                /** @var OrderItem $orderItem */
                if ($addonModel !== null && !$addonModel->can_change_quantity) {
                    // quantity can not be changed
                    $quantity = 0;
                }
                if (null !== $orderItem) {
                    $orderItem->quantity += $quantity;
                }
            }
            if (false === $orderItem->save()) {
                $result['errors'][] = Yii::t('app', 'Cannot save order item.');
            } else {
                // refresh order
                Order::clearStaticOrder();
                $order = $this->loadOrder(false);
            }

            if (null !== $productModel) {
                $result['products'][] = [
                    'model' => $productModel,
                    'quantity' => $quantity,
                    'orderItem' => $orderItem,
                ];
            }

            if (isset($product['children']) && is_array($product['children'])) {
                $result = $this->addProductsToOrder($order, $product['children'], $result, $orderItem->id);
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $products = Yii::$app->request->post('products', []);

        if (false === is_array($products) || true === empty($products)) {
            throw new BadRequestHttpException;
        }

        $order = $this->loadOrder(true);
        if (null === $order->stage || true === $order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException;
        }

        $result = [
            'products' => [],
            'errors' => [],
            'additional' => [],
        ];

        $result = $this->addProductsToOrder($order, $products, $result);

        $order = $this->loadOrder();
        $order->calculate(true);

        $userCurrency = CurrencyHelper::getUserCurrency();

        $result['success'] = count($products) > 0 && count($result['products']) > 0;
        $result['itemsCount'] = $order->items_count;
        $result['totalPrice'] = $userCurrency->format(
            CurrencyHelper::convertToUserCurrency($order->total_price, CurrencyHelper::getMainCurrency())
        );

        $event = new CartActionEvent($order, $result['products']);
        Event::trigger($this, self::EVENT_ACTION_ADD, $event);

        $result['additional'] = $event->getEventData();

        /**
         * Backward compatibility
         */
        $result['itemModalPreview'] = isset($result['additional']['bcItemModalPreview'])
            ? $result['additional']['bcItemModalPreview']
            : '';

        $result['products'] = $this->productsModelsToArray($result['products']);

        return $result;
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionChangeQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];

        $id = Yii::$app->request->post('id');
        $quantity = floatval(Yii::$app->request->post('quantity', 0));

        if (null === $id || $quantity <= 0) {
            throw new BadRequestHttpException;
        }

        $orderItem = $this->loadOrderItem($id);
        $order = $this->loadOrder();

        if (null === $order->stage || true === $order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException;
        }

        $model = $orderItem->product;
        $product = [
            [
                'model' => $model,
                'quantity' => $model->measure->ceilQuantity($quantity) - $orderItem->quantity,
                'orderItem' => $orderItem,
            ]
        ];

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

        $event = new CartActionEvent($order, $product);
        Event::trigger($this, self::EVENT_ACTION_QUANTITY, $event);

        $result['additional'] = $event->getEventData();
        $result['success'] = $order->calculate(true);
        $result['message'] = false === $result['success'] ? Yii::t('app', 'Cannot change quantity') : '';
        $result['itemsCount'] = $order->items_count;
        $result['itemPrice'] = CurrencyHelper::getMainCurrency()->format($orderItem->total_price);
        $result['totalPrice'] = CurrencyHelper::getMainCurrency()->format($order->total_price);
        $result['calculatedQuantity'] = $orderItem->quantity;
        $result['products'] = $this->productsModelsToArray($product);

        return $result;
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

        $result = [];

        $order = $this->loadOrder();
        if (null === $order->stage || true === $order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException;
        }

        $orderItem = $this->loadOrderItem($id);
        $model = $orderItem->product;

        $product = [
            [
                'model' => $model,
                'quantity' => $orderItem->quantity,
                'orderItem' => $orderItem,
            ]
        ];
        $result['success'] = $orderItem->delete() && $order->calculate(true);
        $event = new CartActionEvent($order, $product);
        Event::trigger($this, self::EVENT_ACTION_REMOVE, $event);

        $result['additional'] = $event->getEventData();

        /**
         * Backward compatibility
         */
        $result['itemModalPreview'] = isset($result['additional']['bcItemModalPreview'])
            ? $result['additional']['bcItemModalPreview']
            : '';
        $result['products'] = $this->productsModelsToArray($product);
        $result['itemsCount'] = $order->items_count;
        $result['totalPrice'] = CurrencyHelper::getMainCurrency()->format($order->total_price);
        $result['message'] = false === $result['success'] ? Yii::t('app', 'Cannot change additional params') : '';

        return $result;
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionClear()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = [];

        $order = $this->loadOrder();
        if (null === $order->stage || true === $order->getImmutability(Order::IMMUTABLE_USER)) {
            throw new BadRequestHttpException;
        }

        $products = array_reduce($order->items, function($res, $item) {
            $res[] = [
                'model' => $item->product,
                'quantity' => $item->quantity,
                'orderItem' => $item,
            ];
            return $res;
        }, []);

        /** @var OrderItem $item */
        foreach ($order->items as $item) {
            $item->delete();
        }
        Order::clearStaticOrder();
        $order = $this->loadOrder();
        $result['success'] = $order->calculate(true);

        $event = new CartActionEvent($order, $products);
        Event::trigger($this, self::EVENT_ACTION_CLEAR, $event);

        $result['additional'] = $event->getEventData();
        $result['products'] = $this->productsModelsToArray($products);

        return $result;
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
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

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (false === parent::beforeAction($action)) {
            return false;
        }

        $_renderCartPreview = [
            self::EVENT_ACTION_ADD,
            self::EVENT_ACTION_REMOVE,
            self::EVENT_ACTION_QUANTITY,
            self::EVENT_ACTION_CLEAR,
        ];
        foreach ($_renderCartPreview as $_eventName) {
            Event::on(self::className(), $_eventName, [CartHandler::className(), 'renderCartPreview']);
        }

        return true;
    }

    /**
     * @param array $products
     * @return array
     */
    private function productsModelsToArray($products)
    {
        return array_reduce($products, function($res, $item) {
            /** @var Product $model */
            $model = $item['model'];

            $i = [
                'id' => $model->id,
                'name' => $model->name,
                'price' => CurrencyHelper::convertToMainCurrency($model->price, $model->currency),
                'currency' => CurrencyHelper::getMainCurrency()->iso_code,
            ];
            if (isset($item['quantity'])) {
                $i['quantity'] = $item['quantity'];
            }

            $res[] = $i;
            return $res;
        }, []);
    }
}
