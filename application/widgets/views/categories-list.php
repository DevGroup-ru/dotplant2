<?php

use yii\helpers\Url;
?>

<ul class="categories-list">
    <?php foreach ($categories as $category): ?>
        <li>
            <a href="<?=Url::to(['/shop/product/list', 'last_category_id'=>$category->id])?>">
                <?= \yii\helpers\Html::encode($category->name) ?>
            </a>
        </li>
    <?php endforeach;?>
</ul>