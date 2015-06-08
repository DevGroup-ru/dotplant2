<?php
/** @var \app\models\Slider $slider */
/** @var array $slider_params */
/** @var string $slide_viewFile */
/** @var string $css_class */
/** @var \app\components\WebView $this */
$slides = $slider->getSlides(true);
?>

<div id="<?= $id ?>" class="carousel slide <?= $css_class ?>" <?php if(count($slider_params)==0) {echo 'data-ride="carousel"';}; ?>>
    <!-- Indicators -->
    <ol class="carousel-indicators">
        <?php
        foreach ($slides as $index => $slide) :
        ?>
        <li data-target="#<?= $id ?>" data-slide-to="<?=$index?>" class="<?= ($index==0?'active':'') ?>"></li>
        <?php endforeach; ?>

    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
        <?php
            foreach ($slides as $index => $slide) {

                echo $this->render(
                    $slide_viewFile,
                    [
                        'slide' => $slide,
                        'slide_index' => $index,
                    ]
                );

            }
        ?>

    </div>

    <!-- Controls -->
    <a class="left carousel-control" href="#<?=$id?>" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    </a>
    <a class="right carousel-control" href="#<?=$id?>" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    </a>
</div>

<?php
if(count($slider_params)>0) {
    $this->registerJs(
        "$(\"#$id\").carousel(".\yii\helpers\Json::encode($slider_params).");",
        \yii\web\View::POS_READY
    );
}
$height = $slider->image_height.'px';
$this->registerCss(
    <<<CSS
#$id, #$id .item {
    max-height: $height;
}

CSS

);