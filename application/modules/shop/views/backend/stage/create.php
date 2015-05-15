<?php
/**
 * @var \app\modules\shop\models\OrderStage $model
 */

    use app\backend\widgets\BackendWidget;
    use yii\bootstrap\ActiveForm;
?>

<?= app\widgets\Alert::widget(['id' => 'alert']); ?>

<?php
    $form = ActiveForm::begin([
        'id' => 'shop-stage-create',
        'layout' => 'horizontal',
    ]);
    BackendWidget::begin([
        'title' => Yii::t('app', 'Stage settings'),
        'icon' => 'cogs',
        'footer' => \yii\helpers\Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success'])
    ]);
?>

    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'name_frontend'); ?>
    <?= $form->field($model, 'name_short'); ?>
    <?= $form->field($model, 'event_name'); ?>
    <?= $form->field($model, 'is_initial')->checkbox(); ?>
    <?= $form->field($model, 'is_buyer_stage')->checkbox(); ?>
    <?= $form->field($model, 'become_non_temporary')->checkbox(); ?>
    <?= $form->field($model, 'is_in_cart')->checkbox(); ?>
    <?= $form->field($model, 'immutable_by_user')->checkbox(); ?>
    <?= $form->field($model, 'immutable_by_manager')->checkbox(); ?>
    <?= $form->field($model, 'immutable_by_assigned')->checkbox(); ?>
    <?= $form->field($model, 'reach_goal_ym'); ?>
    <?= $form->field($model, 'reach_goal_ga'); ?>

<?php
    BackendWidget::end();
    $form->end();
?>
