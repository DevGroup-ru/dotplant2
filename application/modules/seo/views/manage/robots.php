<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Config $model
 */

$this->title = $model->key;
$this->params['breadcrumbs'][] = ['label' => 'SEO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>

<?= $this->render('_configForm', ['model' => $model]); ?>
