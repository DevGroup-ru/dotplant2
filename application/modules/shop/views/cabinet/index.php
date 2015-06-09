<?php
/**
 * @var \yii\web\View $this
 */
use \yii\helpers\Url;
use \yii\helpers\Html;
?>
<ul>
    <li><a href="<?= Url::to(['/shop/orders/list'])?>"><?= Yii::t('app', 'Orders list'); ?></a></li>
    <?= Yii::$app->user->isGuest ? '' : Html::tag('li', Html::a(Yii::t('app', 'Customer profile'), Url::to(['/shop/cabinet/profile']))); ?>
</ul>
