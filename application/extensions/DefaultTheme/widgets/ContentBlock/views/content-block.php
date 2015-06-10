<?php
/** @var string $content */
/** @var boolean $displayHeader */
/** @var string $header  */
/** @var boolean $isInSidebar */

use yii\helpers\Html;
$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="content-block ' . $sidebarClass . '">';

if ($displayHeader === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}

echo $content;

echo '</div>';