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
 */

?>

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
                                    $go_back = '';
                                    if (isset($params['properties'][$property_id]) && is_array($params['properties'][$property_id])) {
                                        if (in_array($value['id'], $params['properties'][$property_id])) {
                                            $options['class'] .= 'active';
                                            if ($go_back_alignment != 'none'){
                                                $params_clone = $params;
                                                unset($params_clone['properties'][$property_id]);
                                                $url = Url::toRoute($params_clone);
                                                $go_back = Html::a(
                                                    Html::tag('i', '', ['class' => 'fa fa-times']),
                                                    $url,
                                                    ['class'=>'go-back']
                                                );
                                            }
                                        }
                                    }
                                    $params['properties'][$property_id] = [$value['id']];
                                    $url = Url::toRoute($params);
                                    $label = Html::a($value['name'], $url);
                                    if ($go_back_alignment === 'left') {
                                        $label = $go_back.$label;
                                    } elseif ($go_back_alignment === 'right') {
                                        $label .= $go_back;
                                    }
                                    $selects[] = Html::tag(
                                        'li',
                                        $label,
                                        $options
                                    );
                                }
                                echo Html::tag('ul', implode("\n", $selects), ['class'=>'property property-'.$property_id]);
                            endif;
                    }
                ?>
                </li>
            </ul>
        <?php endforeach;?>
    </div>
    <?php if ($render_dynamic === true) : ?>
    <div class="filter-widget filter-dynamic">
        <?= Html::beginForm('', 'get', ['class' => 'filter-form']) ?>
            <?php foreach ($possible_selections as $group_id => $item): ?>
                <?php
                    /** @var \app\models\PropertyGroup $group */
                    $group = $item['group'];
                ?>
                <div class="filter-group">
                    <?php if ($group->hidden_group_title == "0"): ?>
                        <div class="title">
                            <?= Html::encode($group->name) ?>
                        </div>
                    <?php endif;?>
                    <?php
                    if (isset($item['dynamic_selections'])):
                        foreach ($item['dynamic_selections'] as $property_id):
                            $property = Property::findById($property_id);
                    ?>
                    <div class="property-name property-<?= $property_id ?>">
                        <?= Html::encode($property->name); ?>
                    </div>
                        <?php
                            $minval = isset(Yii::$app->request->get("p", [])[$property->id]['min'])
                                ? Yii::$app->request->get("p", [])[$property->id]['min']
                                : '';
                            $maxval = isset(Yii::$app->request->get("p", [])[$property->id]['max'])
                                ? Yii::$app->request->get("p", [])[$property->id]['max']
                                : '';
                        ?>
                    <div class="input-group input-group-sm">
                        <?= Html::textInput(
                            "p[{$property->id}][min]",
                            $minval,
                            ['class' => 'form-control', 'placeholder' => 'from', 'style' => 'margin-bottom:5px;']
                        ) ?>
                    </div>
                    <div class="input-group input-group-sm">
                        <?= Html::textInput(
                            "p[{$property->id}][max]",
                            $maxval,
                            ['class' => 'form-control', 'placeholder' => 'to']
                        ) ?>
                    </div>
                    <?php
                    endforeach;
                    endif;
                    ?>
                </div>
            <?php endforeach; ?>
        <?= Html::endForm() ?>
    </div>
    <?php endif; ?>
</div>