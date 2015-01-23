<?php

/**
 * @var $this \yii\web\View
 */

use app\assets\AppAsset;
use app\models\Config;
use kartik\helpers\Html;

AppAsset::register($this);
unset($this->assetBundles['yii\bootstrap\BootstrapAsset']);

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <base href="http://<?=Config::getValue('core.serverName', Yii::$app->request->serverName)?>">
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <?php $this->head(); ?>
    <!-- Bootstrap style -->
    <link id="callCss" rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/demo/bootshop/bootstrap.min.css" media="screen"/>
    <link href="<?= Yii::$app->request->baseUrl ?>/demo/css/base.css" rel="stylesheet" media="screen"/>
    <!-- Bootstrap style responsive -->
    <link href="<?= Yii::$app->request->baseUrl ?>/demo/css/bootstrap-responsive.min.css" rel="stylesheet"/>
    <link href="<?= Yii::$app->request->baseUrl ?>/demo/css/font-awesome.css" rel="stylesheet" type="text/css">
    <!-- Google-code-prettify -->
    <link href="<?= Yii::$app->request->baseUrl ?>/demo/js/google-code-prettify/prettify.css" rel="stylesheet"/>
    <!-- fav and touch icons -->
    <link rel="shortcut icon" href="<?= Yii::$app->request->baseUrl ?>/demo/images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?= Yii::$app->request->baseUrl ?>/demo/images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= Yii::$app->request->baseUrl ?>/demo/images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= Yii::$app->request->baseUrl ?>/demo/images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?= Yii::$app->request->baseUrl ?>/demo/images/ico/apple-touch-icon-57-precomposed.png">
    <style type="text/css" id="enject"></style>
    <link href='<?= Yii::$app->request->baseUrl ?>/css/demo.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript">
        var  baseUrl = '<?= Yii::$app->request->baseUrl ?>';
    </script>
</head>
<body itemscope itemtype="http://schema.org/WebPage">
<?php $this->beginBody(); ?>
<div id="header">
    <div class="container">
        <div id="welcomeLine" class="row">
            <div class="span6">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Yii::t('app', 'Hello') ?>
                    <strong><?= Html::a(Yii::$app->user->identity->username, ['/cabinet']) ?>!</strong>
                <?php endif; ?>
            </div>
            <div class="span3">
                <?php if (is_array(Yii::$app->session->get('comparisonProductList')) && count(Yii::$app->session->get('comparisonProductList')) > 0): ?>
                    <?=
                    \kartik\helpers\Html::a(
                        Yii::t(
                            'shop',
                            'Compare products [{count}]',
                            [
                                'count' => count(Yii::$app->session->get('comparisonProductList')),
                            ]
                        ),
                        [
                            '/product-compare/compare',
                        ],
                        [
                            'class' => 'btn',
                        ]
                    )
                    ?>
                <?php endif; ?>
            </div>
            <?php
                echo \app\widgets\CartInfo::widget()
            ?>
        </div>
        <div id="logoArea" class="navbar navbar-inverse">
            <a id="smallScreen" data-target="#topMenu" data-toggle="collapse" class="btn btn-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-inner">
                <a class="brand" href="<?= Yii::$app->homeUrl ?>"><img src="<?= Yii::$app->request->baseUrl ?>/demo/images/logo.png" alt="DotPlant" /></a>
                <!--                -->
                <?php
                    $form = \yii\widgets\ActiveForm::begin(
                        [
                            'action' => ['/default/search'],
                            'id' => 'search-form',
                            'method' => 'get',
                            'options' => [
                                'class' => 'form-inline navbar-search',
                            ],
                        ]
                    );
                    $model = new \app\models\Search;
                    $model->load(Yii::$app->request->get());
                    echo $form->field(
                        $model,
                        'q',
                        [
                            'options' => [
                                'class' => '',
                                'tag' => 'span',
                            ],
                            'template' => '{input}',
                        ]
                    )->widget(
                        \app\widgets\AutoCompleteSearch::className(),
                        [
                            'options' => [
                                'class' => 'srchTxt',
                                'placeholder' => Yii::t('app', 'Search'),
                            ]
                        ]
                    );
                    echo Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']);
                    \yii\widgets\ActiveForm::end();
                ?>
                <?=
                    \app\widgets\navigation\NavigationWidget::widget(
                        [
                            'rootId' => 1,
                            'appendItems' => [
                                [
                                    'label' => Yii::$app->user->isGuest
                                        ? Yii::t('app', 'Login')
                                        : Yii::t('app', 'Logout'),
                                    'itemOptions' => [
                                        'class' => 'btn btn-large btn-success',
                                    ],
                                    'url' => Yii::$app->request->baseUrl.(Yii::$app->user->isGuest ? '/login' : '/logout'),
                                ],
                            ],
                            'options' => [
                                'class' => 'nav pull-right',
                            ],
                        ]
                    )
                ?>
            </div>
        </div>
    </div>
</div>