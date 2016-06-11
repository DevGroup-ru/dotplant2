<?php

use yii\helpers\Url;
?>
<?php
Yii::beginProfile('Categories-list');
?>
<ul class="categories-list">
    <?php foreach ($categories as $category): ?>
        <?php
        $url = Url::to(['@category', 'last_category_id'=>$category->id, 'category_group_id'=> $category->category_group_id]);
        ?>
        <li<?= ($url === Yii::$app->request->url) ? " class='$activeClass'" : '' ?>>
            <a href="<?=$url?>">
                <?= \yii\helpers\Html::encode($category->name) ?>
            </a>
        </li>
    <?php endforeach;?>
</ul>
<?php
Yii::endProfile('Categories-list');