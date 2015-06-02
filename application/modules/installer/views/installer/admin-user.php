<?php

use \kartik\icons\Icon;
use yii\helpers\Url;
use \kartik\form\ActiveForm;
use yii\helpers\Html;
/** @var \app\modules\installer\models\AdminUser $model */
/** @var \yii\web\View $this */


$this->title = Yii::t('app', 'Installer - Admin user');

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

        <?= $form->field($model, 'username') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'email') ?>

    </div>
</div>


<div class="installer-controls">
    <a href="<?= Url::to(['migrate']) ?>" class="btn btn-info btn-lg pull-left ladda-button" data-style="expand-left">
        <?= Icon::show('arrow-left') ?>
        <?= Yii::t('app', 'Back') ?>
    </a>

    <?=
    Html::submitButton(
        Yii::t('app', 'Next') .' ' . Icon::show('arrow-right'),
        [
            'class' => 'btn btn-primary btn-lg pull-right',
        ]
    )
    ?>

</div>
<?php
ActiveForm::end();
$js = <<<JS

JS;
$this->registerJs($js);
?>