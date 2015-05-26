<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use kartik\icons\Icon;
use \app\modules\core\models\Extensions;

$isAjax = Yii::$app->request->isAjax;
/** @var \Packagist\Api\Result\Package\Version $version */
/** @var \Packagist\Api\Result\Package $package */
?>

<div class="package-info">
    <h2>
        <?= Html::encode($package->getName()) ?>
        <small><?= $version->getVersion() ?></small>
    </h2>
    <?= Yii::t('app', 'Extension homepage')?>: <?= Html::a($version->getHomepage(), $version->getHomepage(), ['target'=>'_blank']) ?>
    <div class="description">
        <?= Html::encode($version->getDescription()) ?>
    </div>

    <div class="btn-group">
    <?php
        if (Extensions::isPackageInstalled($package->getName())) {
            echo $this->render('_installed-package', [
                'package' => $package,
            ]);
        } else {
            echo Html::a(
                Icon::show('cloud-download') . ' ' . Yii::t('app', 'Install extension'),
                ['/core/backend-extensions/install-extension', 'name' => $package->getName()],
                [
                    'class' => 'btn btn-primary',
                    'data-action' => 'post',
                ]
            );
        }
    ?>
    </div>

</div>
