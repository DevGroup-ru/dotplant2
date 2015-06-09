<?php
/**
 * @var \yii\web\View $this
 */

use \app\backend\widgets\BackendWidget;

    $this->title = Yii::t('app', 'Customer edit');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Customers'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;

    $model = \app\modules\shop\models\Customer::createEmptyCustomer();

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
    echo \app\modules\shop\widgets\Customer::widget([
        'viewFile' => 'customer/inherit_form',
        'form' => $form,
        'model' => $model,
    ]);
    BackendWidget::end();

    $form->end();