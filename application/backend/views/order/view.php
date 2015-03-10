<?php

use app\backend\widgets\BackendWidget;
use kartik\editable\Editable;
use yii\helpers\Html;
use kartik\dynagrid\DynaGrid;

/**
 * @var $this yii\web\View
 * @var $managers array
 * @var $model app\models\Order
 * @var $transactionsDataProvider \yii\data\ArrayDataProvider
 */

$this->title = Yii::t('shop', 'Order #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1 class="page-title txt-color-blueDark">
    <?= Html::encode($this->title) ?>
    <a href="#" class="btn btn-default pull-right do-not-print" id="print-button"><?= \kartik\icons\Icon::show('print') ?>&nbsp;&nbsp;<?= Yii::t('shop', 'Print') ?></a>
    <a href="<?= Yii::$app->request->get('returnUrl', ['/backend/order/index']) ?>" class="btn btn-danger pull-right do-not-print"><?= \kartik\icons\Icon::show('arrow-circle-left') ?>&nbsp;&nbsp;<?= Yii::t('app', 'Back') ?></a>
</h1>
<?php
$sum_transactions = 0;
foreach ($model->transactions as $transaction) {
    $sum_transactions += $transaction->total_sum;
}
if ($sum_transactions < $model->total_price):
    ?>
    <div class="alert alert-danger">
        <b><?= Yii::t('app', 'Warning!') ?></b>
        <?=
        Yii::t(
            'app',
            'Total sum of transactions is {sum} which is lower then order\'s total price {order}',
            [
                'sum' => $sum_transactions,
                'order' => $model->total_price,
            ]
        );
        ?>
    </div>
<?php endif;?>
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
                                        'action' => ['update-status', 'id' => $model->id],
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
                            Editable::widget(
                                [
                                    'attribute' => 'shipping_option_id',
                                    'data' => \app\components\Helper::getModelMap(
                                        \app\models\ShippingOption::className(),
                                        'id',
                                        'name'
                                    ),
                                    'displayValue' => !is_null($model->shippingOption)
                                        ? Html::tag(
                                            'span',
                                            $model->shippingOption->name
                                        )
                                        : 'Not set',
                                    'formOptions' => [
                                        'action' => ['update-shipping-option', 'id' => $model->id],
                                    ],
                                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                    'model' => $model,
                                ]
                            )
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= $model->getAttributeLabel('payment_type_id') ?></th>
                        <td>
                            <?=
                            !is_null($model->paymentType)
                                ? $model->paymentType->name
                                : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                            ?>
                        </td>
                    </tr>
                    <?php foreach($model->abstractModel->attributes as $name => $attribute): ?>
                        <tr>
                            <th><?= $model->abstractModel->getAttributeLabel($name) ?></th>
                            <td>
                                <button data-toggle="modal" data-target="#custom-fields-modal" class="kv-editable-value kv-editable-link">
                                <?=
                                !empty($attribute)
                                    ? Html::encode($attribute)
                                    : '<em>' . Yii::t('yii', '(not set)') . '</em>'
                                ?>
                                </button>
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
                            <th style="width: 43px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($model->items  as $item): ?>
                            <tr>
                                <td><?= $item->product->name ?></td>
                                <td><?= $item->product->convertedPrice() ?></td>
                                <td>
                                    <?=
                                    Editable::widget(
                                        [
                                            'attribute' => 'quantity',
                                            'options' => [
                                                'id' => 'edit-quantity' . $item->id,
                                            ],
                                            'formOptions' => [
                                                'action' => [
                                                    '/backend/order/change-order-item-quantity',
                                                    'id' => $item->id,
                                                ],
                                            ],
                                            'inputType' => Editable::INPUT_TEXT,
                                            'model' => $item,
                                        ]
                                    )
                                    ?>
                                </td>
                                <td><?= Yii::$app->formatter->asDecimal($item->quantity * $item->product->convertedPrice(), 2) ?></td>
                                <td><?= Html::a(\kartik\icons\Icon::show('remove'), ['delete-order-item', 'id' => $item->id], ['class' => 'btn btn-primary btn-xs do-not-print']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (isset($model->shippingOption)): ?>
                            <tr>
                                <td colspan="3"><?= Html::encode($model->shippingOption->name) ?></td>
                                <td colspan="2"><?= Yii::$app->formatter->asDecimal($model->shippingOption->cost, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th colspan="2"><?= Yii::t('shop', 'Summary') ?></th>
                            <th><?= $model->items_count ?></th>
                            <th colspan="2"><?= Yii::$app->formatter->asDecimal($model->total_price, 2) ?></th>
                        </tr>
                    </tbody>
                </table>
                <div class="do-not-print">
                    <br />
                    <div class="row">
                        <div class="col-xs-3">
                            <label for="add-product"><?= Yii::t('shop', 'Add a new product to order') ?></label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3">
                            <?=
                            \app\widgets\AutoCompleteSearch::widget(
                                [
                                    'options' => [
                                        'class' => 'form-control',
                                    ],
                                    'id' => 'add-product',
                                    'name' => 'add-product',
                                    'route' => ['auto-complete-search', 'orderId' => $model->id],
                                ]
                            )
                            ?>
                        </div>
                    </div>
                </div>
            <?php BackendWidget::end(); ?>

            <?php
            BackendWidget::begin(
                [
                    'icon' => 'dollar',
                    'title'=> Yii::t('shop', 'Order transactions'),
                ]
            );
            ?>

            <?=
            DynaGrid::widget(
                [
                    'options' => [
                        'id' => 'transactions-grid',
                    ],
                    'theme' => 'panel-default',
                    'gridOptions' => [
                        'dataProvider' => $transactionsDataProvider,
                        'hover' => true,
                        'panel' => false
                    ],
                    'columns' => [
                        'id',
                        'start_date',
                        'end_date',
                        'total_sum',
                        [
                            'attribute' => 'payment_type_id',
                            'filter' => \app\components\Helper::getModelMap(\app\models\PaymentType::className(), 'id', 'name'),
                            'value' => function ($model, $key, $index, $column) {
                                if ($model === null || $model->paymentType === null) {
                                    return null;
                                }
                                return $model->paymentType->name;
                            },
                        ],

                    ],
                ]
            );
            ?>


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
                            <?=
                            Html::submitButton(
                                Yii::t('shop', 'Submit'),
                                ['class' => 'btn btn-sm btn-primary pull-right']
                            )
                            ?>
                        </span>
                    <?php \kartik\widgets\ActiveForm::end(); ?>
                </div>
            <?php BackendWidget::end(); ?>
        </div>
    </div>
</div>

<?php \yii\bootstrap\Modal::begin(['id' => 'custom-fields-modal', 'header' => Yii::t('shop', 'Edit order properties')]) ?>
<?php $form = \kartik\widgets\ActiveForm::begin(['action' => ['update-order-properties', 'id' => $model->id]])?>
<?php foreach (\app\models\PropertyGroup::getForModel($model->object->id, $model->id) as $group): ?>
    <?php if ($group->hidden_group_title == 0): ?>
        <h4><?= $group->name; ?></h4>
    <?php endif; ?>
    <?php $properties = \app\models\Property::getForGroupId($group->id); ?>
    <?php foreach ($properties as $property): ?>
        <?= $property->handler($form, $model->abstractModel, [], 'frontend_edit_view'); ?>
    <?php endforeach; ?>
<?php endforeach; ?>
<?= Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-primary']) ?>
<?php \kartik\widgets\ActiveForm::end() ?>
<?php \yii\bootstrap\Modal::end() ?>

<script>
jQuery('#orderchat-message').keypress(function(event) {
    if (event.keyCode == 10) {
        jQuery(this).parents('form').eq(0).submit();
    }
});
jQuery('body').on('editableSuccess', function() {
    location.reload();
});
jQuery('#print-button').click(function() {
    window.print();
    return false;
});
</script>