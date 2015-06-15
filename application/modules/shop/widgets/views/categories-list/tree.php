<?php
/**
 * @var \yii\web\View $this
 * @var array $tree
 */

echo yii\widgets\Menu::Widget([
    'items' => $tree,
    'options' => [
        'class' => 'widget-categories',
    ],
]);
