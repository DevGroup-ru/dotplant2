<?php
/**
 * @var yii\web\View $this
 * @var app\models\SpamChecker $model
 */

use app\backend\widgets\BackendWidget;
use app\widgets\Alert;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Spam checker edit');
$this->params['breadcrumbs'][] = ['url' => ['index'], 'label' => Yii::t('app', 'Spam checker')];
$this->params['breadcrumbs'][] = $this->title;

echo Alert::widget(['id' => 'alert',]);

$form = ActiveForm::begin(['id' => 'form-form', 'type' => ActiveForm::TYPE_HORIZONTAL]);

$this->beginBlock('submit');
echo Html::beginTag('div', ['class' => 'form-group no-margin']);
echo Html::a(
    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
    Yii::$app->request->get('returnUrl', ['/backend/form/index', 'id' => $model->id]),
    ['class' => 'btn btn-danger']
);

if ($model->isNewRecord) {
    echo Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go next'),
        [
            'class' => 'btn btn-success',
            'name' => 'action',
            'value' => 'next',
        ]
    );
}

echo Html::submitButton(
    Icon::show('save') . Yii::t('app', 'Save & Go back'),
    [
        'class' => 'btn btn-warning',
        'name' => 'action',
        'value' => 'back',
    ]
);
echo Html::submitButton(
    Icon::show('save') . Yii::t('app', 'Save'),
    [
        'class' => 'btn btn-primary',
        'name' => 'action',
        'value' => 'save',
    ]
);
echo Html::endTag('div');
$this->endBlock('submit');

echo Html::beginTag('section', ['id' => 'widget-grid']);
echo Html::beginTag('div', ['class' => 'row']);
echo Html::beginTag('article', ['class' => 'col-xs-12 col-sm-6 col-md-6 col-lg-6']);
BackendWidget::begin(
    [
        'title' => Yii::t('app', 'Spam checker'),
        'icon' => 'list-ul',
        'footer' => $this->blocks['submit']
    ]
);
echo $form->field($model, 'name');
echo $form->field($model, 'behavior');
echo $form->field($model, 'api_key');
echo $form->field($model, 'author_field');
echo $form->field($model, 'content_field');
BackendWidget::end();
echo Html::endTag('article');
echo Html::endTag('div');
echo Html::endTag('section');

ActiveForm::end();