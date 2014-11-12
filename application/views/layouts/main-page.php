<?php

/**
 * @var $content string
 * @var $this \yii\web\View
 */

use app\widgets\Alert;
use yii\widgets\Breadcrumbs;

?>
<?php include('blocks/header.php') ?>
<?php include('blocks/carousel.php') ?>
    <div id="mainBody">
        <div class="container">
            <div class="row">
                <?php include('blocks/sidebar.php'); ?>
                <div class="span9">
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
                </div>
            </div>
        </div>
    </div>
<?php include('blocks/footer.php') ?>