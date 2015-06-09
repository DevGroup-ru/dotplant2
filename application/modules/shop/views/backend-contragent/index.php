<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \app\components\SearchModel $searchModel
 */

use yii\helpers\Html;
use yii\helpers\Url;

    $this->title = Yii::t('app', 'Contragents');
    $this->params['breadcrumbs'][] = $this->title;
?>
<div class="contragent-index">
    <?=
    \kartik\dynagrid\DynaGrid::widget([
        'options' => [
            'id' => 'contragents-index-grid',
        ],
        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                'after' => Html::a(
                    \kartik\icons\Icon::show('plus') . Yii::t('app', 'Add'),
                    ['create', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
                    ['class' => 'btn btn-success']
                ),
            ],
            'rowOptions' => function ($model, $key, $index, $grid) {
                /** @var \app\modules\shop\models\Contragent $model */
                if (null === $model->customer) {
                    return [
                        'class' => 'danger',
                    ];
                }
                return [];
            },
        ],
        'columns' => [
            'id',
            [
                'attribute' => 'orders',
                'label' => Yii::t('app', 'Orders count'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\shop\models\Contragent $model */
                    return count($model->orders);
                }
            ],
            [
                'attribute' => 'customer_id',
                'label' => Yii::t('app', 'Customer'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\shop\models\Contragent $model */
                    $customer = $model->customer;
                    return null === $customer
                        ? Yii::t('app', 'Guest')
                        : Html::a($customer->first_name, Url::toRoute(['/shop/backend-customer/edit', 'id' => $customer->id]));
                },
                'format' => 'raw',
            ],
            'type',
            [
                'label' => Yii::t('app', 'Additional information'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\shop\models\Contragent $model */
                    /** @var \app\properties\AbstractModel $abstractModel */
                    $abstractModel = $model->getAbstractModel();
                    $abstractModel->setArrayMode(false);
                    $props = '';
                    foreach ($abstractModel->attributes() as $attr) {
                        $props .= '<li>' . $abstractModel->getAttributeLabel($attr) . ': ' . $abstractModel->$attr .'</li>';
                    }

                    return !empty($props) ? '<ul class="additional_information">'.$props.'</ul>' : '';
                },
                'format' => 'raw',
            ],
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' =>  function($model, $key, $index, $parent) {
                    $result = [
                        [
                            'url' => 'edit',
                            'icon' => 'eye',
                            'class' => 'btn-info',
                            'label' => Yii::t('app','View'),
                        ],
                    ];
                    return $result;
                },
            ],
        ],
    ]);
    ?>
</div>
