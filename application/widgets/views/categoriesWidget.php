<?php

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