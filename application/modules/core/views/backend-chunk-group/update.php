<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\core\models\ContentBlockGroup */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Content Block Group',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Content Block Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="content-block-group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
