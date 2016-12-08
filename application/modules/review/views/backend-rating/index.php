<?php

/**
 * @var yii\web\View $this
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;

$this->title = Yii::t('app', 'Rating groups');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('add-button'); ?>
<?=
    \yii\helpers\Html::a(
        \kartik\icons\Icon::show('plus') . Yii::t('app', 'Add'),
        ['group-create', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
        ['class' => 'btn btn-success']
    )
?>
<?php $this->endBlock(); ?>

<div class="rating-index">
    <?=
    DynaGrid::widget(
        [
            'options' => [
                'id' => 'rating-group-grid',
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $data_provider,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => $this->blocks['add-button'],
                ],
            ],
            'columns' => [
                'rating_group',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => function($model, $key, $index, $parent) {
                        return [
                            [
                                'url' => 'group-edit',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => Yii::t('app', 'Edit'),
                            ],
                            [
                                'url' => 'group-delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app', 'Delete'),
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ];
                    },
                    'urlCreator' => function($action, $model, $key, $index) {
                        return \yii\helpers\Url::to([$action, 'group' => urlencode($model->rating_group)]);
                    }
                ],
            ],
        ]
    );
    ?>
</div>