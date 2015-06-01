<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\Customer $customer
 * @var \app\modules\shop\models\Contragent[] $contragents
 * @var \app\properties\AbstractModel $abstractModel
 */

use app\properties\AbstractModel;

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

        <h2>Данные о контрагенте</h2>
        <?= $form->field($order, 'contragent_id')->dropDownList(array_reduce($contragents,
                function ($result, $item)
                {
                    /** @var \app\modules\shop\models\Contragent $item */
                    if ($item->isNewRecord) {
                        $result[0] = 'Новый Контрагент';
                    } else {
                        $result[$item->id] = $item->type;
                    }
                    return $result;
                }, [])
            , ['class' => 'contragents form-control']);
        ?>

        <hr />
        <div class="contragents_list">
            <?php
            foreach ($contragents as $key => $contragent) {
                /** @var \app\modules\shop\models\Contragent $contragent */
                $_content = $form->field($contragent, 'type')
                    ->dropDownList(['Individual' => 'Individual', 'Self-employed' => 'Self-employed', 'Legal entity' => 'Legal entity']);
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