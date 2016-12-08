<?php
use yii\helpers\Html;
use app\properties\HasProperties;
?>
    <div id="properties-widget-<?=$widget_id?>">
        <?php
        $this->beginBlock('header-append');

        $items = [];
        $new_groups = [];

        foreach ($object_property_groups as $i => $opg) {
            $items[] = [
                'label' => $opg->group->name . ' ' . Html::tag(
                        'i',
                        '',
                        [
                            'class' => 'fa fa-times remove-property-group',
                            'data-pg' => json_encode(
                                [
                                    'id' => $opg->group->id,
                                    'form_name' => $model->formName(),
                                ]
                            )
                        ]
                    ),
                'url' => '#pg-' . $opg->group->id,
                'active' => ($i == 0),
                'linkOptions' => [
                    'data-toggle' => 'tab',
                ],
                'encode' => false,
            ];
        }

        foreach ($property_groups_to_add as $id => $name) {
            $new_groups[] = [
                'label' => $name,
                'url' => '#',
                'linkOptions' => [
                    'class' => 'add-property-group',
                    'data-pg' => json_encode(
                        [
                            'id' => $id,
                            'form_name' => $model->formName(),
                        ]
                    ),
                ],
            ];
        }

        ?>
        <div class="widget-toolbar">
            <?=
            yii\bootstrap\Nav::widget(
                [
                    'items' => $items,
                    'options' => [
                        'class' => 'nav-tabs',
                    ],
                ]
            );
            ?>
            <?php
            if (count($property_groups_to_add) > 0):?>
                <?=yii\bootstrap\ButtonDropdown::widget(
                    [
                        'label' => Yii::t('app', 'Add'),
                        'dropdown' => [
                            'items' => $new_groups,
                        ],
                        'options' => [
                            'class' => 'btn-xs',
                        ],
                    ]
                );?>
            <?php
            endif;
            ?>
        </div>
        <?php
        $this->endBlock();
        ?>

        <?php app\backend\widgets\BackendWidget::begin(
            [
                'title' => Yii::t('app', 'Properties'),
                'header_append' => $this->blocks['header-append'],
                'icon' => 'cubes',
                'footer' => $this->blocks['submit'],
            ]
        ); ?>
        <div class="tab-content">
            <?php
            $model->getAbstractModel()->setArrayMode(true);
            foreach ($object_property_groups as $i => $opg) {
                echo '<div class="tab-pane';
                if ($i == 0) {
                    echo ' active';
                }
                echo '" id="pg-' . $opg->group->id . '"">';
                $properties = app\models\Property::getForGroupId($opg->group->id);

                foreach ($properties as $prop) {
                    if ($property_values = $model->getPropertyValuesByPropertyId($prop->id)) {
                        echo $prop->handler($form, $model->getAbstractModel(), $property_values, 'backend_edit_view');
                    }
                }
                echo "</div>";
            }
            ?>
        </div>
    </div> <!-- /properties-widget -->
<?php app\backend\widgets\BackendWidget::end();
$js = <<<'JS'
$(function() {
    $("#properties-widget-%1$s .add-property-group").click(function() {
        var $form = $("#%2$s"),
            $this = $(this);
        var data = $this.data('pg');
        var $hidden = $('<input type="hidden">');
        $hidden.attr('name', '%3$s[' + data.form_name + ']').val(data.id);
        $form.append($hidden);
        $form.find(".btn-primary:submit:first").mouseup().click();

        return false;
    });
    $("#properties-widget-%1$s .remove-property-group").click(function() {
        var $form = $("#%2$s"),
            $this = $(this);
        var data = $this.data('pg');
        var $hidden = $('<input type="hidden">');
        $hidden.attr('name', '%4$s[' + data.form_name + ']').val(data.id);
        $form.append($hidden);
        $form.find(".btn-primary:submit:first").mouseup().click();
    });
});
JS;

$this->registerJs(sprintf($js,
    $widget_id, // %1$s
    $form->id,  // %2$s
    HasProperties::FIELD_ADD_PROPERTY_GROUP,   // %3$s
    HasProperties::FIELD_REMOVE_PROPERTY_GROUP // %4$s
));
