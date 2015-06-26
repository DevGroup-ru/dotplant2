<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;
/** @var app\components\WebView $this */
/** @var bool $useFontAwesome */
/** @var \app\extensions\DefaultTheme\Module $theme */
/** @var integer $rootNavigationId */
/** @var integer $depth */
/** @var string $type */
/** @var string $header  */
/** @var boolean $displayHeader */
/** @var boolean $isInSidebar */
/** @var string $linkTemplate */
/** @var string $submenuTemplate */
/** @var array $options */
/** @var string $viewFile */

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="navigation-list ' . $sidebarClass . '">';

if ($displayHeader === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}

echo \app\widgets\navigation\NavigationWidget::widget([
    'rootId' => $rootNavigationId,
    'depth' => $depth,
    'options' => $options,
    'linkTemplate' => $linkTemplate,
    'submenuTemplate' => $submenuTemplate,
    'viewFile' => $viewFile,
]);

echo '</div>';
