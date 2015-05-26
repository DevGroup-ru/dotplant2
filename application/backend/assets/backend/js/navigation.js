$(function () {

    function addProperty(key, value) {
        var index = $('.parameter').length;
        var compiled = _.template($("#parameter-template").html());

        var $property = $(compiled({'index' : index}));

        $property.find('#key_' + index).val(key);
        $property.find('#value_' + index).val(value);

        $property.find('.btn-remove').click(function () {
            $(this).parents('#parameter_' + index).remove();
            return false;
        });

        $("#properties").append($property);
    }


    $(".add-property").click(function () {
        addProperty('', '');
        return false;
    });

    $("#navigation-form").submit(function () {

        var serialized = {};
        $("#navigation-form .parameter").each(function () {
            var key = $(this).find('.param-key').val();
            var value = $(this).find('.param-val').val();
            serialized[key] = value;
        });

        $("#route_params").val(JSON.stringify(serialized));

        return true;
    });

    for (var c in current_params) {
        addProperty(c, current_params[c]);
    }
});