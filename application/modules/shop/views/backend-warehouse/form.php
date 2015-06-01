<?php

use app\backend\widgets\BackendWidget;
use app\backend\components\ActiveForm;
use kartik\helpers\Html;
use kartik\icons\Icon;
use \yii\helpers\ArrayHelper;
use \app\models\Country;
use \app\models\City;

/* @var $this yii\web\View */
/* @var $model app\modules\shop\models\Warehouse */
/* @var $warehousePhone app\modules\shop\models\WarehousePhone */
/* @var $wareHouseOpeningHours \app\modules\shop\models\WarehouseOpeninghours */
/* @var $warehousePhoneProvider \yii\data\ActiveDataProvider */
/* @var $warehouseEmail app\modules\shop\models\WarehouseEmail */
/* @var $warehouseEmailProvider \yii\data\ActiveDataProvider */
/* @var $form ActiveForm */


$this->title = Yii::t('app', 'Warehouse edit');
$this->params['breadcrumbs'][] = ['url' => ['/shop/backend-warehouse/index'], 'label' => Yii::t('app', 'Warehouses')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
); ?>


<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/shop/backend-warehouse/index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        ) ?>
    <?php endif; ?>
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>
</div>
<?php $this->endBlock('submit'); ?>

<div class="row">

    <?php $form = ActiveForm::begin(['id' => 'Warehouse-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <article class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
        <?php BackendWidget::begin([
            'title' => Yii::t('app', 'Warehouse'),
            'icon' => 'cubes',
            'footer' => $this->blocks['submit']
        ]); ?>

        <?= $form->field($model, 'is_active')->widget(\kartik\switchinput\SwitchInput::className()) ?>
        <?= $form->field($model, 'shipping_center')->widget(\kartik\switchinput\SwitchInput::className()) ?>
        <?= $form->field($model, 'issuing_center')->widget(\kartik\switchinput\SwitchInput::className()) ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'country_id')->dropDownList(
            ArrayHelper::map(
                Country::find()->all(),
                'id',
                'name'
            )
        ) ?>
        <?= $form->field($model, 'city_id')->dropDownList(
            ArrayHelper::map(
                City::find()->all(),
                'id',
                'name'
            )
        ) ?>
        <?= $form->field($model, 'address')->textarea(); ?>
        <?= $form->field($model, 'description')->textarea(); ?>
        <?= $form->field($model, 'map_latitude') ?>
        <?= $form->field($model, 'map_longitude') ?>
        <?= $form->field($model, 'sort_order') ?>


        <?php BackendWidget::end(); ?>
    </article>
    <?php ActiveForm::end(); ?>



    <?php if (!$model->isNewRecord): ?>
        <?php $form = ActiveForm::begin(['id' => 'wareHouseOpeningHours', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
        <article class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin([
                'title' => Yii::t('app', 'Warehouse Opening Hours'),
                'icon' => 'cubes',
                'footer' => $this->blocks['submit']
            ]); ?>

            <?= $form->field($wareHouseOpeningHours, 'opens'); ?>
            <?= $form->field($wareHouseOpeningHours, 'closes'); ?>
            <?= $form->field($wareHouseOpeningHours, 'break_from'); ?>
            <?= $form->field($wareHouseOpeningHours, 'break_to'); ?>

            <?= $form->field($wareHouseOpeningHours, 'monday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours, 'tuesday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours,
                'wednesday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours,
                'thursday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours, 'friday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours,
                'saturday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours, 'sunday')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?= $form->field($wareHouseOpeningHours, 'all_day')->widget(\kartik\switchinput\SwitchInput::className()) ?>




            <?php BackendWidget::end(); ?>
        </article>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>


</div>
<?php if (!$model->isNewRecord): ?>
    <div class="row">
        <article class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <?= \app\backend\widgets\GridView::widget(
                [
                    'dataProvider' => $warehousePhoneProvider,
                    'columns' => [
                        'name',
                        'phone',
                        [
                            'class' => 'app\backend\components\ActionColumn',
                            'buttons' => [
                                [
                                    'url' => 'edit-phone',
                                    'icon' => 'pencil',
                                    'class' => 'btn-primary',
                                    'label' => Yii::t('app', 'Edit'),
                                ],
                                [
                                    'url' => 'delete-phone',
                                    'icon' => 'trash-o',
                                    'class' => 'btn-danger',
                                    'label' => Yii::t('app', 'Delete'),
                                    'options' => [
                                        'data-action' => 'delete',
                                    ],
                                ]

                            ],
                        ],


                    ]
                ]
            );
            ?>
            <?= $this->render('form_edit_phone', ['warehousePhone' => $warehousePhone]) ?>

        </article>
        <article class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <?= \app\backend\widgets\GridView::widget(
                [
                    'dataProvider' => $warehouseEmailProvider,
                    'columns' => [
                        'name',
                        'email',
                        [
                            'class' => 'app\backend\components\ActionColumn',
                            'buttons' => [
                                [
                                    'url' => 'edit-email',
                                    'icon' => 'pencil',
                                    'class' => 'btn-primary',
                                    'label' => Yii::t('app', 'Edit'),
                                ],
                                [
                                    'url' => 'delete-filters',
                                    'icon' => 'trash-o',
                                    'class' => 'btn-danger',
                                    'label' => Yii::t('app', 'Delete'),
                                    'options' => [
                                        'data-action' => 'delete',
                                    ],
                                ]
                            ],
                        ],


                    ]
                ]
            );
            ?>

            <?= $this->render('form_edit_email', ['warehouseEmail' => $warehouseEmail]) ?>
        </article>

    </div>
<?php endif; ?>
