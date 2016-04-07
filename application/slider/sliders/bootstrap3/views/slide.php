<div class="item<?php if ($slide_index==0) {echo ' active';};?>">
    <a href="<?= \yii\helpers\Html::encode($slide->link) ?>" class="slider-link">
        <img src="<?= $slide->image ?>" alt="...">
        <div class="carousel-caption">
            <?= $slide->text ?>
        </div>
    </a>
</div>
