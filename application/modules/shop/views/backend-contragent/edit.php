<?php

use app\backend\widgets\BackendWidget;
use app\components\Helper;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Contragent $model
 */

    $this->title = Yii::t('app', 'Contragent edit');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contragents'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('buttons_primary');
    echo \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']);
    echo empty($model->customer) ? '' : Html::a(
        Yii::t('app', 'View customer'),
        \yii\helpers\Url::toRoute(['/shop/backend-customer/edit', 'id' => $model->customer_id]),
        ['class' => 'btn btn-default']
    );
$this->endBlock();

    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'contragent-form',
        'action' => \yii\helpers\Url::toRoute(['edit', 'id' => $model->id]),
        'layout' => 'horizontal',
    ]);
    BackendWidget::begin([
        'icon' => 'user',
        'title' => Yii::t('app', 'Contragent edit'),
        'footer' => $this->blocks['buttons_primary'],
    ]);

    $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2contragent-result">' +
        '<strong>' + (data.first_name || '') + ' ' + (data.middle_name || '') + ' ' + (data.last_name || '') + ' ' + '</strong>' +
        '<div>' + (data.email || '') + ' (' + (data.phone || '') + ')</div>' +
        '</div>';
    return tpl;
}
JSCODE;

    echo \app\backend\widgets\Select2Ajax::widget([
        'initialData' => [$model->customer_id => null !== $model->customer ? $model->customer->first_name : 'Guest'],
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
        ]
    ]);
    echo \app\modules\shop\widgets\Contragent::widget([
        'viewFile' => 'contragent/backend_contragent',
        'model' => $model,
        'form' => $form,
    ]);

    BackendWidget::end();
    $form->end();

    $searchModelConfig = [
        'defaultOrder' => ['id' => SORT_DESC],
        'model' => \app\modules\shop\models\Order::className(),
        'partialMatchAttributes' => ['start_date', 'end_date', 'user_username'],
        'additionalConditions' => [
            ['contragent_id' => $model->id],
        ],
    ];
    /** @var \app\components\SearchModel $searchModel */
    $searchModel = new \app\components\SearchModel($searchModelConfig);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    echo \kartik\dynagrid\DynaGrid::widget(
        [
            'options' => [
                'id' => 'orders-grid',
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', 'Contragent orders', ['class' => 'panel-title']),
                ],
                'rowOptions' => function ($model, $key, $index, $grid) {
                    if ($model->is_deleted) {
                        return [
                            'class' => 'danger',
                        ];
                    }
                    return [];
                },
            ],
            'columns' => [
                [
                    'attribute' => 'id',
                ],
                [
                    'attribute' => 'user_username',
                    'label' => Yii::t('app', 'User'),
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->user === null) {
                            return null;
                        }
                        return $model->user->username;
                    },
                ],
                'start_date',
                'end_date',
                [
                    'attribute' => 'order_stage_id',
                    'filter' => Helper::getModelMap(\app\modules\shop\models\OrderStage::className(), 'id', 'name_short'),
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->stage === null) {
                            return null;
                        }
                        return $model->stage->name_short;
                    },
                ],
                [
                    'attribute' => 'shipping_option_id',
                    'filter' => Helper::getModelMap(\app\modules\shop\models\ShippingOption::className(), 'id', 'name'),
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->shippingOption === null) {
                            return null;
                        }
                        return $model->shippingOption->name;
                    },
                ],
                [
                    'attribute' => 'payment_type_id',
                    'filter' => Helper::getModelMap(\app\modules\shop\models\PaymentType::className(), 'id', 'name'),
                    'value' => function ($model, $key, $index, $column) {
                        if ($model === null || $model->paymentType === null) {
                            return null;
                        }
                        return $model->paymentType->name;
                    },
                ],
                'items_count',
                'total_price',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' =>  function($model, $key, $index, $parent) {
                        $result = [
                            [
                                'url' => '/shop/backend-order/view',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app','View'),
                            ],
                        ];
                        return $result;
                    },
                ],
            ],
        ]
    );