<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\Customer|\app\properties\HasProperties $customer
 * @var \app\modules\shop\models\Contragent[] $contragents
 * @var \app\properties\AbstractModel $abstractModel
 */

use app\properties\AbstractModel;
if ($order->contragent_id === 0 && count($contragents) > 0) {
    $order->contragent_id = array_slice($contragents, -1)[0]->id;
}

?>
<div class="col-md-6 col-md-offset-3">
    <div class="row">
        <?= \yii\helpers\Html::hiddenInput($order->formName().'[customer_id]', $order->customer_id); ?>
        <?= $form->field($customer, 'first_name'); ?>
        <?= $form->field($customer, 'middle_name'); ?>
        <?= $form->field($customer, 'last_name'); ?>
        <?= $form->field($customer, 'email'); ?>
        <?= $form->field($customer, 'phone'); ?>
        <?php
            $abstractModel = $customer->getAbstractModel();
            $abstractModel->setArrayMode(false);
            foreach ($abstractModel->attributes() as $attr) {
                echo $form->field($abstractModel, $attr);
            }
        ?>

        <h2><?= Yii::t('app', 'Payer information') ?></h2>
        <?= $form->field($order, 'contragent_id')->dropDownList(array_reduce($contragents,
                function ($result, $item)
                {
                    /** @var \app\modules\shop\models\Contragent $item */
                    if ($item->isNewRecord) {
                        $result[0] = Yii::t('app', 'New payer profile');
                    } else {
                        $result[$item->id] = Yii::t('app', $item->type);
                    }
                    return $result;
                }, [])
            , ['class' => 'contragents form-control']);
        ?>

        <hr />
        <div class="contragents_list">
            <?php
            foreach ($contragents as $key => $contragent) {
                /** @var \app\modules\shop\models\Contragent|\app\properties\HasProperties $contragent */
                $_content = $form->field($contragent, 'type')
                    ->dropDownList([
                        'Individual' => Yii::t('app', 'Individual'),
                        'Self-employed' => Yii::t('app', 'Self-employed'),
                        'Legal entity' => Yii::t('app', 'Legal entity'),
                    ]);
                /** @var \app\properties\AbstractModel $abstractModel */
                $abstractModel = $contragent->getAbstractModel();
                $abstractModel->setArrayMode(false);
                foreach ($abstractModel->attributes() as $attr) {
                    $_content .= $form->field($abstractModel, $attr);
                }

                echo \yii\helpers\Html::tag('div', $_content, [
                    'class' => "contragent contragent_$key" . ($key === intval($order->contragent_id) ? '' : ' hide')
                ]);
            }
            ?>
        </div>
    </div>
</div>
<?php
$js = <<<JS
    "use strict";
    $('select.contragents').change(function(event) {
        $('.contragents_list .contragent').addClass('hide');
        $('.contragents_list .contragent_'+$(this).val()).removeClass('hide');
    });
    $('form#shop-stage').submit(function(event) {
        $('.contragents_list .contragent.hide').remove();
    });
JS;
$this->registerJs($js);
?>