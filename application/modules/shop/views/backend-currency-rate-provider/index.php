<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;


$this->title = Yii::t('app', 'Currency rate providers');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<div class="row">
    <div class="col-md-12">
        <?=
            DynaGrid::widget(
                [
                    'options' => [
                        'id' => 'currencies-grid',
                    ],
                    'columns' => [
                        [
                            'class' => \app\backend\columns\CheckboxColumn::className(),
                        ],
                        'id',
                        'name',
                        'class_name',
                        [
                            'class' => \app\backend\components\ActionColumn::className(),
                        ],
                    ],
                    'theme' => 'panel-default',
                    'gridOptions' => [
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'hover' => true,
                        'panel' => [
                            'heading' => $this->render('@app/modules/shop/views/backend-currencies/_tabs', [
                                'currencies' => false
                            ]),
                            'after' => \app\backend\widgets\helpers\AddRemoveAllPanel::widget([
                                'baseRoute' => '/shop/backend-currency-rate-provider/',
                            ]),
                        ],
                    ]
                ]
            );
        ?>
    </div>
</div>
