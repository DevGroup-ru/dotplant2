<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Submission $searchModel
 */

use app\models\Submission;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Submissions');
$this->params['breadcrumbs'][] = ['url' => ['/backend/form/index'], 'label' => Yii::t('app', 'Forms')];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('buttons');
echo Html::beginTag('div', ['class' => 'form-group no-margin']);
if (Yii::$app->request->get('show_deleted') == 1) {
    echo Html::a(
        Yii::t('app', 'Show undeleted'),
        ['/backend/form/view', 'id' => Yii::$app->request->get('id')],
        ['class' => 'btn btn-info']
    );
} else {
    echo Html::a(
        Yii::t('app', 'Show deleted'),
        ['/backend/form/view', 'id' => Yii::$app->request->get('id'), 'show_deleted' => 1],
        ['class' => 'btn btn-danger']
    );
}
echo Html::endTag('div');
$this->endBlock('buttons');
?>
<?=\kartik\dynagrid\DynaGrid::widget(
    [
        'options' => [
            'id' => 'submission-grid-'.$form->id,
        ],
        'columns' => \yii\helpers\ArrayHelper::merge(
            $searchModel->columns([
                'id',
                'date_received',
                'ip',
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'sending_status',
                    'filter' => Submission::getStatuses(),
                    'value' => function ($model, $key, $index, $widget) {
                        $array = Submission::getStatuses();
                        return $array[$model->sending_status];
                    }
                ],
                [
                    'attribute' => 'user_agent',
                    'format' => ['truncated', 40],
                ]
            ]),
            [[
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => function ($model, $key, $index, $parent) {
                    if (1 === $model->is_deleted) {
                        return [
                            [
                                'url' => 'view-submission',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => 'View',
                            ],
                            [
                                'url' => 'restore-submission',
                                'icon' => 'refresh',
                                'class' => 'btn-success',
                                'label' => 'Restore',
                            ],
                            [
                                'url' => 'delete-submission',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => 'Delete',
                            ],
                        ];
                    }
                    return [
                        [
                            'url' => 'view-submission',
                            'icon' => 'eye',
                            'class' => 'btn-info',
                            'label' => 'View',
                        ],
                        [
                            'url' => 'delete-submission',
                            'icon' => 'trash-o',
                            'class' => 'btn-danger',
                            'label' => 'Delete',
                        ],
                    ];
                },
                'options' => [
                    'width' => '50px',
                ]
            ]]
        ),

        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                'after' => $this->blocks['buttons'],
            ],

        ]
    ]
);?>