<?php
/**
 * @var \yii\web\View $this
 * @var array $items
 * @var string $widget
 * @var string $linkTemplate
 * @var string $submenuTemplate
 */
echo $widget::widget([
        'items' => $items,
        'options' => $options,
        'linkTemplate' => $linkTemplate,
        'submenuTemplate' => $submenuTemplate,
    ]);
