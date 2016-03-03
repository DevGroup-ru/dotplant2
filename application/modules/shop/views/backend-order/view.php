<?php

use app\backend\widgets\BackendWidget;
use kartik\editable\Editable;
use yii\helpers\Html;
use \app\modules\shop\helpers\PriceHelper;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $managers array
 * @var $model \app\modules\shop\models\Order
 * @var $transactionsDataProvider \yii\data\ArrayDataProvider
 * @var \app\backend\models\OrderChat $message
 * @var boolean $orderIsImmutable
 */

$this->title = Yii::t('app', 'Order #{id}', ['id' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$items = [];
foreach ($model->items as $item) {
    if (isset($items[$item->parent_id])) {
        $items[$item->parent_id][] = $item;
    } else {
        $items[$item->parent_id] = [$item];
    }
}
?>
<?php $this->beginBlock('page-buttons'); ?>
<div class="row" style="margin-bottom: 10px;">
    <div class="col-xs-12 col-md-6">
        <div class="btn-group pull-right">
            <a href="#" class="btn btn-default do-not-print" id="print-button"><?=\kartik\icons\Icon::show(
                    'print'
                )?>&nbsp;&nbsp;<?=Yii::t('app', 'Print')?></a>
            <a href="<?=Yii::$app->request->get(
                'returnUrl',
                \yii\helpers\Url::toRoute(['index'])
            )?>" class="btn btn-danger do-not-print"><?=\kartik\icons\Icon::show(
                    'arrow-circle-left'
                )?>&nbsp;&nbsp;<?=Yii::t('app', 'Back')?></a>
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']); ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('submit'); ?>
<?=Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-primary'])?>
<?php $this->endBlock(); ?>
<h1 class="page-title txt-color-blueDark">
    <?=Html::encode($this->title)?>
</h1>
<?php
$sum_transactions = 0;
foreach ($model->transactions as $transaction) {
    $sum_transactions += $transaction->total_sum;
}
if ($sum_transactions < $model->total_price):
    ?>
    <div class="alert alert-danger">
        <b><?=Yii::t('app', 'Warning!')?></b>
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
<?php endif; ?>
<?php
    $form = \kartik\widgets\ActiveForm::begin(
        [
            'action' => ['', 'id' => $model->id],
            'method' => 'post',
            'type' => \kartik\form\ActiveForm::TYPE_HORIZONTAL,
            'options' => [
                'class' => 'form-order-backend',
            ],
        ]
    );
    echo $this->blocks['page-buttons'];
