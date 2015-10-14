<?php
/**
 * @var \yii\web\View $this
 * @var array $tree
 */

echo yii\widgets\Menu::Widget([
    'items' => $tree,
    'activeCssClass' => $activeClass,
    'activateParents' => $activateParents,
    'options' => [
        'class' => 'widget-categories',
    ],
]);
