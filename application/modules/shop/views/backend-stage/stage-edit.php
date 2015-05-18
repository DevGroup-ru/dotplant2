<?php
/**
 * @var \app\modules\shop\models\OrderStage $model
 * @var array $events
 */

use app\backend\widgets\BackendWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

    $this->title = Yii::t('app', 'Edit order stage');
    $this->params['breadcrumbs'] = [
        [
            'label' => Yii::t('app', 'Order stage subsystem'),
            'url' => Url::to(['index']),
        ],
        [
            'label' => Yii::t('app', 'Order stages'),
            'url' => Url::to(['stage-index']),
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
        'title' => Yii::t('app', 'Edit order stage'),
        'icon' => 'cogs',
        'footer' => \yii\helpers\Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success'])
    ]);
?>

    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'name_frontend'); ?>
    <?= $form->field($model, 'name_short'); ?>
    <?= $form->field($model, 'event_name')->dropDownList($events); ?>
    <?= $form->field($model, 'view'); ?>
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
