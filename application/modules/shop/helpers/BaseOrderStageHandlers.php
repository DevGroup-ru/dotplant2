<?php

namespace app\modules\shop\helpers;

use app\modules\shop\events\OrderStageEvent;
use app\modules\shop\events\OrderStageLeafEvent;
use app\modules\shop\models\Contragent;
use app\modules\shop\models\Customer;
use app\modules\shop\models\DeliveryInformation;
use app\modules\shop\models\Order;
use app\modules\shop\models\OrderDeliveryInformation;

class BaseOrderStageHandlers
{
    /**
     * @param $event OrderStageLeafEvent
     */
    public static function handleCustomer(OrderStageLeafEvent $event)
    {
        $event->setStatus(false);

        if (\Yii::$app->request->isPost) {
            $user_id = \Yii::$app->user->isGuest ? 0 : \Yii::$app->user->id;
            $customer = Customer::getCustomerByUserId($user_id);
            if (empty($customer)) {
                $contragent = Contragent::createEmptyContragent();
                    $contragent->type = 'Individual';
                $contragent->save();
                $contragent->refresh();

                $customer = Customer::createEmptyCustomer($user_id, $contragent->id);
            }
            if ($customer->load(\Yii::$app->request->post())) {
                $data = \Yii::$app->request->post();
                $isNewCustomer = $customer->isNewRecord;
                if ($customer->save()) {
                    if ($isNewCustomer) {
                        $customer->addPropertyGroup($customer->getPropertyGroup()->id);
                        $data[$customer->getAbstractModel()->formName()] = isset($data['CustomerNew']) ? $data['CustomerNew'] : [];
                    }
                    $customer->saveProperties($data);
                    $customer->invalidateTags();

                    $order = Order::getOrder();
                        $order->customer_id = $customer->id;
                        $order->contragent_id = $customer->contragent_id;
                    if ($order->save()) {
                        $event->setStatus(true);
                    }
                }
            }
        }
    }

    /**
     * @param $event OrderStageLeafEvent
     */
    public static function handlePayment(OrderStageLeafEvent $event)
    {
        $event->setStatus(true);
    }

    /**
     * @param $event OrderStageLeafEvent
     */
    public static function handleDelivery(OrderStageLeafEvent $event)
    {
        $event->setStatus(false);

        if (\Yii::$app->request->isPost) {
            $order = Order::getOrder();
            if (empty($order)) {
                return null;
            }
            /** @var DeliveryInformation $deliveryInformation */
            $deliveryInformation = empty($order->contragent) ? null :
                ( empty($order->contragent->deliveryInformation) ? DeliveryInformation::createNewDeliveryInformation($order->contragent) : $order->contragent->deliveryInformation );
            /** @var OrderDeliveryInformation $orderDeliveryInformation */
            $orderDeliveryInformation = empty($order->orderDeliveryInformation) ? OrderDeliveryInformation::createNewOrderDeliveryInformation($order) : $order->orderDeliveryInformation;
            if (empty($deliveryInformation) || empty($orderDeliveryInformation)) {
                return null;
            }

            if ($deliveryInformation->load(\Yii::$app->request->post())) {
                if ($deliveryInformation->save()) {
                    $data = \Yii::$app->request->post();
                    $isNewOrderDeliveryInformation = $orderDeliveryInformation->isNewRecord;
                    $orderDeliveryInformation->load(\Yii::$app->request->post());
                    if ($orderDeliveryInformation->save()) {
                        if ($isNewOrderDeliveryInformation) {
                            $orderDeliveryInformation->addPropertyGroup($orderDeliveryInformation->getPropertyGroup()->id);
                            $data[$orderDeliveryInformation->getAbstractModel()->formName()] = isset($data['OrderDeliveryInformationNew']) ? $data['OrderDeliveryInformationNew'] : [];
                        }
                        $orderDeliveryInformation->saveProperties($data);
                        $orderDeliveryInformation->invalidateTags();

                        $event->setStatus(true);
                    }
                }
            }
        }
    }

    public static function handleStageCustomer(OrderStageEvent $event)
    {
        $order = Order::getOrder();
        /** @var Customer $customer */
        $customer = !empty($order->customer) ? $order->customer :
            (!empty(Customer::getCustomerByUserId($order->user_id)) ? Customer::getCustomerByUserId($order->user_id) : Customer::createEmptyCustomer($order->user_id, 0));

        /** @var Contragent[] $contragents */
        $contragents = $customer->contragents;

        $event->setEventData([
            'user_id' => $order->user_id,
            'customer' => $customer,
            'contragents' => $contragents,
        ]);
    }

    public static function handleStagePayment(OrderStageEvent $event)
    {
    }

    public static function handleStageDelivery(OrderStageEvent $event)
    {
        /** @var Order $order */
        $order = Order::getOrder();
        /** @var Contragent $contragent */
        $contragent = $order->contragent;

        /** @var DeliveryInformation $deliveryInformation */
        $deliveryInformation = !empty($contragent->deliveryInformation) ? $contragent->deliveryInformation : DeliveryInformation::createNewDeliveryInformation($contragent);

        /** @var OrderDeliveryInformation $orderDeliveryInformation */
        $orderDeliveryInformation = !empty($order->orderDeliveryInformation) ? $order->orderDeliveryInformation : OrderDeliveryInformation::createNewOrderDeliveryInformation($order);

        $event->setEventData([
            'deliveryInformation' => $deliveryInformation,
            'orderDeliveryInformation' => $orderDeliveryInformation,
        ]);
    }
}
?>