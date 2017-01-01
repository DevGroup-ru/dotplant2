<?php

use app\backend\widgets\BackendWidget;
use app\modules\image\models\Watermark;
use app\widgets\Alert;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\icons\Icon;
use app\modules\image\models\ThumbnailSize;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var \app\modules\image\models\ThumbnailSize $model
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
                <?=$form->field($model, 'quality')?>
                <?php
                if (Yii::$app->getModule('image')->useWatermark == 1) {
                    $watermarks = Watermark::find()->all();
                    echo $form->field($model, 'default_watermark_id')->radioList(
                        ArrayHelper::map(
                            $watermarks,
                            'id',
                            function ($watermarks) {
                                return Html::img($watermarks->file, ['style' => 'max-width:200px;']);
                            }
                        ),
                        [
                            "style" => "background-color: rgba(170, 170, 170, 0.5); padding: 10px;"
                        ]
                    );
                }
                ?>
                <?=$form->field($model, 'resize_mode')->dropDownList(ThumbnailSize::getResizeModes())?>
                <?php BackendWidget::end(); ?>
            </article>


        </div>
    </section>

<?php ActiveForm::end(); ?>