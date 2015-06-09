<?php

use app\backend\components\ActionColumn;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;

/**
 * @var $this yii\web\View
 * @var $searchModel app\components\SearchModel
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = Yii::t('app', 'Open Graph Object');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= \app\backend\widgets\GridView::widget(
    [
        'dataProvider' => $provider,
        'columns' => [
            'id',
            'object.name',
            'active',
            [
                'class' => 'app\backend\components\ActionColumn',
                'buttons' => [
                    [
                        'url' => 'edit',
                        'icon' => 'pencil',
                        'class' => 'btn-primary',
                        'label' => Yii::t('app', 'Edit'),
                    ],

                ],
            ],


        ]
    ]
);
?>