?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'info-circle',
                'title' => Yii::t('app', 'Order information'),
            ]
        );
        ?>
        <table class="table table-striped table-bordered">
            <tbody>
            <tr>
                <td colspan="2">
                    <?php
                    $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2customer-result">' +
        '<strong>' + (data.username || '') + '</strong>' +
        '<div>' + (data.first_name || '') + ' ' + (data.last_name || '') + ' (' + (data.email || '') + ')</div>' +
        '</div>';
    return tpl;
}
JSCODE;
                    echo \app\backend\widgets\Select2Ajax::widget([
                        'form' => $form,
                        'model' => $model,
                        'modelAttribute' => 'user_id',
                        'initialData' => array_replace([0 => Yii::t('app', 'Guest')],
                            [$model->user_id => null !== $model->user ? $model->user->username : Yii::t('app', 'Guest')]),
                        'multiple' => false,
                        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-user']),
                        'pluginOptions' => [
                            'allowClear' => false,
                            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
                            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
                            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.username || data.text;}'),
                        ],
                    ]);
                    echo Html::tag('div',
                        Html::a(
                            Yii::t('app', Yii::t('app', 'Guest')),
                            '#clear',
                            ['data-sel' => 'order-user_id', 'class' => 'btn btn-xs btn-info col-md-offset-2 do-not-print']
                        )
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th><?=$model->getAttributeLabel('manager')?></th>
                <td>
                    <?=
                    $orderIsImmutable
                        ? (null !== $model->manager ? $model->manager->username : Html::tag('em', Yii::t('yii', '(not set)')))
                        : Editable::widget(
                            [
                                'attribute' => 'manager_id',
                                'data' => $managers,
                                'displayValue' => !is_null(
                                    $model->manager
                                ) ? $model->manager->username : Html::tag('em', Yii::t('yii', '(not set)')),
                                'formOptions' => [
                                    'action' => ['change-manager', 'id' => $model->id],
                                ],
                                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                'model' => $model,
                            ]
                        );
                    ?>
                </td>
            </tr>
            <tr>
                <th><?=$model->getAttributeLabel('start_date')?></th>
                <td><?=$model->start_date?></td>
            </tr>
            <tr>
                <th><?=$model->getAttributeLabel('end_date')?></th>
                <td><?=$model->end_date?></td>
            </tr>
            <tr>
                <th><?=$model->getAttributeLabel('order_stage_id')?></th>
                <td>
                    <?=
                    $orderIsImmutable
                        ? Html::encode($model->stage->name_short)
                        : Editable::widget(
                            [
                                'attribute' => 'order_stage_id',
                                'data' => \app\components\Helper::getModelMap(
                                    \app\modules\shop\models\OrderStage::className(),
                                    'id',
                                    'name_short'
                                ),
                                'displayValue' => $model->stage !== null ? Html::tag(
                                    'span',
                                    $model->stage->name_short
                                ) : Html::tag('em', Yii::t('yii', '(not set)')),
                                'formOptions' => [
                                    'action' => ['update-stage', 'id' => $model->id],
                                ],
                                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                'model' => $model,
                            ]
                        );
                    ?>
                </td>
            </tr>
            <tr>
                <th><?=$model->getAttributeLabel('payment_type_id')?></th>
                <td>
                    <?=
                    $orderIsImmutable
                        ? (null !== $model->paymentType ? Html::encode($model->paymentType->name) : Html::tag('em', Yii::t('yii', '(not set)')))
                        : Editable::widget(
                            [
                                'attribute' => 'payment_type_id',
                                'data' => \app\components\Helper::getModelMap(
                                    \app\modules\shop\models\PaymentType::className(),
                                    'id',
                                    'name'
                                ),
                                'displayValue' => $model->paymentType !== null ? Html::tag(
                                    'span',
                                    $model->paymentType->name
                                ) : Html::tag('em', Yii::t('yii', '(not set)')),
                                'formOptions' => [
                                    'action' => ['update-payment-type', 'id' => $model->id],
                                ],
                                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                                'model' => $model,
                            ]
                        );
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <?php $model->getPropertyGroups(true,false,true);?>
                    <?php $properties = $model->abstractModel->getPropertiesModels(); ?>
                    <?php foreach ($properties as $property): ?>
                        <?php
                        $property_values = $model->getPropertyValuesByPropertyId($property->id);
                        echo $property->handler($form, $model->abstractModel, $property_values, 'backend_render_view');
                        ?>
                    <?php endforeach; ?>
                </td>
            </tr>


            </tbody>
        </table>
        <?php BackendWidget::end(); ?>

        <?php
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title' => Yii::t('app', 'Customer'),
                ]
            );
        ?>
        <?php
        $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2customer-result">' +
        '<strong>' + (data.username || '') + '</strong>' +
        '<div>' + (data.first_name || '') + ' (' + (data.email || '') + ')</div>' +
        '</div>';
    return tpl;
}
JSCODE;
        $_jsDataFunc = <<< 'JSCODE'
function (term, page) {
    return {
        search: { term: term.term, user: $('select#order-user_id').val() }
    };
}
JSCODE;
        $_jsSelectionFunc = <<< 'JSCODE'
function (data) {
    if (data.card) {
        $('div#div_customer').html(data.card);
    }
    if (data.id) {
        $('a.edit_customer').attr('href', '/shop/backend-customer/edit?id=' + data.id);
    }
    return data.first_name || data.text;
}
JSCODE;
        $_jsProcessResultsFunc = <<< 'JSCODE'
