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
use Yii;
use yii\base\Exception;
use yii\caching\TagDependency;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CartController extends Controller
{
    private function loadOrder($id, $allowedStatuses = null)
    {
        $model = Order::findOne($id);
        if (is_null($model)) {
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
        if (!is_null($allowedStatuses) && !in_array($model->order_status_id, (array) $allowedStatuses)) {
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
        if (is_null($allowedStatuses)) {
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
        if (!is_null($cart)) {
            $cart->reCalc(true);
        }
        return $this->render('index', ['cart' => $cart]);
    }

    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (is_null(Yii::$app->request->post('id'))) {
            throw new BadRequestHttpException;
        }
        $product = Product::findOne(Yii::$app->request->post('id'));
        if ($product === null) {
            throw new NotFoundHttpException;
        }
        $quantity = Yii::$app->request->post('quantity', 1);
        $cart = Cart::getCart(true);
        if (isset($cart->items[$product->id])) {
            $cart->items[$product->id] += $quantity;
        } else {
            $cart->items[$product->id] = $quantity;
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$cart->reCalc()) {
                throw new Exception('Cannot save cart');
            }
            $transaction->commit();
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
        } catch (Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot add product to cart'),
            ];
        }
    }

    public function actionChangeQuantity()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (is_null(Yii::$app->request->post('id')) || is_null(Yii::$app->request->post('quantity'))
            || (int) Yii::$app->request->post('quantity') < 1) {
            throw new BadRequestHttpException;
        }
        $id = Yii::$app->request->post('id');
        $product = Product::findOne($id);
        $cart = Cart::getCart(false);
        if (is_null($cart) || !isset($cart->items[$id]) || is_null($product)) {
            throw new NotFoundHttpException;
        }
        $cart->items[$id] = Yii::$app->request->post('quantity');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$cart->reCalc()) {
                throw new Exception;
            }
            $transaction->commit();
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'itemPrice' => Yii::$app->formatter->asDecimal($cart->items[$id] * $product->price, 2),
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
            ];
        } catch (Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot change quantity'),
            ];
        }
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cart = Cart::getCart(false);
        if (is_null($cart) || !isset($cart->items[$id])) {
            throw new NotFoundHttpException;
        }
        unset($cart->items[$id]);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$cart->reCalc()) {
                throw new Exception;
            }
            $transaction->commit();
            return [
                'success' => true,
                'itemsCount' => $cart->items_count,
                'totalPrice' => Yii::$app->formatter->asDecimal($cart->total_price, 2),
            ];
        } catch (Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => Yii::t('shop', 'Cannot delete'),
            ];
        }
    }

    public function actionShippingOption($id = null)
    {
        if (is_null($id)) {
            $cart = Cart::getCart();
            if (is_null($cart) || $cart->items_count == 0 || !$cart->reCalc(true)) {
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
                if (!is_null($cart)) {
                    foreach ($cart->items as $productId => $quantity) {
                        $orderItem = new OrderItem;
                        $orderItem->attributes = [
                            'order_id' => $order->id,
                            'product_id' => $productId,
                            'quantity' => $quantity,
                        ];
                        $orderItem->save();
                    }
                    $cart->items = [];
                    $cart->reCalc();
                }
                if (Yii::$app->session->has('orders')) {
                    Yii::$app->session->set(
                        'orders',
                        ArrayHelper::merge(
                            Yii::$app->session->get('orders'),
                            [$order->id]
                        )
                    );
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
        $shippingOptions = Yii::$app->cache->get('CartShippingOptions');
        if ($shippingOptions === false) {
            $shippingOptions = ShippingOption::findAll(['active' => 1]);
            Yii::$app->cache->set(
                'CartShippingOptions',
                $shippingOptions,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            \app\behaviors\TagDependency::getCommonTag(ShippingOption::className()),
                        ],
                    ]
                )
            );
        }
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
        if (Yii::$app->request->isPost) {
            $order->scenario = 'paymentType';
            $order->load(Yii::$app->request->post());
            $paymentType = PaymentType::find($order->payment_type_id);
            if ($order->validate() && !is_null($paymentType)) {
                $order->save();
                $this->redirect(['/cart/payment', 'id' => $order->id]);
            }
        }
        $paymentTypes = Yii::$app->cache->get('CartPaymentTypes');
        if ($paymentTypes === false) {
            $paymentTypes = PaymentType::findAll(['active' => 1]);
            Yii::$app->cache->set(
                'CartPaymentTypes',
                $paymentTypes,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            \app\behaviors\TagDependency::getCommonTag(PaymentType::className()),
                        ],
                    ]
                )
            );
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
        if (is_null($paymentType)) {
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
