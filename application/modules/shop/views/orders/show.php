<?php
/**
 * @var \app\modules\shop\models\Order $order
 * @var \yii\web\View $this
 */

use kartik\helpers\Html;

    $this->title = Yii::t('app', 'Order #{order}', ['order' => $order->id]);
    $this->params['breadcrumbs'] = [
        [
            'label' => Yii::t('app', 'Personal cabinet'),
            'url' => ['/shop/cabinet/']
        ],
        [
            'label' => Yii::t('app', 'Orders list'),
            'url' => ['/shop/orders/list']
        ],
        $this->title,
    ];

    $orderIsImmutable = Yii::$app->user->isGuest
        ? true
        : $order->getImmutability(\app\modules\shop\models\Order::IMMUTABLE_USER);
?>
    <h1><?= $this->title ?></h1>
    <h2><?= Yii::t('app', 'Order information') ?></h2>
    <table class="table table-bordered table-striped">
        <tbody>
        <tr>
            <th><?= $order->getAttributeLabel('start_date') ?></th>
            <td><?= Html::encode($order->start_date) ?></td>
        </tr>
        <tr>
            <th><?= Yii::t('app', 'Status') ?></th>
            <td>
                <?=
                $order->stage->name_frontend
                ?>
            </td>
        </tr>
        <tr>
            <th><?= Yii::t('app', 'Shipping method') ?></th>
            <td>
                <?php
                    $_raw = $order->shippingOption;
                    echo Html::encode(!empty($_raw) ? $_raw->name : Yii::t('yii', '(not set)'));
                ?>
            </td>
        </tr>
        <tr>
            <th><?= $order->getAttributeLabel('payment_type_id') ?></th>
            <td>
                <?php
                    $_raw = $order->paymentType;
                    echo Html::encode(!empty($_raw) ? $_raw->name : Yii::t('yii', '(not set)'));
                ?>
            </td>
        </tr>
        <?php foreach($order->abstractModel->attributes as $attribute => $value): ?>
            <tr>
                <th><?= $order->abstractModel->getAttributeLabel($attribute) ?></th>
                <td><?= empty($value) ? Yii::t('yii', '(not set)') : Html::encode($value) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2><?= Yii::t('app', 'Order items') ?></h2>
    <?=
        $this->render(
            '_items',
            [
                'model' => $order,
                'items' => $order->items,
            ]
        )
    ?>

    <?= \app\modules\shop\widgets\OrderTransaction::widget([
            'model' => $order,
            'immutable' => $orderIsImmutable,
        ]);
    ?>

    <?php
        $form = \yii\bootstrap\ActiveForm::begin([
            'id' => 'order-details-form',
            'action' => \yii\helpers\Url::to(['/shop/cabinet/update-order', 'hash' => $order->hash]),
            'layout' => 'horizontal',
        ]);

        echo \yii\helpers\Html::hiddenInput('orderId', $order->id);
    ?>
<div class="panel panel-default">
    <div class="panel-body">
    <?= \app\modules\shop\widgets\Customer::widget([
        'viewFile' => 'customer/inherit_form',
        'model' => $order->customer,
        'form' => $form,
        'immutable' => $orderIsImmutable,
    ]); ?>

    <?= \app\modules\shop\widgets\Contragent::widget([
        'viewFile' => 'contragent/inherit_form',
        'model' => $order->contragent,
        'form' => $form,
        'immutable' => $orderIsImmutable,
    ]); ?>

    <?= \app\modules\shop\widgets\Delivery::widget([
        'viewFile' => 'delivery/inherit_form',
        'deliveryInformation' => !empty($order->contragent) ? $order->contragent->deliveryInformation : null,
        'orderDeliveryInformation' => $order->orderDeliveryInformation,
        'form' => $form,
        'immutable' => $orderIsImmutable,
    ]); ?>
    </div>
    <?= $orderIsImmutable
        ? ''
        : \yii\helpers\Html::tag(
            'div',
            \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']),
            ['class' => 'panel-footer']
        );
    ?>
</div>
    <?php $form->end(); ?>
