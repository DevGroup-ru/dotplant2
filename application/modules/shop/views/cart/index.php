<?php
/**
 * @var \app\modules\shop\models\Order $model
 * @var \app\modules\shop\models\OrderCode $orderCode
 * @var \yii\web\View $this
 */

$this->title = Yii::t('app', 'Cart');

?>
    <h1><?= Yii::t('app', 'Cart') ?></h1>
<div class="cart">
<?php if (!is_null($model) && $model->items_count > 0): ?>
    <?= $this->render('items', ['model' => $model, 'items' => $model->items]) ?>
    <div class="pull-right">
        <div class="discount-code">
        <?php if ($orderCode->isNewRecord): ?>
            <?php $form = \kartik\form\ActiveForm::begin(
                [
                    'type' => \kartik\form\ActiveForm::TYPE_INLINE,
                ]
            );
            ?>
            <?= $form->errorSummary($orderCode) ; ?>
            <?= $form->field($orderCode, 'code') ?>
            <?= \kartik\helpers\Html::submitButton(Yii::t('app', 'Apply code'), ['class' => 'btn btn-success']); ?>
            <?= \kartik\helpers\Html::endForm(); ?>
        <?php else: ?>
            <?= Yii::t('app', 'Applied discount code:') ?>
            <div class="applied-discount-code">
                <?= $orderCode->discountCode->code ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="pull-right cta">
        <?= \yii\helpers\Html::a(Yii::t('app', $model->stage->isInitial() ? 'Checkout' : 'Continue checkout'), ['/shop/cart/stage'], ['class' => 'btn btn-primary']); ?>
    </div>

<?php else: ?>
    <p><?= Yii::t('app', 'Your cart is empty') ?></p>
<?php endif; ?>
</div>