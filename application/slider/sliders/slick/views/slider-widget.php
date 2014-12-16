<?php
/** @var \app\models\Slider $slider */
/** @var array $slider_params */
/** @var string $slide_viewFile */
/** @var string $css_class */
?>

    <div id="<?= $id ?>" class="<?= $css_class ?>">

        <?php
        foreach ($slider->getSlides() as $index => $slide) {
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

<?php

$this->registerJs(
    "$(\"#$id\").slick(".\yii\helpers\Json::encode($slider_params).");",
    \yii\web\View::POS_READY
);
