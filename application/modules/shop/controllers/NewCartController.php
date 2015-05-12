<?php

namespace app\modules\shop\controllers;

use app\modules\shop\models\Order;
use app\modules\shop\models\OrderItem;
use app\modules\shop\models\Product;
use app\modules\shop\ShopModule;
use kartik\helpers\Html;
use yii\helpers\VarDumper;
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
     * @return Order
     * @throws NotFoundHttpException
     */
    protected function loadOrder($create = false)
    {
        $model = Order::getOrder($create);
        if (is_null($model)) {
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
        // @todo Throw exception if order stage isn't a forming.
        $id = Yii::$app->request->post('id');
        $quantity = Yii::$app->request->post('quantity');
        if (is_null($id) || is_null($quantity) || (double) Yii::$app->request->post('quantity') <= 0) {
            throw new BadRequestHttpException;
        }
        $orderItem = $this->loadOrderItem($id);
        $orderItem->quantity = $quantity;
        // @todo Consider lock_product_price ?
        if ($orderItem->lock_product_price == 0) {
            $orderItem->price_per_pcs = $orderItem->product->price;
        }
        $totalPriceWithoutDiscount = $orderItem->price_per_pcs * $orderItem->quantity;
        $orderItem->total_price_without_discount = $totalPriceWithoutDiscount;
        $orderItem->total_price = $totalPriceWithoutDiscount; // @todo Need to implement discount. It has been calculated without discount now
        $orderItem->save();
        $this->loadOrder()->calculate(true);
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
}
