<?php

/**
 * @var $this yii\web\View
 */

$this->title = Yii::t('app', 'Personal cabinet');
$this->params['breadcrumbs'] = [
    $this->title,
];

$links = [
    '/cabinet/orders' => 'Orders list',
    '/cabinet/profile' => 'Edit profile',
    '/cabinet/change-password' => 'Change a password',
];

?>

<h1><?= $this->title ?></h1>
<ul>
    <?php foreach($links as $url => $anchor): ?>
        <li><a href="<?= Yii::$app->request->baseUrl.$url ?>"><?= Yii::t('app', $anchor) ?></a></li>
    <?php endforeach; ?>
</ul>