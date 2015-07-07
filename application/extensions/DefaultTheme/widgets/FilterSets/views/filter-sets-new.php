<?php

/** @var app\components\WebView $this */
/** @var boolean $isInSidebar */
/** @var boolean $hideEmpty */
/** @var array $filtersArray */
/** @var \app\modules\shop\models\FilterSets[] $filterSets */
/** @var boolean $displayHeader */
/** @var string $header  */
/** @var string $id */

use yii\helpers\Html;
use yii\helpers\Url;

$sidebarClass = $isInSidebar ? 'sidebar-widget' : '';
$this->registerJs("$('#{$id}').dotPlantSmartFilters();");

?>

<div class="filter-sets-widget <?= $sidebarClass ?>">
    <?php if ($displayHeader === true): ?>
        <div class="widget-header">
            <?= $header ?>
        </div>
    <?php endif; ?>
    <div class="filters" id="<?=$id?>">
        <form action="<?= Url::to(['/shop/product/list', 'last_category_id'=>$urlParams['last_category_id']]) ?>" method="post" class="filter-form">
            <?php
            $cacheParams = [
                'duration'=>86400,
                'dependency' => new \yii\caching\TagDependency([
                    'tags' => \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\modules\shop\models\FilterSets::className())
                ])
            ];
            ?>
            <?php foreach ($filtersArray as $filter): ?>
            <div class="filter-property">
                <div class="property-name"><?= Html::encode($filter['name']) ?></div>
                <ul class="property-values">
                    <?php foreach ($filter['selections'] as $selection): ?>
                    <li>
                        <?=
                        Html::checkbox(
                            'properties[' . $filter['id'] . '][]',
                            $selection['checked'],
                            [
                                'value' => $selection['id'],
                                'class' => 'filter-check filter-check-property-' . $filter['id'],
                                'id' => 'filter-check-' . $selection['id'],
                                'data-property-id' => $filter['id'],
                            ]
                        )
                        ?>
                        <?=
                        Html::a(
                            $selection['label'],
                            $selection['url'],
                            [
                                'class' => 'filter-link',
                                'data-selection-id' => $selection['id'],
                                'data-property-id' => $filter['id'],
                            ]
                        )
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
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
        </form>
    </div>
</div>
