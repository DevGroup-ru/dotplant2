<?php

use app\backend\widgets\BackendWidget;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $model
 */

    $this->title = Yii::t('app', 'New order');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Orders'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $form = \kartik\widgets\ActiveForm::begin(
        [
            'method' => 'post',
            'type' => \kartik\form\ActiveForm::TYPE_HORIZONTAL,
            'options' => [
                'class' => 'form-order-backend-create',
            ],
        ]
    );
    BackendWidget::begin(
        [
            'icon' => 'info-circle',
            'title' => Yii::t('app', 'Order information'),
            'footer' => Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-success'])
        ]
    );
?>

<div class="row">
    <div class="col-md-4">
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
        'initialData' => [$model->user_id => null !== $model->user ? $model->user->username : 'Guest'],
        'multiple' => false,
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-user']),
        'pluginOptions' => [
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.username || data.text;}'),
        ],
    ]);
    echo Html::tag('div', Html::a(Yii::t('app', 'Clear'), '#clear', ['data-sel' => 'order-user_id', 'class' => 'col-md-offset-2']));
?>
    </div>

    <div class="col-md-4">
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
        search: {term:term.term, user:$('select#order-user_id').val()}
    };
}
JSCODE;
    echo \app\backend\widgets\Select2Ajax::widget([
        'initialData' => [$model->customer_id => null !== $model->customer ? $model->customer->first_name : 'New customer'],
        'model' => $model,
        'modelAttribute' => 'customer_id',
        'form' => $form,
        'multiple' => false,
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-customer']),
        'pluginOptions' => [
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.first_name || data.text;}'),
            'ajax' => [
                'data' => new \yii\web\JsExpression($_jsDataFunc),
            ]
        ]
    ]);
    echo Html::tag('div', Html::a(Yii::t('app', 'Clear'), '#clear', ['data-sel' => 'order-customer_id', 'class' => 'col-md-offset-2']));
    echo Html::tag('div',
        \app\modules\shop\widgets\Customer::widget([
            'viewFile' => 'customer/inherit_form',
            'form' => $form,
            'model' => $customer = \app\modules\shop\models\Customer::createEmptyCustomer(0),
        ]),
        ['id' => 'div_customer']
    );
?>
    </div>

    <div class="col-md-4">
<?php
    $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2customer-result">' +
        '<strong>' + (data.type || '') + '</strong>' +
        '</div>';
    return tpl;
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
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-contragent']),
        'pluginOptions' => [
            'minimumInputLength' => null,
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.type || data.text;}'),
            'ajax' => [
                'data' => new \yii\web\JsExpression($_jsDataFunc),
            ]
        ]
    ]);
    echo Html::tag('div', Html::a(Yii::t('app', 'Clear'), '#clear', ['data-sel' => 'order-contragent_id', 'class' => 'col-md-offset-2']));
    echo Html::tag('div',
        \app\modules\shop\widgets\Contragent::widget([
            'viewFile' => 'contragent/inherit_form',
            'model' => \app\modules\shop\models\Contragent::createEmptyContragent($customer),
            'form' => $form,
        ]),
        ['id' => 'div_contragent']
    );
?>
    </div>
</div>
<?php
    BackendWidget::end();
    $form->end();

    $_js = <<<'JSCODE'
$(function(){
    $('select#order-user_id').on('change', function(event) {
        $('select#order-customer_id').val(0).trigger('change');
    });

    $('select#order-customer_id').on('change', function(event) {
        if (0 == $(this).val()) {
            $('div#div_customer').removeClass('hide');
        } else {
            $('div#div_customer').addClass('hide');
        }

        $('select#order-contragent_id').val(0).trigger('change');
    });

    $('select#order-contragent_id').on('change', function(event) {
        if (0 == $(this).val()) {
            $('div#div_contragent').removeClass('hide');
        } else {
            $('div#div_contragent').addClass('hide');
        }
    });

    $('a[href="#clear"]').on('click', function(event) {
        event.preventDefault();
        $('select#' + $(this).data('sel')).val(0).trigger('change');
        return false;
    });

    $('form.form-order-backend-create').on('submit', function() {
        $('form.form-order-backend-create div.hide').remove();
    });
});
JSCODE;

    $this->registerJs($_js, \yii\web\View::POS_END);