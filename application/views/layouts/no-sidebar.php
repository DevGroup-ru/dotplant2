<?php

/**
 * @var $content string
 * @var $this \yii\web\View
 */

use yii\widgets\Breadcrumbs;
use app\widgets\Alert;

?>
<?php include('blocks/header.php') ?>
    <div id="mainBody">
        <div class="container">
            <div class="row">
                <div class="span12">
                    <?=
                        Breadcrumbs::widget(
                            [
                                'itemTemplate' => "<li>{link} <span class=\"divider\">/</span></li>\n",
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