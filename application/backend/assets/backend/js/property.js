$(function () {

    function addProperty(property_id, selected) {
        console.log($("#parameter-template").html());

        var compiled = _.template($("#parameter-template").html());
        var $property = $(compiled({ index: $('#properties .parameter').length  })
        );
        $property.find('.property_id').change(function () {
            var $select = $(this).parent().parent().find('.select');
            $select.empty();
            var property_id = $(this).val();
            if (property_id > 0) {
                var static_values = static_values_properties[property_id]['static_values_select'];
                for (var i in static_values) {
                    var $option = $('<option>');
                    $option
                        .val(i)
                        .html(static_values[i]);
                    $select.append($option);
                }
            }
        });
        $property.find('.btn-remove').click(function () {
            $(this).parent().parent().parent().parent().remove();
            return false;
        });
        if (property_id > 0 && selected > 0) {
            $property.find('.property_id').val(property_id).change();
            $property.find('.select').val(selected);
        }


        $("#properties").append($property);
    }

    $(".add-property").click(function () {
        addProperty(0, 0);
        return false;
    });

    $(".form-vertical").submit(function () {
        var $input = $("#" + current_field_id);
        $input.val();

        var serialized = {};
        $("#properties .parameter").each(function () {
            var key = $(this).find('.property_id').val();
            var value = $(this).find('.select').val();
            serialized[key] = value;
        });

        $("#" + current_field_id).val(JSON.stringify(serialized));

        return true;
    });
    for (var c in current_selections) {
        addProperty(c, current_selections[c]);
    }
});