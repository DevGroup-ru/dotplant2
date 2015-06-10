<?php

/**
 * @var $error integer
 * @var $message string
 * @var $object \app\models\Object
 * @var $products \app\modules\shop\models\Product[]
 * @var $this \yii\web\View
 */

    $this->title = Yii::t('app', 'Products comparison');

    echo \yii\helpers\Html::tag('h1', $this->title);

    if (isset($error) && $error) {
        echo $message;
    } else {
        echo \app\modules\shop\widgets\ProductCompare::widget();
    }
?>
