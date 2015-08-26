<?php

use \kartik\icons\Icon;
use yii\helpers\Url;
use \kartik\form\ActiveForm;
use yii\helpers\Html;
/** @var \app\modules\installer\models\MigrateModel $model */
/** @var \yii\web\View $this */
/** @var \Symfony\Component\Process\Process $process */
/** @var boolean $check */
/** @var string $commandToRun */

$this->title = Yii::t('app', 'Installer - Database migration');

?>
<h1>
    <?= $this->title ?>
</h1>

<?= \app\widgets\Alert::widget() ?>
<?php
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
]);
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <h2>
            <?= Yii::t('app', 'Migration settings:') ?>
        </h2>

        <?= $form->field($model, 'updateComposer')->checkbox() ?>
        <?= $form->field($model, 'composerHomeDirectory')->hint(Yii::t('app', 'Fill it with your users\'s home directory followed by "/.composer/" (ie. /home/user/.composer/).')) ?>
        <?= $form->field($model, 'ignore_time_limit_warning')->checkbox() ?>
        <?= $form->field($model, 'manual_migration_run')->checkbox() ?>

<h4><?= Yii::t('app', 'Command for manual run: ') ?></h4>
<pre>
<?= $commandToRun ?>
</pre>
<?php if ($process->getExitCode()!==null): ?>
<h4><?= Yii::t('app', 'Migration command output') ?></h4>
<div>
    <strong><?= Yii::t('app', 'Exit code:') ?></strong> <?= $process->getExitCode() ?>
</div>
<div>
    <strong><?= Yii::t('app', 'STD err:') ?></strong>
    <?= '<pre>'.$process->getErrorOutput().'</pre>' ?>
</div>
<div>
    <strong><?= Yii::t('app', 'STD out:') ?></strong>
    <?= '<pre>'.$process->getOutput().'</pre>' ?>
</div>
<?php endif; ?>

    </div>
</div>


<div class="installer-controls">
    <a href="<?= Url::to(['db-config']) ?>" class="btn btn-info btn-lg pull-left ladda-button" data-style="expand-left">
        <?= Icon::show('arrow-left') ?>
        <?= Yii::t('app', 'Back') ?>
    </a>

    <?=
    Html::submitButton(
        Yii::t('app', 'Next') .' ' . Icon::show('arrow-right'),
        [
            'class' => 'btn btn-primary btn-lg pull-right ladda-button',
            'data-style' => 'expand-left',
        ]
    )
    ?>

</div>
<?php
ActiveForm::end();
$js = <<<JS
Ladda.bind( 'input[type=submit]' );
Ladda.bind( '.btn' );
JS;
$this->registerJs($js);
?>