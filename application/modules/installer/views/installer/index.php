<?php

use \kartik\icons\Icon;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var array $file_permissions */

$this->title = Yii::t('app', 'Installer');
$permissions_ok = true;
?>
<h1>
    <?= Yii::t('app', 'Installation') ?>
</h1>

<strong>
    <?= Yii::t('app', 'File permissions:') ?>
</strong>

<div class="scrollable">
    <?php
        foreach ($file_permissions as $file => $result) {
            $permissions_ok = $permissions_ok && $result;
            if ($result) {
                $result = '<span class="label label-success">OK</span>';
            } else {
                $result = '<span class="label label-warning">Error</span>';
            }
            echo $file . ' - ' . $result . "<br>";
        }
    ?>
</div>
<?php if ($permissions_ok === false): ?>
<div class="alert alert-warning">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<strong>
        <?= Icon::show('exclamation-riangle') ?>
        <?= Yii::t('app', 'Warning!') ?>
    </strong>
    <?= Yii::t('app', 'Some files are not writeable.') ?>
    <?= Yii::t('app', 'Continue at your own risk.') ?>
</div>
<?php endif; ?>

<div class="installer-controls">
    <a href="<?= Url::to(['language']) ?>" class="btn btn-primary btn-lg pull-right ladda-button" data-style="expand-left">
        <?= Yii::t('app', 'Next') ?>
        <?= Icon::show('arrow-right') ?>
    </a>
</div>
