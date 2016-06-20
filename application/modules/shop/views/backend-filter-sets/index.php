<?php

use kartik\dropdown\DropdownX;
use yii\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;
use devgroup\JsTreeWidget\TreeWidget;
use devgroup\JsTreeWidget\ContextMenuHelper;


$this->title = Yii::t('app', 'Filter sets');

/** @var \app\modules\shop\models\Category $selectedCategory */
/** @var array $propertiesDropdownItems */

$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>
<h1>
    <?php if (is_object($selectedCategory)): ?>
    <?= Yii::t('app', 'Current category:') ?>
    <?= Html::encode($selectedCategory->name) ?>
    <?php else: ?>
    <?= Yii::t('app', 'Select category first') ?>
    <?php endif;?>
</h1>
<div class="row">
    <div class="col-md-4">
        <?=
            TreeWidget::widget([
                'treeDataRoute' => ['/shop/backend-filter-sets/getTree'],
                'changeParentAction' => '/backend/category/move',
                'reorderAction' => '/backend/category/reorder',
                'contextMenuItems' => [
                    'open' => [
                        'label' => 'Open',
                        'icon' => 'fa fa-folder-open',
                        'action' => ContextMenuHelper::actionUrl(
                            ['/shop/backend-filter-sets/index'],
                            [
                                'category_id' => 'id',
                            ]
                        ),
                    ],
                ],
            ]);
        ?>
    </div>
    <div class="col-md-8" id="filter-sets">
        <?php
        echo Html::beginTag('div', ['class'=>'dropdown']);
        echo Html::button(Yii::t('app', 'Add property') . ' <span class="caret"></span></button>',
            ['type'=>'button', 'class'=>'btn btn-default', 'data-toggle'=>'dropdown']);
        echo DropdownX::widget([

            'items' => $propertiesDropdownItems,


        ]);
        echo Html::endTag('div');
        ?>

        <?php if ($selectedCategory !== null): ?>
            <?php
            $filterSets = $selectedCategory->filterSets();
            $filterSetsByGroup = [];
            foreach ($filterSets as $item) {
                $property = \app\models\Property::findById($item->property_id);
                if (!isset($filterSetsByGroup[$property->property_group_id])) {
                    $filterSetsByGroup[$property->property_group_id] = [];
                }
                $filterSetsByGroup[$property->property_group_id][] = $item;
            }

            foreach ($filterSetsByGroup as $groupId => $sets):
                $group = \app\models\PropertyGroup::findById($groupId);
                if ($group === null) {
                    echo "Group: $groupId not found!!!<BR>";
                    continue;
                }
                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?= $group->name ?>
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed table-striped table-hover">
                            <thead>
                                <tr>
                                    <td class="td-no-padding">&nbsp;</td>
                                    <td>
                                        <?= Yii::t('app', 'Property name') ?>
                                    </td>
                                    <td>
                                        <?= Yii::t('app', 'Delegate to children') ?>
                                    </td>
                                    <td>
                                        <?= Yii::t('app', 'Multiple') ?>
                                    </td>
                                    <td>
                                        <?= Yii::t('app', 'Is range slider') ?>
                                    </td>
                                    <td>
                                        <?= Yii::t('app', 'Inherited from') ?>
                                    </td>
                                    <td>
                                        <?= Yii::t('app', 'Actions') ?>
                                    </td>
                                </tr>
                            </thead>
                            <tbody class="properties-body">
                                <?php foreach ($sets as $filterSet):?>
                                    <?php
                                    /** @var \app\modules\shop\models\FilterSets $filterSet */
                                        $rowClass = $filterSet->category_id === $selectedCategory->id
                                            ? ($filterSet->delegate_to_children === 1 ? 'success' : '')
                                            : 'info';
                                        $property = \app\models\Property::findById($filterSet->property_id);
                                    ?>
                                    <tr class="<?= $rowClass ?>" property-id="<?=$property->id?>" filterset-id="<?=$filterSet->id?>">
                                        <td class="td-no-padding">
                                            <span class="sort-handle">
                                                <?= Icon::show('arrows-v') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= Html::encode($property->name) ?>
                                        </td>
                                        <td>
                                            <?=
                                            Html::checkbox(
                                                'delegateToChildren['.$filterSet->id.']',
                                                $filterSet->delegate_to_children === 1,
                                                [
                                                    'class'=>'delegate-property-checkbox',
                                                    'data-filterset-id' => $filterSet->id,
                                                    'data-inherited' => $filterSet->category_id === $selectedCategory->id ? '0' : '1',
                                                ]
                                            )
                                            ?>
                                        </td>
                                        <td>
                                            <?=
                                            Html::checkbox(
                                                'multiple['.$filterSet->id.']',
                                                $filterSet->multiple === 1,
                                                [
                                                    'class'=>'multiple-checkbox',
                                                    'data-filterset-id' => $filterSet->id,
                                                ]
                                            )
                                            ?>
                                        </td>
                                        <td>
                                            <?=
                                            Html::checkbox(
                                                'isRangeSlider['.$filterSet->id.']',
                                                $filterSet->is_range_slider === 1,
                                                [
                                                    'class'=>'is-range-slider-checkbox',
                                                    'data-filterset-id' => $filterSet->id,
                                                ]
                                            )
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($filterSet->category_id !== $selectedCategory->id) {
                                                $cat = \app\modules\shop\models\Category::findById($filterSet->category_id);
                                                echo Html::a(
                                                    $cat->name,
                                                    ['/shop/backend-filter-sets/index', 'category_id'=>$cat->id]
                                                );
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?=
                                                Html::a(
                                                    Icon::show('trash-o'),
                                                    [
                                                        '/shop/backend-filter-sets/delete-filter-set',
                                                        'id' => $filterSet->id,
                                                        'category_id' => $selectedCategory->id,
                                                    ],
                                                    [
                                                        'class' => 'btn btn-danger btn-xs',
                                                        'data-action' => 'post',
                                                    ]
                                                )
                                            ?>
                                            <?php if ($property->has_static_values === 1): ?>
                                                <a href="#psv-table-<?=$property->id?>" class="btn btn-info btn-xs show-psv-values" data-toggle="collapse">
                                                    <?= Icon::show('caret-down') ?>
                                                    <?= Yii::t('app', 'Show values') ?>
                                                </a>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                    <?php if ($property->has_static_values === 1): ?>
                                    <tr id="psv-tr-<?=$property->id?>" class="psv-tr">
                                        <td colspan="5">
                                            <div class="collapse" id="psv-table-<?=$property->id?>">
                                                <table class="table table-bordered table-condensed table-hover table-striped">
                                                    <thead>
                                                    <tr>
                                                        <td class="td-no-padding">&nbsp;</td>
                                                        <td>
                                                            <?= Yii::t('app', 'Static value') ?>
                                                        </td>
                                                        <td>
                                                            <?= Yii::t('app', 'Slug') ?>
                                                        </td>
                                                        <td>
                                                            <?= Yii::t('app', 'Display in filter') ?>
                                                        </td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    $staticValues = \app\models\PropertyStaticValues::getValuesForPropertyId($property->id);
                                                    foreach ($staticValues as $staticValue):?>
                                                        <tr psv-id="<?=$staticValue['id']?>">
                                                            <td class="td-no-padding">
                                                                <span class="psv-sort-handle">
                                                                    <?= Icon::show('arrows-v') ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?= Html::encode($staticValue['name']) ?>
                                                            </td>
                                                            <td>
                                                                <?= Html::textInput(
                                                                    'slug['.$staticValue['id'].']',
                                                                    $staticValue['slug'],
                                                                    [
                                                                        'class' => 'psv_slug form-control',
                                                                        'data-static-value-id' => $staticValue['id'],
                                                                    ]
                                                                ) ?>
                                                            </td>
                                                            <td>
                                                                <?= Html::checkbox(
                                                                    'display_in_filter['.$staticValue['id'].']',
                                                                    $staticValue['dont_filter'] === '0',
                                                                    [
                                                                        'class' => 'display_in_filter form-control',
                                                                        'data-static-value-id' => $staticValue['id'],
                                                                    ]
                                                                ) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            endforeach;

            ?>
        <?php endif; ?>
    </div>
</div>

<?php
$modifyFilterSetUrl = \yii\helpers\Json::encode(Url::to(['/shop/backend-filter-sets/modify-filter-set']));
$modifyPSV = \yii\helpers\Json::encode(Url::to(['/shop/backend-filter-sets/modify-psv']));
$saveSortedUrl = \yii\helpers\Json::encode(Url::to(['/shop/backend-filter-sets/save-sorted']));

$js = <<<JS
$(".delegate-property-checkbox").change(function(){
    var state = this.checked,
        row = $(this).parent().parent(),
        inherited = $(this).data('inherited') == '1';
    $.ajax({
        'url': $modifyFilterSetUrl,
        'method': 'POST',
        'data': {
            'id': $(this).data('filtersetId'),
            'FilterSets[delegate_to_children]': state ? '1' : '0'
        },
        complete: function() {
            row
                .removeClass('info')
                .removeClass('success')
                .removeClass('danger');

            if (state && inherited) {
                row.addClass('info');
            } else if (state && !inherited) {
                row.addClass('success');
            } else if (!state && inherited) {
                row.addClass('danger');
            }
        }
    })
});
$(".multiple-checkbox").change(function(){
    var state = this.checked,
        row = $(this).parent().parent(),
        inherited = $(this).data('inherited') == '1';
    $.ajax({
        'url': $modifyFilterSetUrl,
        'method': 'POST',
        'data': {
            'id': $(this).data('filtersetId'),
            'FilterSets[multiple]': state ? '1' : '0'
        },
        complete: function() {
            row
                .removeClass('info')
                .removeClass('success')
                .removeClass('danger');

            if (state && inherited) {
                row.addClass('info');
            } else if (state && !inherited) {
                row.addClass('success');
            } else if (!state && inherited) {
                row.addClass('danger');
            }
        }
    })
});
$(".is-range-slider-checkbox").change(function(){
    var state = this.checked,
        row = $(this).parent().parent(),
        inherited = $(this).data('inherited') == '1';
    $.ajax({
        'url': $modifyFilterSetUrl,
        'method': 'POST',
        'data': {
            'id': $(this).data('filtersetId'),
            'FilterSets[is_range_slider]': state ? '1' : '0'
        },
        complete: function() {
            row
                .removeClass('info')
                .removeClass('success')
                .removeClass('danger');

            if (state && inherited) {
                row.addClass('info');
            } else if (state && !inherited) {
                row.addClass('success');
            } else if (!state && inherited) {
                row.addClass('danger');
            }
        }
    })
});

$(".psv_slug,.display_in_filter").change(function(){
    var that = $(this),
        data = {
            'id': $(this).data('staticValueId')
        };


    data['key'] = that.hasClass('psv_slug') ? 'slug' : 'dont_filter';
    data['value'] = that.hasClass('psv_slug') ? that.val() : (this.checked ? '0' : '1');


    $.ajax({
        'url': $modifyPSV,
        'method': 'POST',
        'data': data,
        success: function(result) {
            if (result) {
                that
                    .parent()
                    .addClass('success')
                    .removeClass('error');
            } else {
                that
                    .parent()
                    .addClass('error')
                    .removeClass('success');
            }
        }
    });
});
var saveSortedIds = function(ids, filterSets) {
    $.ajax({
        'url': $saveSortedUrl,
        'method': 'POST',
        'data': {
            'ids': ids,
            'filterSets': filterSets ? '1' : '0'
        }
    });
};
$(".properties-body").each(function(){
    var that = $(this);
    that.sortable({
        handle: '.sort-handle',
        helper: function(e, tr) {
            var originals = tr.children();
            var helper = tr.clone();
            helper.children().each(function(index)
            {
              // Set helper cell sizes to match the original sizes
              $(this).width(originals.eq(index).width());
            });
            return helper;
        },
        start: function(event, ui) {
            var dragged = ui.item,
            propertyId = dragged.attr('property-id'),
                psvTr = $("#psv-tr-"+propertyId);
            if (psvTr.length) {
                psvTr.collapse('hide');
            }
        },
        update: function(event, ui) {
            var dragged = ui.item,
                propertyId = dragged.attr('property-id'),
                psvTr = $("#psv-tr-"+propertyId);
            if (psvTr.length) {
                dragged.after(psvTr);
            }

            var sortedIds = _.filter(
                that.sortable('toArray', {attribute:'filterset-id'}),
                function (val) {
                    return val !== "";
                }
            );
            saveSortedIds(sortedIds, true);


        }
    }).disableSelection();

    var psvTbody = that.find('tbody');
    if (psvTbody.length) {
        psvTbody.sortable({
            handle: '.psv-sort-handle',
            helper: function(e, tr) {
                var originals = tr.children();
                var helper = tr.clone();
                helper.children().each(function(index)
                {
                  // Set helper cell sizes to match the original sizes
                  $(this).width(originals.eq(index).width());
                });
                return helper;
            },
            update: function(event, ui) {
                var sortedIds = _.filter(
                    $(this).sortable('toArray', {attribute:'psv-id'}),
                    function (val) {
                        return val !== "";
                    }
                );
                saveSortedIds(sortedIds, false);


            }
        }).disableSelection();
    }
});


JS;
$this->registerJs($js);
