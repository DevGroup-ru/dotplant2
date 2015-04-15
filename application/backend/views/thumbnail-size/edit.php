<?php

use app\backend\widgets\BackendWidget;
use app\models\Config;
use app\widgets\Alert;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var \app\models\ThumbnailSize $model
 */

$this->title = Yii::t('app', 'Thumbnail size edit');
$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => Yii::t('app', 'Thumbnail size')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?=Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(['id' => 'form-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>
    <div class="form-group no-margin">
        <?=
        Html::a(
            Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
            Yii::$app->request->get('returnUrl', ['index', 'id' => $model->id]),
            ['class' => 'btn btn-danger']
        )
        ?>

        <?php if ($model->isNewRecord): ?>
            <?=Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save & Go next'),
                [
                    'class' => 'btn btn-success',
                    'name' => 'action',
                    'value' => 'next',
                ]
            )?>
        <?php endif; ?>

        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go back'),
            [
                'class' => 'btn btn-warning',
                'name' => 'action',
                'value' => 'back',
            ]
        )
        ?>

        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            [
                'class' => 'btn btn-primary',
                'name' => 'action',
                'value' => 'save',
            ]
        )
        ?>
    </div>
<?php $this->endBlock('submit'); ?>

    <section id="widget-grid">
        <div class="row">

            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php BackendWidget::begin(
                    [
                        'title' => Yii::t('app', 'Thumbnail size'),
                        'icon' => 'list-ul',
                        'footer' => $this->blocks['submit']
                    ]
                ); ?>
                <?=$form->field($model, 'width')?>
                <?=$form->field($model, 'height')?>
                <?php
                $useWatermark = Config::getValue('image.useWatermark', 0);
                if ($useWatermark == 1) {

                }
                ?>
                <?php BackendWidget::end(); ?>
            </article>


        </div>
    </section>

<?php ActiveForm::end(); ?>