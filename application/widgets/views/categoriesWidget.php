<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\icons\Icon;

?>

<!-- begin categories widget -->

<?=
    yii\widgets\Menu::Widget([
        'items' => $possible_selections,
        'options' => [
            'class' => 'widget-categories',
        ],
    ])
?>

<!-- end of categories widget -->