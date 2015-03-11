<?php

/* @var yii\web\View $this */
/* @var \yii\data\ActiveDataProvider $objects */

$this->title = Yii::t('app', 'Data');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= \app\widgets\Alert::widget() ?>

<div class="data-index">
    <?=
    \yii\grid\GridView::widget([
        'id' => 'data-grid',
        'dataProvider' => $objects,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            [
                'attribute' => 'id',
                'options' => [
                    'width' => '50px',
                ],
            ],
            'name',
            [
                'attribute' => 'lastExport.filename',
                'format' => 'raw',
                'label' => Yii::t('app', 'Last Export File'),
                'value' => function ($model, $key, $index, $column) {
                    if (isset($model->lastExport)) {
                        $date = \yii\helpers\Html::tag(
                            'small',
                            ' [' . date('d-M-Y H:i', $model->lastExport->update_time) . ']'
                        );
                        switch ($model->lastExport->status) {
                            case \app\data\models\Export::STATUS_PROCESS :
                                return \yii\helpers\Html::img('/img/loading-block.gif') . $date;
                            case \app\data\models\Export::STATUS_COMPLETE :
                                return \yii\helpers\Html::a(
                                    Yii::t('app', 'Download') . $date,
                                    [Yii::$app->getModule('data')->dataBase . '/export/' . $model->lastExport->filename],
                                    [
                                        'class' => 'btn btn-primary btn-sm'
                                    ]
                                );
                            case \app\data\models\Export::STATUS_FAILED :
                                return \yii\helpers\Html::tag(
                                    'span',
                                    \kartik\icons\Icon::show('warning')
                                        . Yii::t('app', 'failed'),
                                    [
                                        'class' => 'label label-danger',
                                    ]
                                ) . $date;
                        }
                    }

                    return null;
                }
            ],
            [
                'attribute' => 'lastImport.status',
                'format' => 'raw',
                'label' => Yii::t('app', 'Last Import Status'),
                'value' => function ($model, $key, $index, $column) {
                    if (isset($model->lastImport)) {
                        $date = \yii\helpers\Html::tag(
                            'small',
                            ' [' . date('d-M-Y H:i', $model->lastImport->update_time) . ']'
                        );
                        switch ($model->lastImport->status) {
                            case \app\data\models\Export::STATUS_PROCESS :
                                return \yii\helpers\Html::img('/img/loading-block.gif') . $date;
                            case \app\data\models\Export::STATUS_COMPLETE :
                                return \yii\helpers\Html::tag(
                                    'span',
                                    \kartik\icons\Icon::show('check') . Yii::t('app', 'complete'),
                                    [
                                        'class' => 'label label-success',
                                    ]
                                ) . $date;
                            case \app\data\models\Export::STATUS_FAILED :
                                return \yii\helpers\Html::tag(
                                    'span',
                                    \kartik\icons\Icon::show('warning') . Yii::t('app', 'failed'),
                                    [
                                        'class' => 'label label-danger',
                                    ]
                                ) . $date;
                        }
                    }

                    return null;
                }
            ],
            [
                'class' => \app\backend\components\ActionColumn::className(),
                'buttons' => [
                    [
                        'url' => 'export',
                        'icon' => 'download',
                        'class' => 'btn-primary',
                        'label' => 'Export',
                        'text' => Yii::t('app', 'Export'),
                    ],
                    [
                        'url' => 'import',
                        'icon' => 'upload',
                        'class' => 'btn-primary',
                        'label' => 'Import',
                        'text' => Yii::t('app','Import'),
                    ],
                ],
                'options' => [
                    'width' => '190px',
                ],
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-condensed table-hover table-bordered',
        ]
    ]);
    ?>
</div>
