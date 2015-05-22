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

        <div class="contragent" data-visible="hide">
        <?php
//            $newContragent = new \app\modules\shop\models\Contragent();
//            $newContragent->setAbstractModel($contragent_abstract_model);
//            foreach ($contragent_abstract_model->attributes() as $attr) {
//                echo $form->field($contragent_abstract_model, $attr);
//            }
        ?>
        </div>

        <?php
            foreach ($contragents as $contragent) {
                echo '<div class="contragent" data-visible="hide">';
                echo $form->field($contragent, 'type');
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