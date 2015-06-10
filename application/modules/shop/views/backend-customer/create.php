<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Customer $model
 */

use \app\backend\widgets\BackendWidget;

    $this->title = Yii::t('app', 'Customer edit');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customers'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'customer-form',
        'action' => \yii\helpers\Url::toRoute(['create']),
        'layout' => 'horizontal',
    ]);
    BackendWidget::begin([
        'icon' => 'user',
        'title' => Yii::t('app', 'Customer create'),
        'footer' => \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']),
    ]);

    $_jsTemplateResultFunc = <<< 'JSCODE'
function (data) {
    if (data.loading) return data.text;
    var tpl = '<div class="s2customer-result">' +
        '<strong>' + (data.username || '') + '</strong>' +
        '<div>' + (data.first_name || '') + ' (' + (data.email || '') + ')</div>' +
        '</div>';
    return tpl;
}
JSCODE;

    echo \app\backend\widgets\Select2Ajax::widget([
        'initialData' => [$model->user_id => null !== $model->user ? $model->user->username : 'Guest'],
        'model' => $model,
        'modelAttribute' => 'user_id',
        'form' => $form,
        'multiple' => false,
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-user']),
        'pluginOptions' => [
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.username || data.text;}'),
        ]
    ]);
    echo \app\modules\shop\widgets\Customer::widget([
        'viewFile' => 'customer/inherit_form',
        'form' => $form,
        'model' => $model,
    ]);

    BackendWidget::end();
    $form->end();