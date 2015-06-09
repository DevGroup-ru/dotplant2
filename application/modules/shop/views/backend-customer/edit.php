<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Customer $model
 */

use \app\backend\widgets\BackendWidget;
use yii\helpers\Html;
use app\components\Helper;

    $this->title = Yii::t('app', 'Customer edit');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customers'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'customer-form',
        'action' => \yii\helpers\Url::toRoute(['edit', 'id' => $model->id]),
        'layout' => 'horizontal',
    ]);

    BackendWidget::begin([
        'icon' => 'user',
        'title' => Yii::t('app', 'Customer edit'),
        'footer' => \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']),
    ]);
    echo \app\backend\widgets\Select2Ajax::widget([
        'initialData' => [$model->user_id => null !== $model->user ? $model->user->username : 'Guest'],
        'model' => $model,
        'modelAttribute' => 'user_id',
        'form' => $form,
        'multiple' => false,
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-user']),
        'additional' => [
            'allowClear' => false
        ]
    ]);
    echo \app\modules\shop\widgets\Customer::widget([
        'viewFile' => 'customer/inherit_form',
        'form' => $form,
        'model' => $model,
        'additional' => [
            'hideHeader' => true,
        ],
    ]);
    BackendWidget::end();
    $form->end();


    $searchModelConfig = [
        'defaultOrder' => ['id' => SORT_DESC],
        'model' => \app\modules\shop\models\Order::className(),
        'partialMatchAttributes' => ['start_date', 'end_date', 'user_username'],
        'additionalConditions' => [
            ['customer_id' => $model->id],
        ],
    ];

    /** @var SearchModel $searchModel */
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
                    'heading' => Html::tag('h3', 'Customer orders', ['class' => 'panel-title']),
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
                                'url' => 'view',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app','View'),
                            ],
                        ];
                        if (intval(Yii::$app->getModule('shop')->deleteOrdersAbility) === 1 && $model->is_deleted == 0) {
                            $result[] =  [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app', 'Delete'),
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ];
                        }
                        return $result;
                    },
                ],
            ],
        ]
    );
