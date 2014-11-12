<?php

use kartik\dynagrid\DynaGrid;

$this->title = Yii::t('app', 'Error Monitor - Search');

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
); ?>

<div class="row">
    <div class="col-md-10">
        <?php
        ?>

        <?=
        DynaGrid::widget(
            [
                'options' => [
                    'id' => 'error-monitor-search-grid',
                ],
                'columns' => [
                    'event_date',
                    'url',
                    'http_code'
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
    </div>
</div>



