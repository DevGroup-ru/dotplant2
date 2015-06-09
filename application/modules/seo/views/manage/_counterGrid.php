<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $id
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\seo\models\Counter $searchModel
 */

?>

<?php Pjax::begin() ?>

<?= GridView::widget([
    'id' => $id,
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "{items}\n{summary}\n{pager}",
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'options' => [
                'width' => '10px',
            ],
        ],
        [
            'class' => 'yii\grid\DataColumn',
            'attribute' => 'id',
            'options' => [
                'width' => '60px',
            ],
        ],
        'name',
        'description',
        [
            'class' => 'yii\grid\DataColumn',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::textarea(
                    'code',
                    $data['code'],
                    ['data-editor' => 'html', 'data-read-only' => 'true', 'rows' => 25, 'cols' => 125]
                );
            },
        ],
        [
            'class' => 'app\backend\components\ActionColumn',
            'urlCreator' => function ($action, $model, $key, $index) {
                $params = is_array($key) ? $key : ['id' => (string)$key];
                $action .= '-counter';
                $params[0] = $this->context->id ? $this->context->id . '/' . $action : $action;
                $params['returnUrl'] = \app\backend\components\Helper::getReturnUrl();
                return Url::toRoute($params);
            },
            'options' => [
                'width' => '95px',
            ],
            'buttons' => [
                [
                    'url' => 'update',
                    'icon' => 'pencil',
                    'class' => 'btn-primary',
                    'label' => Yii::t('app', 'Edit'),
                ],
                [
                    'url' => 'delete',
                    'icon' => 'trash-o',
                    'class' => 'btn-danger',
                    'label' => Yii::t('app', 'Delete'),
                ],
            ],
        ],
    ],
    'tableOptions' => [
        'class' => 'table table-striped table-condensed table-hover',
    ]
]); ?>

<?php Pjax::end() ?>
