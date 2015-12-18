<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\core\models\Wysiwyg */

$this->title = Yii::t('app', 'Create Wysiwyg');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Wysiwygs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wysiwyg-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
