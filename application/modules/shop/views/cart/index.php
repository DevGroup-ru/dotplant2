<?php
/**
 * @var \app\modules\shop\models\Order $model
 * @var \app\modules\shop\models\OrderCode $orderCode
 * @var \yii\web\View $this
 */

$this->title = Yii::t('app', 'Cart');

?>
    <h1><?= Yii::t('app', 'Cart') ?></h1>
<?php if (!is_null($model) && $model->items_count > 0): ?>
    <?= $this->render('items', ['model' => $model, 'items' => $model->items]) ?>
    <div class="pull-right">

        <?php if ($orderCode->isNewRecord): ?>
            <?php $form = \kartik\form\ActiveForm::begin(
                [
                    'type' => \kartik\form\ActiveForm::TYPE_INLINE,
                ]
            );
            ?>
            <?= $form->errorSummary($orderCode) ; ?>
            <?= $form->field($orderCode, 'code') ?>
            <?= \kartik\helpers\Html::submitButton(Yii::t('app', 'Apply code'), ['class' => 'btn btn-primary']); ?>
            <?= \kartik\helpers\Html::endForm(); ?>
        <?php else: ?>
            <?= $orderCode->discountCode->code ?>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
    <?= \yii\helpers\Html::a(Yii::t('app', 'Begin order'), ['/shop/cart/stage'], ['class' => 'btn btn-success']); ?>

<?php else: ?>
    <p><?= Yii::t('app', 'Your cart is empty') ?></p>
<?php endif; ?>
