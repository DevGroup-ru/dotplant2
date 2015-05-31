<?php
/** @var string $content */
/** @var boolean $display_header */
/** @var string $header  */
/** @var boolean $isInSidebar */

use yii\helpers\Html;
$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
echo '<div class="content-block ' . $sidebarClass . '">';

if ($display_header === true) {
    ?>
    <div class="widget-header">
        <?= $header ?>
    </div>
    <?php
}

echo $content;

echo '</div>';