<?php

?>

<!-- begin categories widget -->
<?php Yii::beginProfile("CategoriesWidget - menu render"); ?>
<?=
    yii\widgets\Menu::Widget([
        'items' => $possible_selections,
        'options' => [
            'class' => 'widget-categories',
        ],
    ])
?>
<?php Yii::endProfile("CategoriesWidget - menu render"); ?>
<!-- end of categories widget -->