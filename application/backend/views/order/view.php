<?php

use app\backend\widgets\BackendWidget;
use kartik\editable\Editable;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $managers array
 * @var $model app\models\Order
 */

$this->title = Yii::t('shop', 'Order #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="page-title txt-color-blueDark">
    <?= Html::encode($this->title) ?>
    <a href="#" class="btn btn-default pull-right" id="print-button"><em class="glyphicon glyphicon-print"></em>&nbsp;&nbsp;<?= Yii::t('shop', 'Print') ?></a>
</h1>
<div class="row">
    <div class="col-xs-8">
        <div class="order-view">
            <?php
                BackendWidget::begin(
                    [
                        'icon' => 'info-circle',
                        'title'=> Yii::t('shop', 'Order information'),
                    ]
                );
            ?>
                <table class="table table-striped table-bordered">
                    <tbody>
                    <tr>
                        <th><?= $model->getAttributeLabel('user') ?></th>
                        <td>
                            <?=
                                !is_null($model->user)
                                    ? $model->user->username
                                    : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('manager') ?></th>
                        <td>
                            <?=
                                Editable::widget(
                                    [
                                        'attribute' => 'manager_id',
                                        'data' => $managers,
                                        'displayValue' => !is_null($model->manager)
                                            ? $model->manager->username
                                            : '<em>' . Yii::t('yii', '(not set)') . '</em>',
                                        'formOptions' => [
                                            'action' => ['/backend/order/change-manager', 'id' => $model->id],
                                        ],
                                        'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                        'model' => $model,
                                    ]
                                )
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('start_date') ?></th>
                        <td><?= $model->start_date ?></td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('end_date') ?></th>
                        <td><?= $model->end_date ?></td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('order_status_id') ?></th>
                        <td>
                            <?=
                            Editable::widget(
                                [
                                    'attribute' => 'order_status_id',
                                    'data' => \app\components\Helper::getModelMap(
                                        \app\models\OrderStatus::className(),
                                        'id',
                                        'short_title'
                                    ),
                                    'displayValue' => $model->status !== null
                                        ? Html::tag(
                                            'span',
                                            $model->status->short_title,
                                            ['class' => $model->status->label]
                                        )
                                        : 'Not set',
                                    'formOptions' => [
                                        'action' => ['/backend/order/update-status', 'id' => $model->id],
                                    ],
                                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                    'model' => $model,
                                ]
                            )
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('shipping_option_id') ?></th>
                        <td>
                            <?=
                            $model->shippingOption !== null
                                ? $model->shippingOption->name
                                : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('payment_type_id') ?></th>
                        <td>
                            <?=
                            $model->paymentType !== null
                                ? $model->paymentType->name
                                : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                            ?>
                        </td>
                    </tr>
                    <?php foreach($model->abstractModel->attributes as $name => $attribute): ?>
                        <tr>
                            <th><?= $model->abstractModel->getAttributeLabel($name) ?></th>
                            <td>
                                <?=
                                !empty($attribute)
                                    ? Html::encode($attribute)
                                    : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php BackendWidget::end(); ?>
            <?php
                BackendWidget::begin(
                    [
                        'icon' => 'list-alt',
                        'title'=> Yii::t('shop', 'Order items'),
                    ]
                );
            ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?= Yii::t('shop', 'Name') ?></th>
                            <th><?= Yii::t('shop', 'Price') ?></th>
                            <th><?= Yii::t('shop', 'Quantity') ?></th>
                            <th><?= Yii::t('shop', 'Price sum') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($model->items  as $item): ?>
                            <tr>
                                <td><?= $item->product->name ?></td>
                                <td><?= Yii::$app->formatter->asDecimal($item->product->price, 2) ?></td>
                                <td><?= $item->quantity ?></td>
                                <td><?= Yii::$app->formatter->asDecimal($item->quantity * $item->product->price, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (isset($model->shippingOption)): ?>
                            <tr>
                                <td colspan="3"><?= Html::encode($model->shippingOption->name) ?></td>
                                <td><?= Yii::$app->formatter->asDecimal($model->shippingOption->cost, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th colspan="2"><?= Yii::t('shop', 'Summary') ?></th>
                            <th><?= $model->items_count ?></th>
                            <th><?= Yii::$app->formatter->asDecimal($model->total_price, 2) ?></th>
                        </tr>
                    </tbody>
                </table>
            <?php BackendWidget::end(); ?>
        </div>
    </div>
    <div class="col-xs-4 order-chat">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'comments',
                'title'=> Yii::t('shop', 'Managers chat'),
            ]
        );
        ?>
            <div class="widget-body widget-hide-overflow no-padding">
                <div id="chat-body" class="chat-body custom-scroll">
                    <ul>
                        <?php foreach ($lastMessages as $msg): ?>
                            <li class="message">
                                <?php if (!is_null($msg->user)): ?>
                                    <img src="<?= $msg->user->gravatar() ?>" class="online" alt="">
                                <?php endif; ?>
                                <div class="message-text">
                                    <time>
                                        <?= $msg->date ?>
                                    </time>
                                    <a href="javascript:void(0);" class="username"><?=
                                        !is_null($msg->user)
                                            ? $msg->user->username
                                            : Yii::t('shop', 'Unknown')
                                    ?></a>
                                    <?= nl2br(Html::encode($msg->message)) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="chat-footer">
                    <?php
                        $form = \kartik\widgets\ActiveForm::begin(
                            [
                                'action' => ['/backend/order/view', 'id' => $model->id],
                                'method' => 'post',
                            ]
                        );
                    ?>
                        <div class="textarea-div">
                            <div class="typearea">
                                <?= $form->field($message, 'message')->textarea(['class' => 'custom-scroll']) ?>
                            </div>
                        </div>
                        <span class="textarea-controls">
                            <?= Html::submitButton(Yii::t('shop', 'Submit'), ['class' => 'btn btn-sm btn-primary pull-right']) ?>
                        </span>
                    <?php \kartik\widgets\ActiveForm::end(); ?>
                </div>
            <?php BackendWidget::end(); ?>
        </div>
    </div>
</div>
<script>
jQuery('#orderchat-message').keypress(function(event) {
    if (event.keyCode == 10) {
        jQuery(this).parents('form').eq(0).submit();
    }
});
jQuery('#print-button').click(function() {
    window.print();
    return false;
});
</script>