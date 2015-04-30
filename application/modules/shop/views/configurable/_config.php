<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurableModel $model */

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
        <?= $form->field($model, 'cartCountsUniqueProducts')->checkbox() ?>

        <?= $form->field($model, 'filterOnlyByParentProduct')->checkbox() ?>

        <?= $form->field($model, 'deleteOrdersAbility')->checkbox() ?>

    </div>
</div>

