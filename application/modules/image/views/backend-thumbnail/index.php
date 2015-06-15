<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;

$this->title = Yii::t('app', 'Create thumbnail');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
BackendWidget::begin(['title' => Yii::t('app', 'Create thumbnails for all images')]);
?>
<?=
Html::a(
    Yii::t('app', 'Create Task'),
    ['recreate', 'param' => 'all'],
    ['class' => 'btn btn-success']
)
?>
<?php
BackendWidget::end();
