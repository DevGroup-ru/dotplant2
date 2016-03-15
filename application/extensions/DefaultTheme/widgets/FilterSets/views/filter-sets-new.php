<?php

/**
 * @var app\components\WebView $this
 * @var boolean $isInSidebar
 * @var boolean $hideEmpty
 * @var array $filtersArray
 * @var boolean $displayHeader
 * @var string $header
 * @var string $id
 * @var array $urlParams
 * @var bool $usePjax
 */

use app\modules\shop\models\ConfigConfigurationModel;
use app\modules\shop\widgets\PropertiesSliderRangeWidget;
use yii\helpers\Html;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
if ($usePjax) {
    $this->registerJs("$('#{$id}').dotPlantSmartFilters();");
}
$filterMode = Yii::$app->getModule('shop')->multiFilterMode;

?>
<?php if (false === empty($filtersArray)) : ?>
    <div class="filter-sets-widget <?= $sidebarClass ?>">
        <?php if ($displayHeader === true): ?>
            <div class="widget-header">
                <?= $header ?>
            </div>
        <?php endif; ?>
        <div class="filters" id="<?= $id ?>">
            <?=
            Html::beginForm(
                ['@category', 'last_category_id' => $urlParams['last_category_id']],
                'post',
                [
                    'class' => 'filter-form',
                ]
            )
            ?>
            <?php foreach ($filtersArray as $filter): ?>
                <div class="filter-property">
                    <?php if ($filter['isRange']): ?>
                        <?=
                        PropertiesSliderRangeWidget::widget(
                            [
                                'property' => $filter['property'],
                                'categoryId' => $urlParams['last_category_id'],
                                'maxValue' => $filter['max'],
                                'minValue' => $filter['min'],
                                'step' => $filter['step'],
                            ]
                        )
                        ?>
                    <?php else: ?>
                        <div class="property-name"><?= Html::encode($filter['name']) ?></div>
                        <ul class="property-values" data-multiple="<?= $filter['multiple'] ?>">
                            <?php foreach ($filter['selections'] as $selection): ?>
                                <li>
                                    <?php
                                    if ($filter['multiple']) {
                                        switch ($filterMode) {
                                            case ConfigConfigurationModel::MULTI_FILTER_MODE_INTERSECTION:
                                                echo Html::checkbox(
                                                    'properties[' . $filter['id'] . '][]',
                                                    $selection['checked'],
                                                    [
                                                        'value' => $selection['id'],
                                                        'class' => 'filter-check filter-check-property-' . $filter['id'],
                                                        'id' => 'filter-check-' . $selection['id'],
                                                        'data-property-id' => $filter['id'],
                                                        'disabled' => $selection['active'] === true || $selection['checked'] === true
                                                            ? null
                                                            : 'disabled',
                                                    ]
                                                );

                                                echo $selection['active'] === true || $selection['checked'] === true
                                                    ? Html::a(
                                                        $selection['label'],
                                                        $selection['url'],
                                                        [
                                                            'class' => 'filter-link',
                                                            'data-selection-id' => $selection['id'],
                                                            'data-property-id' => $filter['id'],
                                                            'rel' => !$selection['checked'] ? null : 'nofollow',
                                                        ]
                                                    )
                                                    : Html::tag('span', $selection['label'], ['class' => 'inactive-filter']);

                                                break;
                                            case ConfigConfigurationModel::MULTI_FILTER_MODE_UNION:
                                                echo Html::checkbox(
                                                    'properties[' . $filter['id'] . '][]',
                                                    $selection['checked'],
                                                    [
                                                        'value' => $selection['id'],
                                                        'class' => 'filter-check filter-check-property-' . $filter['id'],
                                                        'id' => 'filter-check-' . $selection['id'],
                                                        'data-property-id' => $filter['id'],
                                                    ]
                                                );

                                                echo Html::a(
                                                    $selection['label'],
                                                    $selection['url'],
                                                    [
                                                        'class' => 'filter-link',
                                                        'data-selection-id' => $selection['id'],
                                                        'data-property-id' => $filter['id'],
                                                        'rel' => !$selection['checked'] ? null : 'nofollow',
                                                    ]
                                                );

                                                break;
                                        }
                                    } else {
                                        echo Html::checkbox(
                                            'properties[' . $filter['id'] . '][]',
                                            $selection['checked'],
                                            [
                                                'value' => $selection['id'],
                                                'class' => 'filter-check filter-check-property-' . $filter['id'],
                                                'id' => 'filter-check-' . $selection['id'],
                                                'data-property-id' => $filter['id'],
                                                'disabled' => $selection['active'] === true || $selection['checked'] === true
                                                    ? null
                                                    : 'disabled',
                                            ]
                                        );

                                        echo $selection['active'] === true || $selection['checked'] === true
                                            ? Html::a(
                                                $selection['label'],
                                                $selection['url'],
                                                [
                                                    'class' => 'filter-link',
                                                    'data-selection-id' => $selection['id'],
                                                    'data-property-id' => $filter['id'],
                                                    'rel' => !$selection['checked'] ? null : 'nofollow',
                                                ]
                                            )
                                            : Html::tag('span', $selection['label'], ['class' => 'inactive-filter']);
                                    }

                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="filter-actions">
                <?=
                Html::submitButton(
                    Yii::t('app', 'Show'),
                    [
                        'class' => 'btn btn-primary btn-filter-show',
                    ]
                )
                ?>
            </div>
            <?= Html::endForm() ?>
            <div class="overlay"></div>
        </div>
    </div>
    <?php
    $JS = <<<JS
(function($){
"use strict"
$('.filter-check').change(function(){
    var \$multiple = $(this).parents('ul.property-values').data('multiple');
    if (0 === \$multiple) {
        if (true === $(this).prop('checked')) {
            $(this).parents('ul.property-values').find('input[type=checkbox]').not(this).each(function(){
                $(this).prop('checked', false);
            })
        }
    }
});
})(jQuery)
JS;
    $this->registerJs($JS);
    ?>
<?php endif; ?>