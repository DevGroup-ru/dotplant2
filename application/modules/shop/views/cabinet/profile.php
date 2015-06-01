<?php
/**
 * @var \yii\web\View $this
 */
?>
<?php
    if (Yii::$app->user->isGuest) {
        echo '<p>'. Yii::t('app', 'Please register to save your profile')  .'</p>';
    } else {
        $customer = null === \app\modules\shop\models\Customer::getCustomerByUserId(Yii::$app->user->id)
            ? \app\modules\shop\models\Customer::createEmptyCustomer(Yii::$app->user->id, false)
            : \app\modules\shop\models\Customer::getCustomerByUserId(Yii::$app->user->id);
        echo \app\modules\shop\widgets\Customer::widget([
            'viewFile' => 'customer/profile',
            'model' => $customer,
            'formAction' => \yii\helpers\Url::toRoute(['/shop/cabinet/update'], true),
        ]);
    }
?>
