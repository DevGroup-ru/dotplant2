<?php

/**
 * @var $breadcrumbs array
 * @var $model \app\modules\core\models\Page
 * @var $this \yii\web\View
 */

use yii\helpers\Html;

$this->params['breadcrumbs'] = $breadcrumbs;


?>

<?php
if ($this->blocks['h1']) {
    echo Html::tag('h1', Html::encode($this->blocks['h1']));
}
?>

<?=$this->blocks['content'];
