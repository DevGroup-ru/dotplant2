<?php
use kartik\dynagrid\DynaGrid;
use yii\helpers\Html;
use app\backend\components\ActionColumn;
use \app\modules\shop\models\AddonCategory;
/*
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \app\modules\shop\models\Addon
 * @var $this yii\web\View
 */
/** @var AddonCategory $addonCategory */

$this->title = Yii::t('app', 'Addon for category "{category}"', ['category' => Html::encode($addonCategory->name)]);
$this->params['breadcrumbs'][] = [
    'url' => ['index'],
    'label' => Yii::t('app', 'Addon categories')
];
$this->params['breadcrumbs'][] = [
    'url' => ['edit-category', 'id' => $addonCategory->id],
    'label' => Yii::t('app', 'Category "{category}"', ['category' => Html::encode($addonCategory->name)])
];
$this->params['breadcrumbs'][] = $this->title;

?>
<?=
DynaGrid::widget(
    [
        'options' => [
            'id' => 'addons-grid',
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
                    ['edit-addon', 'addon_category_id' => $addonCategory->id],
                    ['class' => 'btn btn-success']
                ) .  \app\backend\widgets\RemoveAllButton::widget([
                    'url' => 'remove-all-addons',
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
            'price',
            [
                'class' => \kartik\grid\BooleanColumn::className(),
                'attribute' => 'add_to_order'
            ],
            [
                'class' => \kartik\grid\BooleanColumn::className(),
                'attribute' => 'can_change_quantity'
            ],
            [
                'class' => ActionColumn::className(),
                'buttons' => [
                    [
                        'url' => 'edit-addon',
                        'icon' => 'pencil',
                        'class' => 'btn-default',
                        'label' => Yii::t('app', 'Edit'),
                        'url_append' => '&addon_category_id=' . $addonCategory->id

                    ],
                    [
                        'url' => 'delete-addon',
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

