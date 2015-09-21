<?php

/**
 * @var yii\web\View $this
 * @var \app\models\Submission $submission
 */
use app\backend\widgets\BackendWidget;
use app\backend\components\ActiveForm;
use app\models\Property;
use app\models\Object;
use app\models\Form;
use app\models\PropertyGroup;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Submission #'). $submission->id;
$this->params['breadcrumbs'][] = ['url' => ['/backend/form/index'], 'label' => Yii::t('app', 'Forms')];
$this->params['breadcrumbs'][] = [
    'url' => ['/backend/form/view', 'id' => $submission->form->id],
    'label' => Yii::t('app', 'Submissions')
];
$this->params['breadcrumbs'][] = $this->title;
$formObject = Object::getForClass(Form::className());
$groups = PropertyGroup::getForModel($formObject->id, $submission->form_id);
$submission->getPropertyGroups(true);
?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'form-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
<?= \app\backend\components\Helper::saveButtons($submission) ?>
<?php $this->endBlock(); ?>
<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(
                ['title' => Yii::t('app', 'Submission details'), 'icon' => 'cogs', 'footer' => $this->blocks['submit']]
            ); ?>

            <?=$form->field($submission, 'date_received')?>
            <?=$form->field($submission, 'ip')?>
            <?=$form->field($submission, 'user_agent')?>
            <?=$form->field($submission, 'submission_referrer')?>

            <?php BackendWidget::end(); ?>
        </article>
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?=
            \app\properties\PropertiesWidget::widget(
                [
                    'model' => $submission,
                    'form' => $form,
                ]
            );
            ?>


        </article>
    </div>
</section>
<?php ActiveForm::end(); ?>

