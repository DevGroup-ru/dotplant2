<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\modules\seo\models\Redirect $searchModel
 */

use kartik\dynagrid\DynaGrid;

$this->title = Yii::t('app', 'Newsletter now');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-12" id="jstree-more">
        <?php

        echo DynaGrid::widget(
            [
                'options' => [
                    'id' => 'newslist-grid',
                ],
                'columns' => [
                    'id',
                    'h1',
                    'date_added',
                    [
                        'class' => 'app\backend\components\ActionColumn',
                        'options' => [
                            'width' => '50px',
                        ],
                        'buttons' => [
                            [
                                'url' => 'sendnow',
                                'icon' => 'send-o',
                                'class' => 'btn-primary',
                                'label' => 'Send now',
                            ]
                        ],
                    ],
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