function (data, page) {
    return {results: $.map(data.results, function(e, i) {
        if (data.cards[e.id]) {
            e.card = data.cards[e.id];
        }
        return e;
    })};
}
JSCODE;
        echo \app\backend\widgets\Select2Ajax::widget([
            'initialData' => [$model->customer_id => null !== $model->customer ? $model->customer->first_name : 'New customer'],
            'model' => $model,
            'modelAttribute' => 'customer_id',
            'form' => $form,
            'multiple' => false,
            'searchUrl' => \yii\helpers\Url::toRoute(['ajax-customer', 'template' => 'simple']),
            'pluginOptions' => [
                'allowClear' => false,
                'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
                'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
                'templateSelection' => new \yii\web\JsExpression($_jsSelectionFunc),
                'ajax' => [
                    'data' => new \yii\web\JsExpression($_jsDataFunc),
                    'processResults' => new \yii\web\JsExpression($_jsProcessResultsFunc),
                ]
            ]
        ]);
        ?>
        <?= Html::tag('div',
            Html::a(
                Yii::t('app', 'Create customer'),
                Url::toRoute(['/shop/backend-customer/create']),
                ['target' => '_blank', 'class' => 'btn btn-xs btn-info new_customer']
            ) .
            Html::a(
                Yii::t('app', 'Edit customer'),
                Url::toRoute(['/shop/backend-customer/index']),
                ['target' => '_blank', 'class' => 'btn btn-xs btn-info edit_customer']
            ),
            ['class' => 'btn-group col-md-offset-2 do-not-print']
        ); ?>
        <hr />
        <div id="div_customer">
        <?= \app\modules\shop\widgets\Customer::widget([
            'viewFile' => 'customer/backend_list',
            'model' => $model->customer,
        ]); ?>
        </div>
        <?php BackendWidget::end(); ?>

        <?php
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title' => Yii::t('app', 'Contragent'),
                ]
            );
        ?>
        <?php
        $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2contragent-result">' +
        '<strong>' + (data.type || '') + '</strong>' +
        '</div>';
    return tpl;
}
JSCODE;
        $_jsSelectionFunc = <<< 'JSCODE'
function (data) {
    if (data.card) {
        $('div#div_contragent').html(data.card);
    }
    if (data.id) {
        $('a.edit_contragent').attr('href', '/shop/backend-contragent/edit?id=' + data.id);
    }
    return data.type || data.text;
}
JSCODE;
        $_jsDataFunc = <<< 'JSCODE'
