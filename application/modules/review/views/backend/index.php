<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\backgroundtasks\models\Task $searchModel
 */

use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\modules\review\models\Review;

$this->title = Yii::t('app', 'Product reviews');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
$this->beginBlock('add-button');
?>
<div class="clearfix"></div>
<?=\app\backend\widgets\RemoveAllButton::widget(
    [
        'url' => Url::toRoute(
            [
                '/backend/review/remove-all',
                'returnUrl' => Yii::$app->request->url
            ]
        ),
        'gridSelector' => '.grid-view',
        'htmlOptions' => [
            'class' => 'btn btn-danger pull-right'
        ],
    ]
);?>
<div class="clearfix"></div>
<?php
$this->endBlock();
?>

<div class="reviews-index">
    <?=
    DynaGrid::widget(
        [
            'options' => [
                'id' => 'reviews-grid',
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => $this->blocks['add-button'],
                ],
            ],
            'columns' => [
                [
                    'class' => \kartik\grid\CheckboxColumn::className(),
                    'options' => [
                        'width' => '10px',
                    ],
                ],
                'id',
                'author_email',
                [
                    'class' => yii\grid\DataColumn::className(),
                    'attribute' => 'Form',
                    'value' => function ($data) {
                        if (isset($data->submission)) {
                            /**@var $form \app\models\Form */
                            $form = \app\models\Form::findById($data->submission->form_id);
                            if (null !== $form) {
                                return $form->name;
                            }
                        }
                    },
                ],
                [
                    'class' => yii\grid\DataColumn::className(),
                    'attribute' => 'object_model_id',
                    'value' => function ($data) {
                        /** @var $object \app\models\Object*/
                        if (null !== $object = \app\models\Object::findById($data->object_id)) {
                            $class = $object->object_class;
                            $resource = $class::findById($data->object_model_id);
                            if (null !== $resource) {
                                return $resource->name;
                            }
                            return null;
                        }
                    },
                ],
                [
                    'class' => yii\grid\DataColumn::className(),
                    'attribute' => 'processed_by_user_id',
                    'value' => function ($data) {
                        if (isset($data->submission)) {
                            if (null !== $data->submission->processed_by_user_id) {
                                /** @var $user \app\modules\user\models\User */
                                $user = \app\modules\user\models\User::findIdentity($data->submission->processed_by_user_id);
                                return $user->getDisplayName();
                            } else {
                                return Yii::t('app', 'Guest');
                            }
                        }
                        return null;
                    }
                ],
                'submission.date_received',
//                [
//                    'attribute'=>'date_received',
//                    'value'=>function ($model) {
//                        return date("y-m-d h:i", strtotime($model->submission->date_received));
//                    },
//                    'filter'=>GridView::FILTER_DATE,
//                    'format'=>'raw',
//                    'filterWidgetOptions' => [
//                        'pluginOptions' => ['format' => 'y-m-d h:i']
//                    ],
//
//                ],
                [
                    'attribute' => 'status',
                    'class' => \kartik\grid\EditableColumn::className(),
                    'editableOptions' => [
                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                        'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                        'data' => Review::getStatuses(),
                        'formOptions' => [
                            'action' => 'update-status',
                        ],
                    ],
                    'filter' => Review::getStatuses(),
                    'format' => 'raw',
                ],
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => function($model, $key, $index, $parent) {
                        return [
                            [
                                'url' => 'view',
                                'icon' => 'eye',
                                'class' => 'btn-info',
                                'label' => Yii::t('app', 'View'),
                            ],
                            [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => 'Delete',
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ];
                    }
                ],
            ],
        ]
    );
    ?>
</div>