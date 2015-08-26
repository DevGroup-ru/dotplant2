<?php

use \kartik\icons\Icon;
use yii\helpers\Url;
use \kartik\form\ActiveForm;
use yii\helpers\Html;
/** @var \app\modules\installer\models\FinalStep $model */
/** @var \yii\web\View $this */
/** @var array $cacheClasses */

$this->title = Yii::t('app', 'Installer - Final step');

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
            <?= Yii::t('app', 'Site settings:') ?>
        </h2>

        <?= $form->field($model, 'serverName')->hint(Yii::t('app', 'This is the hostname that your site will be using.')) ?>

        <h2>
            <?= Yii::t('app', 'Cache settings:') ?>
        </h2>
        <?=
        \kartik\widgets\TypeaheadBasic::widget([
            'model' => $model,
            'attribute' => "cacheClass",
            'data' => $cacheClasses,
            'pluginOptions' => ['highlight'=>true, 'limit'=>50],
        ])
        ?>
        <?= $form->field($model, 'useMemcached')->checkbox() ?>
        <?= $form->field($model, 'keyPrefix') ?>

    </div>
</div>


<div class="installer-controls">
    <a href="<?= Url::to(['admin-user']) ?>" class="btn btn-info btn-lg pull-left ladda-button" data-style="expand-left">
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