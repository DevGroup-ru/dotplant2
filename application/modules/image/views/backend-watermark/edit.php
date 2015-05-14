<?php

use app\backend\widgets\BackendWidget;
use app\modules\image\models\Watermark;
use app\widgets\Alert;
use kartik\widgets\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var \app\modules\image\models\Watermark $model
 */

$this->title = Yii::t('app', 'Watermark edit');
$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => Yii::t('app', 'Thumbnail size')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?=Alert::widget(
    [
        'id' => 'alert',
    ]
);?>

<?php $form = ActiveForm::begin(
    ['id' => 'form-form', 'type' => ActiveForm::TYPE_HORIZONTAL, 'options' => ['enctype' => 'multipart/form-data']]
); ?>

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
                        'title' => Yii::t('app', 'Watermark'),
                        'icon' => 'list-ul',
                        'footer' => $this->blocks['submit']
                    ]
                );
                $options = [];
                if ($model->isNewRecord === false) {
                    $this->registerCss('span.file-input > div.file-preview {display:block;}');
                    $this->registerCss('.file-preview img {max-width:100%;}');
                    $options = ['initialPreview' => [Html::img($model->file)]];
                }
                echo $form->field($model, 'image')->widget(
                    FileInput::classname(),
                    [
                        'options' => ['accept' => 'image/*'],
                        'pluginOptions' => $options,
                    ]
                );
                echo $form->field($model, 'position')->dropDownList(Watermark::getPositions());
                BackendWidget::end(); ?>
            </article>


        </div>
    </section>

<?php ActiveForm::end();