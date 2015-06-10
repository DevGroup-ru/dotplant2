<?php


/**
 * @var \yii\web\View $this
 * @var string $content
 */
    \app\assets\AppAsset::register($this);
    $this->beginPage();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="http://<?=Yii::$app->getModule('core')->serverName?>">
    <title><?= \yii\helpers\Html::encode($this->title) ?></title>

    <?= \yii\helpers\Html::csrfMetaTags() ?>
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
    <?= $content; ?>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>

