<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use app\models\Property;
/**
 * @var int $category_group_id
 * @var array $possible_selections
 * @var \yii\web\View $this
 * @var string $id
 * @var array $current_selections
 * @var int $object_id
 * @var string $title
 * @var string $go_back_alignment
 * @var string $route
 * @var array $disabled_ids
 */
use kartik\form\ActiveForm;

$use_links = isset($filterLinks);
$onlyUncheckSlug = isset($uncheckSlugOnly);

?>
<?php $form = ActiveForm::begin(['id' => 'view-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>
<div id="<?= $id ?>" class="filter-container">
    <div class="filter-widget filter-static">
        <?php if (!empty($title)): ?>
            <div class="filter-title">
                <?= $title ?>
            </div>
        <?php endif; ?>
        <?php foreach ($possible_selections as $group_id => $item): ?>
            <?php
                /** @var \app\models\PropertyGroup $group */
                $group = $item['group'];
            ?>
            <ul class="filter-group nav nav-tabs nav-stacked">
                <?php if ($group->hidden_group_title == "0"): ?>
                    <li class="title">
                        <?= Html::encode($group->name) ?>
                    </li>
                <?php endif; ?>
                <?php
                    foreach ($item['static_selections'] as $property_id => $select) {
                        $selects = [];
                        $property = Property::findById($property_id);
                        if ($category_group_id > 0 && $property->depends_on_category_group_id > 0 && $property->depends_on_category_group_id != $category_group_id) {
                            continue;
                        }
                        if ($property->display_only_on_depended_property_selected) {
                            $depended_property_id = $property->depends_on_property_id;
                            if (isset($current_selections['properties'][$depended_property_id])) {
                                $values_allowed = explode(",", $property->depended_property_values);
                                if (!in_array($current_selections['properties'][$depended_property_id][0], $values_allowed)) {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        }
                        if (count($select) > 0): ?>
                            <li class="property-name property-<?= $property_id ?>">
                                <div class="name"><?= Html::encode($property->name); ?></div>
                            <?php
                                $hide_others = false;
                                $dont_hide_value_id = null;
                                if (Property::findById($property_id)->hide_other_values_if_selected) {
                                    if (isset($current_selections['properties'][$property_id])) {
                                        $hide_others = true;
                                        $dont_hide_value_id = $current_selections['properties'][$property_id][0];
                                    }
                                }
                                $checkboxes = [];
                                foreach ($select as $value) {
                                    if ($hide_others) {
                                        if ($dont_hide_value_id != $value['id']) {
                                            continue;
                                        }
                                    }
                                    $options = [
                                        'class' => 'filter-select ',
                                    ];
                                    $params = [$route];
                                    $params += $current_selections;
                                    $params['category_group_id'] = $category_group_id;
                                    foreach ($possible_selections as $u_group_id => $u_item) {
                                        foreach ($u_item['static_selections'] as $u_property_id => $select) {
                                            if (Property::findById($u_property_id)->depends_on_property_id == $property_id) {
                                                unset($params['properties'][$u_property_id]);
                                            }
                                        }
                                    }
                                    $active = false;
                                    if (isset($params['properties'][$property_id]) && is_array($params['properties'][$property_id])) {
                                        if (in_array($value['id'], $params['properties'][$property_id])) {
                                            $options['class'] .= 'active';
                                            $active=true;
                                        }
                                    }
                                    $params['properties'][$property_id] = [$value['id']];

                                    $disabled = in_array($value['id'], $disabled_ids);
                                    $disabled_array = $disabled ? ['disabled'=>'disabled'] : [];

                                    $label = $value['name'];
                                    $go_back = '';
                                    if ($use_links === true) {
                                        $should_link = true;

                                        if (isset($current_selections['properties'][$property_id])) {
                                            if (isset($current_selections['properties'][$property_id][0])) {

                                                $params_clone = $params;

                                                if ($current_selections['properties'][$property_id][0] == $value['id'] || !$onlyUncheckSlug) {
                                                    if ($current_selections['properties'][$property_id][0] == $value['id']) {
                                                        unset($params_clone['properties'][$property_id]);
                                                    }
                                                    $go_back = Url::toRoute($params_clone);
                                                    $should_link = false;
                                                }

                                            }
                                        }
                                        if ($should_link === true) {
                                            $url = Url::toRoute($params);
                                            $label = Html::a($value['name'], $url, ['class'=>'filter-link']);
                                        }
                                    }

                                    $checkbox = Html::tag(
                                        'div',
                                        Html::label(
                                            Html::checkBox(
                                                "properties[$property_id][]",
                                                $active,
                                                [
                                                    'class' => 'filter-checkbox',
                                                    'id' => "p_{$property_id}_{$value['id']}",
                                                    'value'=>$value['id'],
                                                    'data-goback' => $go_back
                                                ]
                                            )." ".$label,
                                            "p_{$property_id}_{$value['id']}",
                                            [
                                                'class'=>($disabled?'muted':'')
                                            ]
                                        ),
                                        [
                                            'class' => '',
                                        ]
                                    );
                                    if ($disabled) {
                                        array_push($checkboxes, $checkbox);
                                    } else {
                                        array_unshift($checkboxes, $checkbox);
                                    }


                                }

                                echo implode("\n", $checkboxes);
                            ?>
                            </li>
                            <?php
                        endif;
                    }
                ?>

                <?php


                ?>

            </ul>
        <?php endforeach;?>
        <div class="form-submit">
            <button class="btn filter-submit"><?= Yii::t('app', 'Show')?></button>
        </div>
    </div>


</div>
<?php ActiveForm::end(); ?>

<script>
    $(function(){
        <?php if ($use_links): ?>
        $("#<?=$id?> .filter-checkbox").change(function(){
            if ($(this).data('goback') !== '') {
                document.location = $(this).data('goback');
            }
            return true;
        });
        <?php endif; ?>
    })
</script>