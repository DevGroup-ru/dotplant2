<?php

/**
 * @var $breadcrumbs array
 * @var $model \app\models\Page
 * @var $this \yii\web\View
 */

use yii\helpers\Html;

$this->params['breadcrumbs'] = $breadcrumbs;
if ($model->title) {
    $this->title = $model->title;
} elseif ($model->h1) {
    $this->title = $model->h1;
}

?>

<?php
if ($model->h1) {
    echo Html::tag('h1', Html::encode($model->h1));
}
?>

<?= $model->content;
