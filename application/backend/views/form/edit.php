<?php

use app\backend\widgets\BackendWidget;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var \app\models\Form $model
 */

$this->title = Yii::t('app', 'Form edit');
$this->params['breadcrumbs'][] = ['url' => ['/backend/form/index'], 'label' => Yii::t('app', 'Forms')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
        'id' => 'alert',
    ]); ?>

<?php $form = ActiveForm::begin(['id' => 'form-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/form/index', 'id' => $model->id]),
        ['class' => 'btn btn-danger']
    )
    ?>

    <?php if ($model->isNewRecord): ?>
        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )
        ?>
    <?php endif; ?>

    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    )
    ?>

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
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin([
                    'title'=> Yii::t('app', 'Form'),
                    'icon'=>'list-ul',
                    'footer'=>$this->blocks['submit']
                ]); ?>
            <?= $form->field($model, 'name')?>
            <?= $form->field($model, 'form_view')?>
            <?= $form->field($model, 'form_success_view')?>
            <?= $form->field($model, 'email_notification_addresses')?>
            <?= $form->field($model, 'email_notification_view')?>
            <?= $form->field($model, 'form_open_analytics_action_id')?>
            <?= $form->field($model, 'form_submit_analytics_action_id')?>
            <?php BackendWidget::end(); ?>
        </article>

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(
                [
                    'title'=> Yii::t('app', 'Property Groups'),
                    'icon'=>'cubes',
                    'footer'=>$this->blocks['submit']
                ]
            ); ?>

            <?= $form->field($model, 'properties')->widget('app\widgets\MultiSelect', [
                    'defaultLabel' => Yii::t('app', 'Choose item'),
                    'items' => $items,
                    'selectedItems' => $selected,
                    'sortable' => false,
                    'ajax' => false,
                ]) ?>

            <?php BackendWidget::end(); ?>
        </article>

    </div>
</section>

<?php ActiveForm::end(); ?>
