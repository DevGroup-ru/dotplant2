<?php

/**
 * @var yii\web\View $this
 * @var \app\models\Submission $submission
 */

$this->title = Yii::t('app', 'Submission #'). $submission->id;
$this->params['breadcrumbs'][] = ['url' => ['/backend/form/index'], 'label' => Yii::t('app', 'Forms')];
$this->params['breadcrumbs'][] = [
    'url' => ['/backend/form/view', 'id' => $submission->form->id],
    'label' => Yii::t('app', 'Submissions')
];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php
    $form = \kartik\widgets\ActiveForm::begin(
        [
            'id' => 'submission-form',
            'type'=>\kartik\widgets\ActiveForm::TYPE_HORIZONTAL
        ]
    );
?>
<?= \app\properties\PropertiesWidget::widget([
        'model' => $submission,
        'form' => $form,
        'viewFile' => 'show-properties-widget',
    ]) ?>
<?php $form->end() ?>