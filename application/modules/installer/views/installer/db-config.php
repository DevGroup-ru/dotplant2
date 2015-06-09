<?php

use \kartik\icons\Icon;
use yii\helpers\Url;
use \kartik\form\ActiveForm;
use yii\helpers\Html;

/** @var \yii\web\View $this */
/** @var array $config */
/** @var \app\modules\installer\models\DbConfig $model */

$this->title = Yii::t('app', 'Installer - Database configuration');

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
            <?= Yii::t('app', 'Database settings:') ?>
        </h2>


        <?= $form->field($model, 'db_host') ?>
        <?= $form->field($model, 'db_name') ?>
        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password') ?>
        <?= $form->field($model, 'enableSchemaCache')->checkbox() ?>
        <?= $form->field($model, 'schemaCacheDuration') ?>
        <?= $form->field($model, 'schemaCache') ?>
        <?=
        Html::submitButton(
            Icon::show('check') .' ' . Yii::t('app', 'Test connection'),
            [
                'class' => 'btn btn-success pull-right ladda-button',
                'data-style' => 'expand-right',
                'name' => 'check-connection',
            ]
        )
        ?>

    </div>
</div>


<div class="installer-controls">
    <a href="<?= Url::to(['language']) ?>" class="btn btn-info btn-lg pull-left ladda-button" data-style="expand-right">
        <?= Icon::show('arrow-left') ?>
        <?= Yii::t('app', 'Back') ?>
    </a>
    <?php if ($config['connectionOk']): ?>
        <?=
        Html::submitButton(
            Yii::t('app', 'Next') . ' ' .Icon::show('arrow-right'),
            [
                'class' => 'btn btn-primary btn-lg pull-right ladda-button',
                'data-style' => 'expand-left',
                'name' => 'next',
            ]
        )
        ?>
    <?php endif; ?>
</div>
<?php
ActiveForm::end();
?>
<?php
$js = <<<JS
Ladda.bind( 'input[type=submit]' );
Ladda.bind( '.btn' );
JS;
$this->registerJs($js);