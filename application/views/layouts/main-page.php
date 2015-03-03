<?php

/**
 * @var $content string
 * @var $this \yii\web\View
 */

use app\widgets\Alert;
use yii\widgets\Breadcrumbs;

?>
<?= $this->render('blocks/header');?>
<?= $this->render('blocks/carousel');?>
    <div id="mainBody">
        <div class="container">
            <div class="row">
                <?= $this->render('blocks/sidebar');?>
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
                        );
                    ?>
                    <?= Alert::widget();?>
                    <?= $content;?>
                </div>
            </div>
        </div>
    </div>
<?= $this->render('blocks/footer');?>