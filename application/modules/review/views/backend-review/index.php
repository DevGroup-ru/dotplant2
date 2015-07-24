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

$this->title = Yii::t('app', 'Reviews');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
$this->beginBlock('add-button');
?>
<div class="clearfix"></div>
    <div class="btn-group pull-right">
    <?=
    \yii\helpers\Html::a(
        Yii::t('app', 'Add'),
        Url::toRoute(['create', 'parent_id' => 0]),
        [
            'class' => 'btn btn-success'
        ]
    );
    ?>
    <?=\app\backend\widgets\RemoveAllButton::widget(
        [
            'url' => Url::toRoute(
                [
                    'remove-all',
                    'returnUrl' => Yii::$app->request->url
                ]
            ),
            'gridSelector' => '.grid-view',
            'htmlOptions' => [
                'class' => 'btn btn-danger'
            ],
        ]
    );?>
    </div>
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
                    'attribute' => 'submission.form.name',
                    'label' => Yii::t('app', 'Form name'),
                ],
                [
                    'attribute' => 'object_id',
                    'filter' => \app\components\Helper::getModelMap(\app\models\Object::className(), 'id', 'name'),
                    'label' => Yii::t('app', 'Object'),
                    'value' => function ($data) {
                        $obj = \app\models\Object::findById($data->object_id);
                        return is_null($obj) ? Yii::t('yii', '(not set)') : $obj->name;
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
                            'action' => ['update-status'],
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