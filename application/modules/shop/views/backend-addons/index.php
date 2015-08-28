<?php
use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;
use app\backend\components\ActionColumn;
/*
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel app\modules\models\AddonCategory
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', 'Addons categories');
$this->params['breadcrumbs'][] = $this->title;

?>
<?=
DynaGrid::widget(
    [
        'options' => [
            'id' => 'addons-categories-grid',
        ],
        'theme' => 'panel-default',
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => true,
            'panel' => [
                'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                'after' => Html::a(
                    \kartik\icons\Icon::show('plus') . Yii::t('app', 'Add'),
                    ['edit-category'],
                    ['class' => 'btn btn-success']
                ) .  \app\backend\widgets\RemoveAllButton::widget([
                    'url' => 'remove-all-categories',
                    'gridSelector' => '.grid-view',
                    'htmlOptions' => [
                        'class' => 'btn btn-danger pull-right'
                    ],
                ]),
            ],
        ],
        'columns' => [
            [
                'class' => \app\backend\columns\CheckboxColumn::className(),
            ],
            'id',
            'name',
            [
                'class' => ActionColumn::className(),
                'buttons' => [
                    [
                        'url' => 'edit-category',
                        'icon' => 'pencil',
                        'class' => 'btn-default',
                        'label' => Yii::t('app', 'Edit'),

                    ],
                    [
                        'url' => 'view-category',
                        'icon' => 'list',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'View'),

                    ],
                    [
                        'url' => 'delete-category',
                        'icon' => 'trash-o',
                        'class' => 'btn-danger',
                        'label' => Yii::t('app', 'Delete'),
                    ],
                ]
            ],
        ],
    ]
);
?>

