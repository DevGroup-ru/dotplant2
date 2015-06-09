<?php

use app\backend\widgets\BackendWidget;
use app\backend\components\ActiveForm;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\DateTimePicker;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\modules\shop\models\Discount */
/* @var $form ActiveForm */

$this->title = Yii::t('app', 'Discount edit');
$this->params['breadcrumbs'][] = ['url' => ['/shop/backend-discount/index'], 'label' => Yii::t('app', 'Discounts')];
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
        Yii::$app->request->get('returnUrl', ['/shop/backend-discount/index']),
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


<section id="widget-grid">
    <?php $form = ActiveForm::begin(['id' => 'discount-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <?php BackendWidget::begin([
                'title' => Yii::t('app', 'Discount'),
                'icon' => 'shekel',
                'footer' => $this->blocks['submit']
            ]); ?>
            <?= $form->field($model, 'name') ?>
            <?= $form->field($model, 'appliance')->dropDownList($model->applianceValues) ?>
            <?= $form->field($model, 'value') ?>
            <?= $form->field($model, 'apply_order_price_lg') ?>
            <?= $form->field($model, 'value_in_percent')->widget(\kartik\switchinput\SwitchInput::className()) ?>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
    <?php ActiveForm::end(); ?>

    <?php if (!$model->isNewRecord): ?>
        <div class="row">
            <?php foreach ($model->getTypeObjects() as $key => $object): ?>
                <?php /** @var \app\modules\shop\models\AbstractDiscountType $object */ ?>
                <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
                <article class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <?php BackendWidget::begin([
                        'title' => Yii::t('app', $object->type->name),
                        'icon' => 'cog',
                        'footer' => $this->blocks['submit']
                    ]); ?>
                    <?= \app\backend\widgets\GridView::widget(
                        [
                            'dataProvider' => $object::searchDiscountFilter($model->id),
                            'columns' => [
                                'fullName',
                                [
                                    'class' => 'app\backend\components\ActionColumn',
                                    'buttons' => [
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
                                    'url_append' => '&typeId=' . $object->type->id
                                ],
                            ]
                        ]
                    );
                    ?>

                    <?= $this->render($object->type->add_view, ['form' => $form, 'object' => $object]) ?>
                    <?php BackendWidget::end(); ?>
                </article>
                <?php if (($key + 1) % 2 === 0): ?>
                    <div class="clearfix"></div>
                <?php endif; ?>
                <?php ActiveForm::end(); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
