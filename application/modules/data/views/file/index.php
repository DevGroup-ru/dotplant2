<?php

/* @var yii\web\View $this */
/* @var \yii\data\ActiveDataProvider $objects */

use \app\modules\data\assets\DataAsset;

$bundle = DataAsset::register($this);

$this->title = Yii::t('app', 'Data');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= \app\widgets\Alert::widget() ?>
<div class="row">
<div class="col-md-12">
    <div class="row">
        <?php \app\backend\widgets\BackendWidget::begin([
            'icon' => 'cogs',
            'title' => Yii::t('app', 'CommerceML'),
        ]); ?>
        <?= \yii\helpers\Html::a(
                Yii::t('app', 'Import from CommerceML'),
                \yii\helpers\Url::to(['/data/commerceml']),
                ['class' => 'btn btn-primary']
        ); ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'Configure CommerceML'),
            \yii\helpers\Url::to(['/data/commerceml/configure']),
            ['class' => 'btn btn-primary']
        ); ?>
        <?php \app\backend\widgets\BackendWidget::end(); ?>
    </div>
</div>
</div>
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
                'value' => function ($model, $key, $index, $column) use ($bundle) {
                    if (isset($model->lastExport)) {
                        $date = \yii\helpers\Html::tag(
                            'small',
                            ' [' . date('d-M-Y H:i', $model->lastExport->update_time) . ']'
                        );
                        switch ($model->lastExport->status) {
                            case \app\modules\data\models\Export::STATUS_PROCESS :
                                return \yii\helpers\Html::img($bundle->baseUrl.'/loading-block.gif') . $date;
                            case \app\modules\data\models\Export::STATUS_COMPLETE :
                                return \yii\helpers\Html::a(
                                    Yii::t('app', 'Download') . $date,
                                    [
                                        '/data/file/download-file',
                                        'dir' => 'export',
                                        'file' => $model->lastExport->filename
                                    ],
                                    [
                                        'class' => 'btn btn-primary btn-sm'
                                    ]
                                );
                            case \app\modules\data\models\Export::STATUS_FAILED :
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
                'value' => function ($model, $key, $index, $column) use ($bundle) {
                    if (isset($model->lastImport)) {
                        $date = \yii\helpers\Html::tag(
                            'small',
                            ' [' . date('d-M-Y H:i', $model->lastImport->update_time) . ']'
                        );
                        switch ($model->lastImport->status) {
                            case \app\modules\data\models\Export::STATUS_PROCESS :
                                return \yii\helpers\Html::img($bundle->baseUrl.'/loading-block.gif') . $date;
                            case \app\modules\data\models\Export::STATUS_COMPLETE :
                                return \yii\helpers\Html::tag(
                                    'span',
                                    \kartik\icons\Icon::show('check') . Yii::t('app', 'complete'),
                                    [
                                        'class' => 'label label-success',
                                    ]
                                ) . $date;
                            case \app\modules\data\models\Export::STATUS_FAILED :
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
