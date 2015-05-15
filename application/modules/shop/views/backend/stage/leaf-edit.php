<?php
/**
 * @var \app\modules\shop\models\OrderStageLeaf $model
 * @var array $stages
 */

    use app\backend\widgets\BackendWidget;
    use yii\bootstrap\ActiveForm;
    use \yii\helpers\Url;

    $this->title = Yii::t('app', 'Edit order stage leaf');
    $this->params['breadcrumbs'] = [
        [
            'label' => Yii::t('app', 'Shop backend'),
            'url' => Url::to(['/shop/backend/index']),
        ],
        [
            'label' => Yii::t('app', 'Order stages leafs'),
            'url' => Url::to(['/shop/backend/stage-leaf-index']),
        ],
        $this->title,
    ];
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

    <?= $form->field($model, 'stage_from_id')->dropDownList($stages); ?>
    <?= $form->field($model, 'stage_to_id')->dropDownList($stages); ?>
    <?= $form->field($model, 'sort_order'); ?>
    <?= $form->field($model, 'button_label'); ?>
    <?= $form->field($model, 'button_css_class'); ?>
    <?= $form->field($model, 'notify_buyer')->checkbox(); ?>
    <?= $form->field($model, 'buyer_notification_view'); ?>
    <?= $form->field($model, 'notify_manager')->checkbox(); ?>
    <?= $form->field($model, 'manager_notification_view'); ?>
    <?= $form->field($model, 'assign_to_user_id'); ?>
    <?= $form->field($model, 'assign_to_role'); ?>
    <?= $form->field($model, 'notify_new_assigned_user')->checkbox(); ?>
    <?= $form->field($model, 'role_assignment_policy'); ?>
    <?= $form->field($model, 'event_name'); ?>

<?php
    BackendWidget::end();
    $form->end();
?>
