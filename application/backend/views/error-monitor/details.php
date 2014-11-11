<?php

use kartik\dynagrid\DynaGrid;

$this->title = Yii::t('app', 'URL details');
$this->params['breadcrumbs'][] = ['url' => ['/backend/error-monitor/index'], 'label' => Yii::t('app', 'Error Monitor')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php
    echo DynaGrid::widget(
        [
            'options' => [
                'id' => 'details-info-grid',
            ],
            'columns' => [
                'timestamp:datetime',
                'http_code',
                'info',
                'server_vars',
                'request_vars',
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