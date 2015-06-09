<?php
use yii\helpers\Html;
use app\modules\shop\models\UserPreferences;
// @deprecated, need to be rewritten
?>
<form class="form-horizontal span6">
    <div class="control-group">
        <?=Html::activeLabel(
            UserPreferences::preferences(),
            'productListingSortId',
            ['class' => 'control-label alignL']
        )?>
        <?=Html::activeDropDownList(
            UserPreferences::preferences(),
            'productListingSortId',
            \yii\helpers\ArrayHelper::map(\app\modules\shop\models\ProductListingSort::enabledSorts(), 'id', 'name'),
            [
                'data-userpreference' => 'productListingSortId',
            ]
        )?>
    </div>
    <div class="control-group">
        <?=Html::activeLabel(UserPreferences::preferences(), 'productsPerPage', ['class' => 'control-label alignL'])?>
        <?=Html::activeDropDownList(
            UserPreferences::preferences(),
            'productsPerPage',
            [
                9 => 9,
                18 => 18,
                30 => 30,
            ],
            [
                'data-userpreference' => 'productsPerPage',
            ]
        )?>
    </div>
</form>

<div class="pull-right">
    <a href="#" data-dotplant-listViewType="listView"><span class="btn btn-large"><i class="fa fa-list"></i></span></a>
    <a href="#" data-dotplant-listViewType="blockView"><span class="btn btn-large btn-primary"><i class="fa fa-th-large"></i></span></a>
</div>