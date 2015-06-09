<?php

use \kartik\icons\Icon;
use yii\helpers\Url;
use \kartik\form\ActiveForm;
use yii\helpers\Html;

/** @var \yii\web\View $this */
/** @var array $languages */
/** @var \yii\base\DynamicModel $model */

$this->title = Yii::t('app', 'Installer - Language selection');

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
            <?= Yii::t('app', 'Select language') ?>
        </h2>

        <?= $form->field($model, 'language')->dropDownList(
            [
                Yii::t('app', 'DotPlant2 translated languages:') => array_reduce(
                    $languages,
                    function($carry, $item) {
                        if ($item['translated'] === true) {
                            $carry[$item['language']] = $item['language'];
                        }
                        return $carry;
                    },
                    []
                ),
                Yii::t('app', 'Not translated but available in Yii2') => array_reduce(
                    $languages,
                    function($carry, $item) {
                        if ($item['translated'] === false) {
                            $carry[$item['language']] = $item['language'];
                        }
                        return $carry;
                    },
                    []
                ),
            ]

        ) ?>


    </div>
</div>


<div class="installer-controls">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-info btn-lg pull-left ladda-button" data-style="expand-right">
        <?= Icon::show('arrow-left') ?>
        <?= Yii::t('app', 'Back') ?>
    </a>

    <?=
    Html::submitButton(
        Yii::t('app', 'Next') . ' ' .Icon::show('arrow-right'),
        [
            'class' => 'btn btn-primary btn-lg pull-right ladda-button',
            'data-style' => 'expand-left',
        ]
    )
    ?>

</div>
<?php
ActiveForm::end();
?>