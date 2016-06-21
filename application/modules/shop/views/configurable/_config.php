<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use app\modules\shop\models\ConfigConfigurationModel;
use kartik\widgets\SwitchInput;
use app\components\Helper;

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'shopping-cart',
                'title' => Yii::t('app', 'Main settings'),
                'options' => ['class' => 'visible-header']
            ]
        )
        ?>
            <?= $form->field($model, 'productsPerPage') ?>
            <?= $form->field($model, 'listViewType')->dropDownList([
                'listView' => Yii::t('app', 'List view'),
                'blockView' => Yii::t('app', 'Block view')
            ]) ?>
            <?= $form->field($model, 'searchResultsLimit') ?>
            <?= $form->field($model, 'allowSearchGeneratedProducts')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'maxProductsToCompare') ?>
            <?= $form->field($model, 'maxLastViewedProducts') ?>
            <?= $form->field($model, 'showProductsOfChildCategories')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'itemView') ?>
        <?php BackendWidget::end() ?>

        <?php
        BackendWidget::begin(
            [
                'icon' => 'shopping-cart',
                'title' => Yii::t('app', 'Filters'),
                'options' => ['class' => 'visible-header']
            ]
        )
        ?>
            <?= $form->field($model, 'showFiltersInBreadcrumbs')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'productsFilteringMode')->dropDownList(ConfigConfigurationModel::getFilterModes()) ?>
            <?=
            $form->field($model, 'multiFilterMode')
                ->dropDownList(\app\modules\shop\models\ConfigConfigurationModel::getMultiFilterModes())
            ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'shopping-cart',
                'title' => Yii::t('app', 'Cart and orders'),
                'options' => ['class' => 'visible-header']
            ]
        )
        ?>
            <?= $form->field($model, 'deleteOrdersAbility')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'allowToAddSameProduct')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'countUniqueProductsOnly')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'countChildrenProducts')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'registrationGuestUserInCart')->widget(SwitchInput::className()) ?>
            <?= $form->field($model, 'showDeletedOrders')->widget(SwitchInput::className()) ?>
            <?=
            $form->field($model, 'defaultMeasureId')
                ->dropDownList(Helper::getModelMap(\app\modules\shop\models\Measure::className(), 'id', 'name'))
            ?>
            <?=
            $form->field($model, 'defaultOrderStageFilterBackend')
                ->dropDownList(
                    [0 => '']
                    + Helper::getModelMap(\app\modules\shop\models\OrderStage::className(), 'id', 'name_short')
                )
            ?>
            <?= $form->field($model, 'useCeilQuantity')->widget(SwitchInput::className()) ?>
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

<div>
    <div class="col-md-6 col-sm-12">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'cogs',
                'title' => Yii::t('app', 'Order stage system'),
                'options' => ['class' => 'visible-header']
            ]
        )
        ?>
        <?=
        $form->field($model, 'finalOrderStageLeaf')
            ->dropDownList(
                \app\components\Helper::getModelMap(
                    \app\modules\shop\models\OrderStageLeaf::className(),
                    'id',
                    'button_label'
                )
            )
        ?>
        <?php BackendWidget::end() ?>
    </div>

</div>