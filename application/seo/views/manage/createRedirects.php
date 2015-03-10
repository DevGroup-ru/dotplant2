<?php

use yii\helpers\Html;
use kartik\icons\Icon;
/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Create Redirects');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SEO'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Redirects'), 'url' => ['redirect']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Create');
?>
<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/seo/manage/redirect']),
        ['class' => 'btn btn-danger']
    )
    ?>

    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'back',
        ]
    )
    ?>



</div>
<?php $this->endBlock('submit'); ?>


<div class="row">
    <div class="col-xs-12">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
</div>
<div class="row">
    <div class="redirects-create">
        <?= Html::beginForm() ?>
            <div class="form-group">
                <label for="static"><?= Yii::t('app', 'Static redirects') ?></label>
                <textarea name="redirects[static]" class="form-control" rows="15" id="static" placeholder="<?= Yii::t('app', 'Enter redirects') ?>"></textarea>
            </div>
            <div class="form-group">
                <label for="regular"><?= Yii::t('app', 'Regular redirects') ?></label>
                <textarea name="redirects[regular]" class="form-control" rows="15" id="regular" placeholder="<?= Yii::t('app', 'Enter redirects') ?>"></textarea>
            </div>
        <?= $this->blocks['submit'] ?>
        <?= Html::endForm() ?>
    </div>
</div>