<?php

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Error Monitor');

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
); ?>

<div class="row">
    <div class="col-md-10">
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
                        'buttons' => [
                            [
                                'url' => 'details',
                                'icon' => 'info',
                                'class' => 'btn-primary',
                                'label' => 'Detailed info',
                            ],
                        ], // /buttons
                    ]
                ],
                'theme' => 'panel-default',
                'gridOptions' => [
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'panel' => [
                        'heading'=>'<h3 class="panel-title">'.$this->title.'</h3>',
                    ]
                ]
            ]
        );
        ?>

        <?php
        $form = ActiveForm::begin(
            [
                'id' => 'code_sorter',
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'action' => 'search',
                'method' => 'get'
            ]
        );
        ?>

        <?= Html::label('HTTP code: ', 'code') ?>
        <?= Html::input('text', 'http_code', ''); ?>
        <?= Html::submitButton('Search'); ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>



