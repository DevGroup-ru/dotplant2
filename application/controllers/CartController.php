<?php

namespace app\controllers;

use app\behaviors\Csrf;
use app\models\Cart;
use app\models\Order;
use app\models\OrderItem;
use app\models\OrderTransaction;
use app\models\PaymentType;
use app\models\Product;
use app\models\ShippingOption;
use app\properties\HasProperties;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CartController extends Controller
{
    private function getCachedData($cacheKey, $className)
    {
        $data = Yii::$app->cache->get($cacheKey);
        if ($data === false) {
            $data = call_user_func([$className, 'findAll'], ['active' => 1]);
            Yii::$app->cache->set(
                $cacheKey,
                $data,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag($className),
                        ],
                    ]
                )
            );
        }
        return $data;
    }

    /**
     * @param $id
     * @param null $allowedStatuses
     * @return Order|null|Response
     * @throws NotFoundHttpException
     */
    private function loadOrder($id, $allowedStatuses = null)
    {
        $model = Order::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        if (Yii::$app->user->isGuest) {
            if (!Yii::$app->session->has('orders') || !in_array($id, (array) Yii::$app->session->get('orders'))) {
                throw new NotFoundHttpException;
            }
        } else {
            if ($model->user_id != Yii::$app->user->id) {
                throw new NotFoundHttpException;
            }
        }
        if ($allowedStatuses !==null && !in_array($model->order_status_id, (array) $allowedStatuses)) {
            Yii::$app->session->setFlash('error', Yii::t('shop', 'Cannot change this order'));
            return $this->redirect(['/cart']);
        }
        return $model;
    }

    private function loadLastTransactionOrder($allowedStatuses = null)
    {
        $query = Order::find()
            ->join('JOIN', OrderTransaction::tableName(), OrderTransaction::tableName() . '.order_id = Order.id')
            ->orderBy(OrderTransaction::tableName() . '.id DESC')
            ->limit(1);
        if (Yii::$app->user->isGuest) {
            if (Yii::$app->session->has('orders') && is_array(Yii::$app->session->get('orders'))) {
                $query->where(['in', Order::tableName() . '.id', Yii::$app->session->get('orders')]);
            } else {
                return null;
            }
        } else {
            $query->where(['user_id' => Yii::$app->user->id]);
        }
        if ($allowedStatuses === null) {
            $query->andWhere(['in', 'id', $allowedStatuses]);
        }
        return $query->one();
    }

    public function behaviors()
    {
        return [
            [
                'class' => Csrf::className(),
                'disabledActions' => ['payment-result', 'payment-success', 'payment-error'],
            ],
        ];
    }

    public function actionIndex()
    {
        $cart = Cart::getCart(false);
        if ($cart !== null) {
            $cart->reCalc(true);
        }
        return $this->render('index', ['cart' => $cart]);
    }

    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->post('id') === null) {
            throw new BadRequestHttpException;
        }
        $product = Product::findOne(Yii::$app->request->post('id'));
        if ($product === null) {
            throw new NotFoundHttpException;
        }
        $quantity = Yii::$app->request->post('quantity', 1);
        $additionalParams = Yii::$app->request->post('additionalParams', '{"additionalPrice":0}');
        $cart = Cart::getCart(true);
        if (isset($cart->items[$product->id])) {
            $cart->items[$product->id] = [
                'quantity' => $quantity + $cart->items[$product->id]['quantity'],
                'additionalParams' => $additionalParams
            ];
        } else {
            $cart->items[$product->id] = [
                'quantity' => $quantity,
                'additionalParams' => $additionalParams
            ];
        }
        if ($cart->reCalc()) {
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
                'itemModalPreview' => $this->renderPartial(
                    'item-modal-preview',
                    [
                        'order' => $cart,
                        'product' => $product,
                    ]
                ),
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot add product to cart'),
            ];
        }
    }

    public function actionChangeQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->post('id') === null || Yii::$app->request->post('quantity') === null
            || (int) Yii::$app->request->post('quantity') < 1) {
            throw new BadRequestHttpException;
        }
        $id = Yii::$app->request->post('id');
        $product = Product::findOne($id);
        $cart = Cart::getCart(false);
        if ($cart === null || !isset($cart->items[$id]) || $product === null) {
            throw new NotFoundHttpException;
        }
        $cart->items[$id]['quantity'] = Yii::$app->request->post('quantity');
        $additionalParams = Json::decode($cart->items[$id]['additionalParams']);
        if ($cart->reCalc()) {
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'itemPrice' => Yii::$app->formatter->asDecimal($cart->items[$id]['quantity'] * ($product->convertedPrice() + $additionalParams['additionalPrice']), 2),
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot change quantity'),
            ];
        }
    }

    public function actionChangeAdditionalParams()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->post('id') === null || Yii::$app->request->post('additionalParams') === null) {
            throw new BadRequestHttpException;
        }
        $id = Yii::$app->request->post('id');
        $product = Product::findOne($id);
        $cart = Cart::getCart(false);
        if ($cart === null || !isset($cart->items[$id]) || $product === null) {
            throw new NotFoundHttpException;
        }
        $additionalParams = ArrayHelper::merge(
            Json::decode($cart->items[$id]['additionalParams']),
            Json::decode(Yii::$app->request->post('additionalParams'))
        );
        $cart->items[$id]['additionalParams'] = Json::encode($additionalParams);
        if ($cart->reCalc()) {
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'itemPrice' => Yii::$app->formatter->asDecimal($cart->items[$id]['quantity'] * ($product->convertedPrice() + $additionalParams['additionalPrice']), 2),
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot change additional params'),
            ];
        }
    }


    public function actionGetDeliveryPrice()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->post('shipping_option_id') === null) {
            throw new BadRequestHttpException;
        }

        $cart = Cart::getCart(false);
        if ($cart) {

            $shippingOption = ShippingOption::findOne(Yii::$app->request->post('shipping_option_id'));

            if ($shippingOption) {

                return [
                    'success' =>true,
                    'name' => $shippingOption->name,
                    'shipping_price' => $shippingOption->cost,
                    'total_price' => Yii::$app->formatter->asDecimal($cart->total_price),
                    'full_price' => Yii::$app->formatter->asDecimal($cart->total_price + $shippingOption->cost),
                    'currency' => Yii::$app->params['currency']
                ];

            }

        }

        return [
            'success' => false,
            'message' => Yii::t('shop', 'Error data'),
        ];


    }


    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = Cart::getCart(false);
        if ($cart === null || !isset($cart->items[$id])) {
            throw new NotFoundHttpException;
        }
        unset($cart->items[$id]);
        if ($cart->reCalc()) {
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
            ];
        } else {
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot delete'),
            ];
        }
    }

    public function actionShippingOption($id = null)
    {
        if ($id === null) {
            $cart = Cart::getCart();
            if ($cart === null || $cart->items_count == 0 || !$cart->reCalc(true)) {
                $this->redirect(['/cart']);
                Yii::$app->end();
            }
            /** @var Order|HasProperties $order */
            $order = new Order;
            $order->attributes = [
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                'order_status_id' => 1,
                'hash' => md5(mt_rand() . uniqid()),
                'total_price' => $cart->total_price,
                'items_count' => $cart->items_count,
                'cart_forming_time' => new Expression(
                    "UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - UNIX_TIMESTAMP(:timestamp)",
                    [
                        ':timestamp' => $cart->create_time,
                    ]
                ),
            ];
            $order->getPropertyGroups(true, true);
        } else {
            $cart = null;
            $order = $this->loadOrder($id, [1, 2]);
            $order->scenario = 'shippingOption';
        }
        if (Yii::$app->request->isPost) {
            $order->load(Yii::$app->request->post());
            $order->abstractModel->setAttrubutesValues(Yii::$app->request->post());
            if ($order->validate() && $order->abstractModel->validate()) {
                $order->order_status_id = 2;
                $formNameWithoutId = $order->abstractModel->formName();
                $order->save();
                if ($cart !== null) {
                    foreach ($cart->items as $productId => $orderOptions) {
                        $orderItem = new OrderItem;
                        $orderItem->attributes = [
                            'order_id' => $order->id,
                            'product_id' => $productId,
                            'quantity' => $orderOptions['quantity'],
                            'additional_options' => $orderOptions['additionalParams'],
                        ];
                        $orderItem->save();
                    }
                    $cart->items = [];
                    $cart->reCalc();
                }
                if (Yii::$app->session->has('orders')) {
                    $orders = Yii::$app->session->get('orders');
                    $orders[] = $order->id;
                    Yii::$app->session->set('orders', $orders);
                } else {
                    Yii::$app->session->set('orders', [$order->id]);
                }
                $data = [
                    'AddPropetryGroup' => [
                        $order->formName() => array_keys($order->getPropertyGroups()),
                    ],
                    $formNameWithoutId . $order->id => Yii::$app->request->post($formNameWithoutId),
                ];
                $order->saveProperties($data);
                $this->redirect(['/cart/payment-type', 'id' => $order->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('shop', 'Please fill required form fields'));
            }
        }
        $shippingOptions = $this->getCachedData('CartShippingOptions', ShippingOption::className());
        return $this->render(
            'shipping-options',
            [
                'cart' => $cart,
                'order' => $order,
                'shippingOptions' => $shippingOptions,
            ]
        );
    }

    public function actionPaymentType($id)
    {
        $order = $this->loadOrder($id, 2);
        $paymentTypes = $this->getCachedData('CartPaymentTypes', PaymentType::className());

        if ($order->payment_type_id === 0 && count($paymentTypes) > 0) {
            $order->payment_type_id = reset($paymentTypes)->id;
        }
        if (Yii::$app->request->isPost) {
            $order->scenario = 'paymentType';
            $order->load(Yii::$app->request->post());
            $paymentType = PaymentType::find($order->payment_type_id);
            if ($order->validate() && $paymentType !== null) {
                $order->save();
                $this->redirect(['/cart/payment', 'id' => $order->id]);
            }
        }

        return $this->render(
            'payment-type',
            [
                'order' => $order,
                'paymentTypes' => $paymentTypes,
            ]
        );
    }

    public function actionPayment($id)
    {
        $order = $this->loadOrder($id, 2);
        $commissionType = null;
        switch ($commissionType) {
            // multiply
            case 1:
                $totalSum = $order->fullPrice * ($order->paymentType->commission + 100) / 100;
                break;
            // add fix
            case 2:
                $totalSum = $order->fullPrice + $order->paymentType->commission;
                break;
            // round down
            case 3:
                $totalSum = $order->fullPrice * (100 - $order->paymentType->commission) / 100;
                break;
            // without commission
            default:
                $totalSum = $order->fullPrice;
        }
        $transaction = new OrderTransaction;
        $transaction->attributes = [
            'order_id' => $id,
            'payment_type_id' => $order->payment_type_id,
            'status' => OrderTransaction::TRANSACTION_START,
            'total_sum' => $totalSum,
        ];
        $transaction->save();
        return $this->render(
            'payment',
            [
                'order' => $order,
                'payment' => $order->paymentType->payment,
                'transaction' => $transaction,
            ]
        );
    }

    public function actionPaymentResult()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        } elseif (isset($_GET['paymentId'])) {
            $id = $_GET['paymentId'];
        } else {
            throw new BadRequestHttpException;
        }
        $paymentType = PaymentType::findOne($id);
        if ($paymentType ===  null) {
            throw new NotFoundHttpException;
        }
        $paymentType->payment->checkResult();
    }

    public function actionPaymentSuccess()
    {
        $order = isset($_GET['id']) ? $this->loadOrder($_GET['id'], [2, 3]) : null;
        return $this->render(
            'payment-success',
            [
                'order' => $order,
            ]
        );
    }

    public function actionPaymentError()
    {
        $order = isset($_GET['id']) ? $this->loadOrder($_GET['id']) : $this->loadLastTransactionOrder([2]);
        return $this->render(
            'payment-error',
            [
                'order' => $order,
            ]
        );
    }
}
