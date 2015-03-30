<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Submission $searchModel
 */

$this->title = Yii::t('app', 'Submissions');
$this->params['breadcrumbs'][] = ['url' => ['/backend/form/index'], 'label' => Yii::t('app', 'Forms')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= \kartik\dynagrid\DynaGrid::widget([
    'options' => [
        'id' => 'submission-grid',
    ],
    'columns' => [
        'id',
        'date_received',
        'ip',
        'user_agent',
        [
            'class' => 'app\backend\components\ActionColumn',
            'buttons' => [
                [
                    'url' => 'view-submission',
                    'icon' => 'eye',
                    'class' => 'btn-info',
                    'label' => 'View',
                ],
            ],
            'options' => [
                'width' => '50px',
            ]
        ],
    ],
    'theme' => 'panel-default',
    'gridOptions'=>[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover'=>true,
        'panel'=>[
            'heading'=>'<h3 class="panel-title">'.$this->title.'</h3>',
            'after' => $this->blocks['add-button'],
        ],

    ]
]); ?>