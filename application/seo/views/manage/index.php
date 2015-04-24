<?php
$this->title = 'SEO';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= \yii\helpers\Html::encode($this->title) ?></h1>

<div class="list-group">

    <?= \yii\helpers\Html::a(
        '<h4 class="list-group-item-heading">' . Yii::t('app', 'Meta tags') . '</h4><p class="list-group-item-text">' . Yii::t('app', 'Creating or updating meta tags on main page') . '</p>',
        ['/seo/manage/meta'],
        [
            'class' => 'list-group-item',
        ]
    ); ?>
    <?= \yii\helpers\Html::a(
        '<h4 class="list-group-item-heading">' . Yii::t('app', 'Counters') . '</h4><p class="list-group-item-text">' . Yii::t('app', 'Creating or updating search engine\'s counters') . '</p>',
        ['/seo/manage/counter'],
        [
            'class' => 'list-group-item',
        ]
    ); ?>
    <?= \yii\helpers\Html::a(
        '<h4 class="list-group-item-heading">Robots.txt</h4><p class="list-group-item-text">' . Yii::t('app', 'Robots.txt file management') . '</p>',
        ['/seo/manage/robots'],
        [
            'class' => 'list-group-item',
        ]
    ); ?>
    <?= \yii\helpers\Html::a(
        '<h4 class="list-group-item-heading">' . Yii::t('app', 'Redirects') . '</h4><p class="list-group-item-text">' . Yii::t('app', 'Redirects management') . '</p>',
        ['/seo/manage/redirect'],
        [
            'class' => 'list-group-item',
        ]
    ); ?>
    <?= \yii\helpers\Html::a(
        '<h4 class="list-group-item-heading">' . Yii::t('app', 'E-commerce counters') . '</h4><p class="list-group-item-text">' . Yii::t('app', 'E-commerce counters management') . '</p>',
        ['/seo/manage/ecommerce'],
        [
            'class' => 'list-group-item',
        ]
    ); ?>

</div>