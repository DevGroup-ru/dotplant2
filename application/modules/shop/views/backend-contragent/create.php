<?php

use app\backend\widgets\BackendWidget;
use app\components\Helper;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Contragent $model
 */

    $this->title = Yii::t('app', 'Contragent edit');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contragents'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'contragent-form',
        'action' => \yii\helpers\Url::toRoute(['create', 'customer' => $model->customer_id]),
        'layout' => 'horizontal',
    ]);
    BackendWidget::begin([
        'icon' => 'user',
        'title' => Yii::t('app', 'Contragent edit'),
        'footer' => \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']),
    ]);

    $_jsTemplateResultFunc = <<< 'JSCODE'
    function (data) {
        if (data.loading) return data.text;
        var tpl = '<div class="s2contragent-result">' +
            '<strong>' + (data.first_name || '') + ' ' + (data.middle_name || '') + ' ' + (data.last_name || '') + ' ' + '</strong>' +
            '<div>' + (data.email || '') + ' (' + (data.phone || '') + ')</div>' +
            '</div>';
        return tpl;
    }
JSCODE;

    echo \app\backend\widgets\Select2Ajax::widget([
        'initialData' => [$model->customer_id => null !== $model->customer ? $model->customer->first_name : 'Guest'],
        'model' => $model,
        'modelAttribute' => 'customer_id',
        'form' => $form,
        'multiple' => false,
        'searchUrl' => \yii\helpers\Url::toRoute(['ajax-customer']),
        'pluginOptions' => [
            'allowClear' => false,
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) {return markup;}'),
            'templateResult' => new \yii\web\JsExpression($_jsTemplateResultFunc),
            'templateSelection' => new \yii\web\JsExpression('function (data) {return data.first_name || data.text;}'),
        ]
    ]);
    echo \app\modules\shop\widgets\Contragent::widget([
        'viewFile' => 'contragent/inherit_form',
        'model' => $model,
        'form' => $form,
        'additional' => [
            'hideHeader' => true,
        ],
    ]);

    BackendWidget::end();
    $form->end();
