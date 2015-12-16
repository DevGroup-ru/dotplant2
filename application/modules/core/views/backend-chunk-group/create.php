<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\core\models\ContentBlockGroup */

$this->title = Yii::t('app', 'Create Content Block Group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Content Block Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-block-group-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
