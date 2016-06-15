<?php
/** @var $bottom bool
 * @var $this \yii\web\View
 */
use kartik\icons\Icon;

?>

<nav id="dotplant-floating-panel" class="<?= $bottom?'dotplant-floating-panel-bottom open':'open'?>">
    <div class="container-fluid">
        <span class="panel-toggler"></span>
        <a href="/backend/" class="navbar-text">DotPlant2</a>
        <div class="navbar-text">
            <?= Yii::t('app', 'Logged as')?>:
            <?= Yii::$app->user->identity->username ?>
            <a href="/logout">
                <?= Icon::show('sign-out') ?>
            </a>
        </div>

        <?= \yii\widgets\Menu::widget([
            'items' => $items,
            'encodeLabels' => false,
            'options' => [
                'class' => 'nav navbar-nav',
            ],
            'linkTemplate' => '<a href="{url}" target="_blank">{label}</a>',
        ]) ?>


    </div>

</nav>
<?php if (!$bottom) {
    $this->registerJs("$('body').css('padding-top', parseInt($('body').css('padding-top'))+51);",
        \yii\web\View::POS_READY);
}
$this->registerJs(<<<JS
    $('#dotplant-floating-panel .panel-toggler').click(function(){
        $('#dotplant-floating-panel').toggleClass('open closed');
    });
JS
);
?>



