$(function () {

    function addProperty(property_id, selected) {
//        console.log($("#parameter-template").html());

        var compiled = _.template($("#parameter-template").html());
        var $property = $(compiled({ index: $('#properties .parameter').length  }));
        $property.find('.property_id').change(function () {
            var $select = $(this).parent().parent().find('.select'),
                $input = $(this).parent().parent().find('input.property-value-input');
            $select.empty().show().prop('disabled', false);
            $input.prop('disabled', true).hide();
            var property_id = $(this).val();
            if (property_id > 0) {
                if (static_values_properties[property_id]['has_static_values']) {
                    var static_values = static_values_properties[property_id]['static_values_select'];
                    for (var i in static_values) {
                        var $option = $('<option>');
                        $option
                            .val(i)
                            .html(static_values[i]);
                        $select.append($option);
                    }
                } else {
                    $input.prop('disabled', false).show();
                    $select.prop('disabled', true).hide();
                }
            }
        });
        $property.find('.btn-remove').click(function () {
            $(this).parent().parent().parent().parent().remove();
            return false;
        });
        if (property_id > 0 && selected) {
            $property.find('.property_id').val(property_id).change();
            if (static_values_properties[property_id]['has_static_values']) {
                $property.find('.select').val(selected);
                $property.find('input.property-value-input').prop('disabled', true).hide();
            } else {
                $property.find('input.property-value-input').val(selected);
                $property.find('.select').prop('disabled', true).hide();
            }
        }


        $("#properties").append($property);
    }

    $(".add-property").click(function () {
        addProperty(0, 0);
        return false;
    });

    $(".form-vertical").submit(function () {
        var serialized = {};
        $("#properties .parameter").each(function () {
            var
                key = $(this).find('.property_id').val(),
                $select = $(this).find('.select'),
                $input = $(this).find('input.property-value-input'),
                value = $select.prop('disabled') ? $input.val() : $select.val();


            serialized[key] = value;
        });

        $("#" + current_field_id).val(JSON.stringify(serialized));

        return true;
    });
    for (var c in current_selections) {
        addProperty(c, current_selections[c]);
    }
});
