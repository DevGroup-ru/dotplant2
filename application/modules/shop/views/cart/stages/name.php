<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\Customer $customer
 * @var \app\modules\shop\models\Contragent[] $contragents
 * @var \app\properties\AbstractModel $abstractModel
 */

use app\properties\AbstractModel;

?>
<div class="col-md-6 col-md-offset-3">
    <div class="row">
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

        <?= \yii\helpers\Html::dropDownList('ContragentId', 0, array_reduce($customer->contragents,
            function ($result, $item)
            {
                /** @var \app\modules\shop\models\Contragent $item */
                $result[$item->id] = $item->type;
                return $result;
            }, [0 => 'Новый контрагент'])
        ); ?>

        <div class="contragent" data-visible="hide">
        <?php
            $newContragent = \app\modules\shop\models\Contragent::createEmptyContragent($customer->id);
            echo $form->field($newContragent, 'type')->dropDownList(['Individual' => 'Individual', 'Self-employed' => 'Self-employed', 'Legal entity' => 'Legal entity']);
            $abstractModel = $newContragent->getAbstractModel();
            foreach ($abstractModel->attributes() as $attr) {
                echo $form->field($abstractModel, $attr);
            }
        ?>
        </div>

        <?php
            foreach ($contragents as $contragent) {
                echo '<div class="contragent" data-visible="hide">';
                echo \yii\helpers\Html::tag('div', $contragent->type);
                $abstractModel = $contragent->getAbstractModel();
                $abstractModel->setArrayMode(false);
                foreach ($abstractModel->attributes() as $attr) {
                    $form->field($abstractModel, $attr);
                }
                echo '</div>';
            }
        ?>
    </div>
</div>