<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \app\components\SearchModel $searchModel
 */

use yii\helpers\Html;

    $this->title = Yii::t('app', 'Customers');
    $this->params['breadcrumbs'][] = $this->title;
?>
<div class="customers-index">
<?=
    \kartik\dynagrid\DynaGrid::widget([
        'options' => [
            'id' => 'customers-index-grid',
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
                /** @var \app\modules\shop\models\Customer $model */
                if (intval($model->user_id) <= 0) {
                    return [
                        'class' => 'warning',
                    ];
                }
                return [];
            },
        ],
        'columns' => [
            'id',
            [
                'attribute' => 'user',
                'label' => Yii::t('app', 'User'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\shop\models\Customer $model */
                    /** @var \app\modules\user\models\User $user */
                    $user = $model->user;

                    return null === $user ?
                        Yii::t('app', 'Guest')
                        : $user->username;
                }
            ],
            'first_name',
            'middle_name',
            'last_name',
            'email',
            'phone',
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
                        [
                            'url' => 'delete',
                            'icon' => 'trash-o',
                            'class' => 'btn-danger',
                            'label' => Yii::t('app', 'Delete'),
                            'options' => [
                                'data-action' => 'delete',
                            ],
                        ]
                    ];
                    return $result;
                },
            ],
        ],
    ]);
?>
</div>
