<?php

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;

$this->title = Yii::t('app', 'Error Monitor');

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
); ?>

<div class="row">

    <?php $this->beginBlock('search-form'); ?>
    <?= Html::beginForm('search', 'get', ['id' => 'code_sorter', 'class' => 'form-inline', 'role' => 'form']) ?>
    <?= Html::label('HTTP code: ', 'code') ?>
    <?= Html::input('text', 'ErrorMonitor[http_code]', null, ['class' => 'form-control']); ?>
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']); ?>
    <?= Html::endForm() ?>
    <?php $this->endBlock(); ?>

    <div class="col-md-12">
        <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'errors-grid',
                ],
                'columns' => [
                    'url',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'options' => [
                            'width' => '50px',
                        ],
                        'buttons' => [
                            [
                                'url' => 'details',
                                'icon' => 'info',
                                'class' => 'btn-primary',
                                'label' => 'Detailed info',
                            ],
                        ],
                    ]
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'panel' => [
                        'heading'=>'<h3 class="panel-title">'.$this->title.'</h3>',
                        'before' => $this->blocks['search-form'],
                    ]
                ]
            ]
        );
        ?>
    </div>
</div>



