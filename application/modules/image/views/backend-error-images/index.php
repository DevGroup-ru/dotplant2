<?php

use app\backend\widgets\BackendWidget;

$this->title = Yii::t('app', 'Broken images');
$this->params['breadcrumbs'][] = $this->title;


?>
<?php
BackendWidget::begin(['title' => Yii::t('app', 'Broken images')]);
?>
<?=
\kartik\dynagrid\DynaGrid::widget(
    [
        'options' => [
            'id' => 'form-grid',
        ],
        'columns' => [
            'id',
            [
                'format' => 'raw',
                'label' => Yii::t('app', 'Frontend links'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\image\models\ErrorImage $model */
                    return $model->getFrontendObjectLink();
                },
            ],
            [
                'format' => 'raw',
                'label' => Yii::t('app', 'Backend links'),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\image\models\ErrorImage $model */
                    return $model->getBackendObjectLink();
                },
            ],
            [
                'attribute' => 'class_name',
                'filter' => \app\modules\image\models\ErrorImage::getClassNames(),
                'value' => function ($model, $key, $index, $column) {
                    /** @var \app\modules\image\models\ErrorImage $model */
                    return $model->getClassName();
                },
            ],
        ],
        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                'after' => \kartik\helpers\Html::a(
                    Yii::t('app', 'Find broken images'),
                    ['find'],
                    [
                        'class' => 'btn btn-success',
                    ]
                ),
            ],
        ]
    ]
);