function (term, page) {
    return {
        search: {customer:$('select#order-customer_id').val()}
    };
}
JSCODE;
        echo \app\backend\widgets\Select2Ajax::widget([
            'initialData' => [$model->contragent_id => null !== $model->contragent ? $model->contragent->type : 'New contragent'],
            'model' => $model,
            'modelAttribute' => 'contragent_id',
            'form' => $form,
            'multiple' => false,
            'searchUrl' => \yii\helpers\Url::toRoute(['ajax-contragent', 'template' => 'simple']),
            'pluginOptions' => [
                'minimumInputLength' => null,
                'allowClear' => false,
                'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
                'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
                'templateSelection' => new \yii\web\JsExpression($_jsSelectionFunc),
                'ajax' => [
                    'data' => new \yii\web\JsExpression($_jsDataFunc),
                    'processResults' => new \yii\web\JsExpression($_jsProcessResultsFunc),
                    'cache' => true,
                ]
            ]
        ]);
        ?>
        <?= Html::tag('div',
            Html::a(
                Yii::t('app', 'Create contragent'),
                Url::toRoute(['/shop/backend-contragent/create']),
                ['target' => '_blank', 'class' => 'btn btn-xs btn-info new_contragent']
            ) .
            Html::a(
                Yii::t('app', 'Edit contragent'),
                Url::toRoute(['/shop/backend-contragent/index']),
                ['target' => '_blank', 'class' => 'btn btn-xs btn-info edit_contragent']
            ),
            ['class' => 'btn-group col-md-offset-2 do-not-print']
        ); ?>
        <hr />
        <div id="div_contragent">
        <?= \app\modules\shop\widgets\Contragent::widget([
            'viewFile' => 'contragent/backend_list',
            'model' => $model->contragent,
        ]); ?>
        </div>
        <?php BackendWidget::end(); ?>

        <?php
            BackendWidget::begin(
                [
                    'icon' => 'user',
                    'title' => Yii::t('app', 'Order delivery information'),
                ]
            );
        ?>
        <?= \app\modules\shop\widgets\Delivery::widget([
            'viewFile' => 'delivery/backend_form',
            'orderDeliveryInformation' => $model->orderDeliveryInformation,
            'form' => $form,
            'immutable' => $orderIsImmutable,
        ]); ?>
        <?php BackendWidget::end(); ?>
    </div>
    <div class="col-xs-12 col-md-6 order-chat">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'comments',
                'options' => ['class' => 'do-not-print'],
                'title' => Yii::t('app', 'Managers chat'),
            ]
        );
        ?>
        <?= \app\modules\shop\widgets\OrderChat::widget([
            'list' => $lastMessages,
            'order' => $model,
        ]); ?>
        <?php BackendWidget::end(); ?>

        <div class="order-view">
            <?php
            BackendWidget::begin(
                [
                    'icon' => 'list-alt',
                    'title' => Yii::t('app', 'Order items'),
                ]
            );
            ?>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th><?=Yii::t('app', 'Name')?></th>
                    <th><?=Yii::t('app', 'Price')?></th>
                    <th><?=Yii::t('app', 'Quantity')?></th>
                    <th><?=Yii::t('app', 'Price sum')?></th>
                    <th style="width: 43px;"></th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($items[0])): ?>
                    <?= $this->render('items', ['allItems' => $items, 'items' => $items[0]]) ?>
                <?php endif; ?>
                <?php if ($model->specialPriceObjects): ?>
                    <?php foreach($model->specialPriceObjects as $specialPriceObject): ?>
                        <tr>
                            <td colspan="3"><?=Html::encode($specialPriceObject->name)?></td>
                            <td colspan="2"><?=Yii::$app->formatter->asDecimal($specialPriceObject->price, 2)?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif?>
                <tr>
                    <th colspan="2"><?=Yii::t('app', 'Summary')?></th>
                    <th><?=$model->items_count?></th>
                    <th colspan="2"><?=$model->total_price?></th>
                </tr>
                </tbody>
            </table>
            <div class="do-not-print">
                <br />

                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <label for="add-product"><?=Yii::t('app', 'Add a new product to order')?></label>
                    </div>
                </div>
                <div class="row form-inline">
                    <div class="col-xs-12 form-group">
                        <?=
                        Html::dropDownList(
                            'parentId',
                            [],
                            ['0' => Yii::t('app', 'Select a parent order item')] + \yii\helpers\ArrayHelper::map(
                                $model->items,
                                'id',
                                function($element) {
                                    return !is_null($element->product)
                                        ? $element->product->name :
                                        Yii::t('app', 'Product not found');
                                }
                            ),
                            [
                                'class' => 'form-control input-group-addon col-xs-3',
                                'id' => 'add-product-parent',
                            ]
                        )
                        ?>
                        <?=
                        \app\widgets\AutoCompleteSearch::widget(
                            [
                                'template' => '<p><a href="{{url}}">{{name}}</a></p>',
                                'options' => [
                                    'class' => 'form-control col-xs-9',
                                ],
                                'id' => 'add-product',
                                'name' => 'add-product',
                                'route' => ['auto-complete-search', 'orderId' => $model->id, 'term' => 'QUERY'],
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
                    'title' => Yii::t('app', 'Order transactions'),
                ]
            );
            ?>
            <?= \app\modules\shop\widgets\OrderTransaction::widget([
                'viewFile' => 'order-transaction/backend',
                'model' => $model,
                'immutable' => $orderIsImmutable,
                'additional' => [
                    'transactionsDataProvider' => $transactionsDataProvider,
                ]
            ]); ?>
            <?php BackendWidget::end(); ?>
        </div>
    </div>
</div>
<?php
echo $this->blocks['page-buttons'];
    $form->end();
?>



<?php

$js = <<<JS
    "use strict";
    $('#orderchat-message').keypress(function (event) {
        if (event.keyCode == 10) {
            $(this).parents('form').eq(0).submit();
        }
    });
    $('body').on('editableSuccess', function () {
        location.reload();
    });
    $('#print-button').click(function () {
        window.print();
        return false;
    });
/*    $('#add-product-parent').change(function() {
        var parentId = $(this).val();
        console.log(parentId);
        jQuery('#add-product').autocomplete('option', 'source', '/shop/backend-order/auto-complete-search?orderId={$model->id}&parentId=' + parentId);
    });*/
    $('a[href="#clear"]').on('click', function(event) {
        event.preventDefault();
        $('select#' + $(this).data('sel')).val(0).trigger('change');
        return false;
    });
    $('a.new_customer').on('click', function(event) {
        $(this).attr('href', '/shop/backend-customer/create?user=' + $('select#order-user_id').val());
    });
    $('a.new_contragent').on('click', function(event) {
        $(this).attr('href', '/shop/backend-contragent/create?customer=' + $('select#order-customer_id').val());
    });
JS;
$this->registerJs($js);

?>

