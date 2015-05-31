<?php

/**
 * @var $this \yii\web\View
 */

use app\assets\AppAsset;
use app\extensions\DefaultTheme\assets\DefaultThemeAsset;
use app\models\Config;
use \app\extensions\DefaultTheme\models\ThemeParts;
use kartik\helpers\Html;

AppAsset::register($this);
DefaultThemeAsset::register($this);

/** @var \app\extensions\DefaultTheme\Module $themeModule */
$themeModule = Yii::$app->getModule('DefaultTheme');

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="http://<?=Config::getValue('core.serverName', Yii::$app->request->serverName)?>">
    <title><?= Html::encode($this->title) ?></title>

    <?= Html::csrfMetaTags() ?>
    <?php $this->head(); ?>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body itemscope itemtype="http://schema.org/WebPage">
<?php $this->beginBody(); ?>
    <?= ThemeParts::renderPart('header') ?>

    <section class="subheader">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    subnav here
                </div>
            </div>
        </div>
    </section>
    <div class="container">
        <div class="row">

            <div class="col-md-4">


                <?php if (is_array(Yii::$app->session->get('comparisonProductList')) && count(Yii::$app->session->get('comparisonProductList')) > 0): ?>
                    <?=
                    \kartik\helpers\Html::a(
                        Yii::t(
                            'app',
                            'Compare products [{count}]',
                            [
                                'count' => count(Yii::$app->session->get('comparisonProductList')),
                            ]
                        ),
                        [
                            '/shop/product-compare/compare',
                        ],
                        [
                            'class' => 'btn',
                        ]
                    )
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
