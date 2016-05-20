<?php

use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\Html;

/**
 * @var $id
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = Yii::t('app', 'Doubles Redirects');

?>

<h1><?= Html::encode($this->title) ?></h1>

<?php Pjax::begin() ?>

<?= GridView::widget([
    'id' => 'redirects',
    'dataProvider' => $dataProvider,
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
            'attribute' => 'type',
            'filter' => \app\modules\seo\models\Redirect::getTypes(),
        ],
        'from',
        [
            'class' => 'app\backend\components\ActionColumn',
            'urlCreator' => function ($action, $model, $key, $index) {
                $params = ['Redirect[from]' => $model->from];
                $params[0] = '/seo/manage/redirect';
                return Url::toRoute($params);
            },
            'buttons' => [
                [
                    'url' => 'all',
                    'icon' => 'list',
                    'class' => 'btn-primary',
                    'label' => Yii::t('app', 'Show all'),
                ],
            ],
        ],

    ],
    'tableOptions' => [
        'class' => 'table table-striped table-condensed table-hover',
    ]
]); ?>

<?php Pjax::end();

