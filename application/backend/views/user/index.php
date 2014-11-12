<?php

/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \app\components\SearchModel
 * @var $this \yii\web\View
 */

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-index">
    <?=
        DynaGrid::widget([
            'options' => [
                'id' => 'users-grid',
            ],
            'columns' => [
                'id',
                'username',
                'email:email',
                [
                    'attribute' => 'status',
                    'filter' => app\models\User::getStatuses(),
                ],
                'create_time:datetime',
                [
                    'class' => ActionColumn::className(),
                    'buttons' => [
                        [
                            'url' => 'update',
                            'icon' => 'pencil',
                            'class' => 'btn-primary',
                            'label' => Yii::t('app', 'Edit'),

                        ],
                        [
                            'url' => 'delete',
                            'icon' => 'trash-o',
                            'class' => 'btn-danger',
                            'label' => Yii::t('app', 'Delete'),
                        ],
                    ],
                ],
            ],
            'theme' => 'panel-default',
            'gridOptions'=>[
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => Html::a(
                        Icon::show('plus') . Yii::t('app', 'Add'),
                        ['/backend/user/update'],
                        ['class' => 'btn btn-success']
                    ),

                ],
            ]
        ]);
    ?>
</div>