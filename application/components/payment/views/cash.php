<?php
/**
 * @var string $url
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
?>
<div class="col-md-12">
    <div class="row">
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'Pay'),
            $url,
            [
                'class' => 'btn btn-success'
            ]
        ); ?>
    </div>
</div>