<?php
/**
 * @var string $url
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\widgets\OrderTransaction $transaction
 * @var array $data
 */
use yii\helpers\Html;
?>
<form action="<?= $url ?>" method="post" accept-charset="utf-8" target="_blank">
    <?php array_walk($data, function ($value, $key) {
        if (is_array($value)) {
            array_walk($value, function ($v, $k, $p) {
                echo Html::hiddenInput($p, $v);
            }, $key);
        } else {
            echo Html::hiddenInput($key, $value);
        }
    }); ?>
    <input type="submit" class="btn btn-primary" value="<?= Yii::t('app', 'Pay') ?>">
</form>
