<?php

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ArrayDataProvider
 */

use app\backend\components\ActionColumn;
use app\backend\widgets\BackendWidget;
use app\backend\widgets\GridView;
use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'I18n');
$this->params['breadcrumbs'] = [
    $this->title,
];

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <?php
        BackendWidget::begin(
            [
                'icon' => 'language',
                'title'=> $this->title,
                'footer' => Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save'),
                    ['class' => 'btn btn-primary']
                ),
            ]
        );
    ?>
        <?php
            Pjax::begin();
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'label' => Yii::t('app', 'Alias'),
                        'value' => function($model, $key, $index, $column) {
                            return $key;
                        },
                    ],
                    [
                        'label' => Yii::t('app', 'Local file'),
                        'value' => function($model, $key, $index, $column) {
                            return $model;
                        },
                    ],
                    [
                        'class' => ActionColumn::className(),
                        'options' => [
                            'width' => '50px',
                        ],
                        'buttons' => [
                            [
                                'url' => 'update',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => Yii::t('app', 'Edit'),
                            ],
                        ],
                    ],
                ],
            ]);
            Pjax::end();
        ?>
    <?php BackendWidget::end(); ?>
</div>