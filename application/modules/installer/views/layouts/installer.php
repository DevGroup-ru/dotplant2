<?php
use yii\helpers\Html;
use app\modules\installer\assets\InstallerAsset;

/* @var $this \yii\web\View */
/* @var $content string */

InstallerAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<header>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <a href="http://dotplant.ru/?utm_source=dotplant&utm_medium=cms&utm_term=logo&utm_campaign=installer" class="logo" target="_blank">
                    <img src="http://dotplant.ru/theme/img/logo.png" alt="DotPlant2 CMS"/>
                </a>
            </div>
        </div>
    </div>
</header>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="installer-box">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>





<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
