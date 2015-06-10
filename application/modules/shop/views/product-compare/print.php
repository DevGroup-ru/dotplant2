<?php

/**
 * @var $error integer
 * @var $message string
 * @var $object \app\models\Object
 * @var $products \app\modules\shop\models\Product[]
 * @var $this \yii\web\View
 */

$this->title = Yii::t('app', 'Products comparison');

/** @var \app\extensions\DefaultTheme\Module $defaultTheme */
$defaultTheme = Yii::$app->getModule('DefaultTheme');
echo null !== $defaultTheme
    ? \yii\helpers\Html::tag('h3', $defaultTheme->siteName)
    : '';
echo \yii\helpers\Html::tag('p', \yii\helpers\Url::to('/', true));

if (isset($error) && $error) {
    echo $message;
} else {
    echo \app\modules\shop\widgets\ProductCompare::widget([
        'viewFile' => 'product-compare/list-print',
    ]);
}
?>
