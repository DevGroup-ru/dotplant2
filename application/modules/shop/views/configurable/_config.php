<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use kartik\widgets\SwitchInput;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'productsPerPage') ?>

        <?= $form->field($model, 'searchResultsLimit') ?>

        <?= $form->field($model, 'showProductsOfChildCategories')->checkbox() ?>

        <?= $form->field($model, 'maxProductsToCompare') ?>

        <?= $form->field($model, 'maxLastViewedProducts') ?>
    </div>
    <div class="col-md-6 col-sm-12">

        <?= $form->field($model, 'filterOnlyByParentProduct')->checkbox() ?>

    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['icon' => 'shopping-cart', 'title' => Yii::t('app', 'Cart and orders'), 'options' => ['class' => 'visible-header']]) ?>

        <?= $form->field($model, 'deleteOrdersAbility')->widget(SwitchInput::className()) ?>

        <?= $form->field($model, 'allowToAddSameProduct')->widget(SwitchInput::className()) ?>

        <?= $form->field($model, 'countUniqueProductsOnly')->widget(SwitchInput::className()) ?>

        <?= $form->field($model, 'countChildrenProducts')->widget(SwitchInput::className()) ?>

        <?= $form->field($model, 'defaultMeasureId')->dropDownList(\app\components\Helper::getModelMap(\app\modules\shop\models\Measure::className(), 'id', 'name')) ?>

        <?php BackendWidget::end() ?>
    </div>
    <?php
    /**
     * @var bool Allow to add same product in the order
     */
    $allowToAddSameProduct = 0;

    /**
     * @var bool Count only unique products in the order
     */
    $countUniqueProductsOnly = 1;

    /**
     * @var bool Count children products in the order
     */
    $countChildrenProducts = 1;

    /**
     * @var int Default measure ID
     */
    $defaultMeasureId = 1;
    ?>
</div>

