<?php
use kartik\helpers\Html;

?>

<h1><?= Yii::t('app', 'New order received') ?> #<?=$model->id?></h1>
<p>
    <b><?= Yii::t('app', 'Date') ?></b> <?= date('d.m.Y H:i:s') ?>
</p>
<table>
<?php foreach ($model->getAttributes() as $name => $value):?>
    <?php if ($name === 'verifyCode') continue; ?>
    <tr>
        <td><?= $model->getAttributeLabel($name) ?></td>
        <td><?= Html::encode($value) ?></td>
    </tr>
<?php endforeach;?>
</table>

<style>
table {
    border: 1px solid #ccc;
}
table td {
    padding: 4px;
    border: 1px solid #ccc;
}
</style>