<?php

/**
 * @var $content string
 * @var $this \yii\web\View
 */

use app\widgets\Alert;
use yii\widgets\Breadcrumbs;
use app\extensions\DefaultTheme\models\ThemeParts;

$leftSidebar = ThemeParts::renderPart('left-sidebar');
$rightSidebar = ThemeParts::renderPart('right-sidebar');
$contentLength = 12;
$leftSidebarLength = 3;
$rightSidebarLength = 3;

if (empty($leftSidebar) === false) {
    $contentLength -= $leftSidebarLength;
}
if (empty($rightSidebar) === false) {
    $contentLength -= $rightSidebarLength;
}

?>
<?php include('blocks/header.php') ?>
    <div class="content-block">
        <?= ThemeParts::renderPart('before-content') ?>
            <div class="container">
                <div class="row">
                    <?php

                    if (!empty($leftSidebar)) {
                        echo '<div class="left-sidebar col-md-'.$leftSidebarLength.' col-xs-12">' . $leftSidebar . '</div>';
                    }

                    ?>
                    <div class="content-part col-md-<?=$contentLength?> col-xs-12">
                    <?=
                        Breadcrumbs::widget(
                            [
                                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                                'options' => [
                                    'itemprop' => "breadcrumb",
                                    'class' => 'breadcrumb',
                                ]
                            ]
                        )
                    ?>
                    <?= Alert::widget() ?>
                    <?= $content ?>
                    </div> <!-- content-part end -->

                    <?php

                    if (!empty($rightSidebar)) {
                        echo '<div class="right-sidebar col-md-'.$rightSidebarLength.' col-xs-12">' . $rightSidebar . '</div>';
                    }
                    ?>
                </div> <!-- /row -->
            </div> <!-- /container -->
    </div>


<?php include('blocks/footer.php') ?>