<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel app\models\SpamCheckerBehavior
 */
use app\backend\widgets\BackendWidget;
use app\models\SpamChecker;
use kartik\dynagrid\DynaGrid;
use kartik\grid\CheckboxColumn;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Spam Checker Settings');
$this->params['breadcrumbs'][] = $this->title;



?>
<div class="config-index">
    <div class="row">
        <div class="col-md-4">
            <?php $form = ActiveForm::begin(['id' => 'spamchecker-form', 'type' => ActiveForm::TYPE_VERTICAL]); ?>
            <?php BackendWidget::begin(['title' => Yii::t('app', 'Spam Checker Settings'), 'icon' => 'list']); ?>

            <?=$form->field($model, 'yandexApiKey')?>

            <?=$form->field($model, 'akismetApiKey')?>

            <?=$form->field($model, 'enabledApiKey')->dropDownList(SpamChecker::getAvailableApis());?>

            <?=$form->field($model, 'configFieldsParentId')->dropDownList(
                SpamChecker::getFieldTypesForForm()
            )?>

            <?=Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save'),
                ['class' => 'btn btn-primary']
            )?>

            <?php BackendWidget::end(); ?>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-md-8">
            <?=DynaGrid::widget(
                [
                    'options' => [
                        'id' => 'spam-grid',
                    ],
                    'columns' => [
                        [
                            'class' => CheckboxColumn::className(),
                            'options' => [
                                'width' => '10px',
                            ],
                        ],
                        [
                            'class' => 'yii\grid\DataColumn',
                            'attribute' => 'id',
                        ],
                        'behavior',
                        [
                            'class' => 'app\backend\components\ActionColumn',
                            'buttons' => [
                                [
                                    'url' => 'edit',
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
                            ]

                        ],
                    ],
                    'theme' => 'panel-default',
                    'gridOptions' => [
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'hover' => true,
                        'panel' => [
                            'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
                            'after' => $this->blocks['buttonGroup'],

                        ],

                    ]
                ]
            );?>
        </div>
    </div>
</div>