<?php

use yii\helpers\Html;
use kartik\icons\Icon;

echo Html::a(
    Icon::show('refresh') . ' ' . Yii::t('app', 'Update extension'),
    ['/core/backend-extensions/update-extension', 'name' => $package->getName()],
    [
        'class' => 'btn btn-sm btn-success',
        'data-action' => 'post',
    ]
);

/** @var \Packagist\Api\Result\Package $package */
if (\app\modules\core\models\Extensions::isPackageActive($package->getName()) === true) {


    echo Html::a(
        Icon::show('power-off') . ' ' . Yii::t('app', 'Deactivate extension'),
        ['/core/backend-extensions/deactivate-extension', 'name' => $package->getName()],
        [
            'class' => 'btn btn-sm btn-warning',
            'data-action' => 'post',
        ]
    );
} else {
    echo Html::a(
        Icon::show('cloud-download') . ' ' . Yii::t('app', 'Activate extension'),
        ['/core/backend-extensions/install-extension', 'name' => $package->getName()],
        [
            'class' => 'btn btn-sm btn-primary',
            'data-action' => 'post',
        ]
    );
}

echo Html::a(
    Icon::show('trash-o') . ' ' . Yii::t('app', 'Remove extension'),
    ['/core/backend-extensions/remove-extension', 'name' => $package->getName()],
    [
        'class' => 'btn btn-sm btn-danger',
        'data-action' => 'post',
    ]
);