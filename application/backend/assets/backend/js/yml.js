$(function () {
    $('#offers_section').on('change', 'select[data-ymlselect]', function (event, flag) {
        var selType = $(this).data('ymlselect');
        var group = $(this).parents('.offer-group');

        if ('type' == selType) {
            $(group).find('select[data-ymlselect="key"]').trigger('change', ['change']);
        }
        else if ('key' == selType) {
            var keyType = $(group).find('select[data-ymlselect="type"]').val();

            if ('change' == flag) {
                if ('field' == keyType) {
                    $(this).html(ymlSelectFields);
                }
                else if ('property' == keyType) {
                    $(this).html(ymlSelectProperties);
                }
                else if ('relation' == keyType) {
                    $(this).html(ymlSelectRelKeys);
                }

                $(group).find('select[data-ymlselect="value"]').trigger('change', ['change']);
            }
            else {
                if ('relation' == keyType) {
                    $(group).find('select[data-ymlselect="value"]').trigger('change', ['change']);
                }
            }
        }
        else if ('value' == selType) {
            var type = $(group).find('select[data-ymlselect="type"]').val();
            var key = $(group).find('select[data-ymlselect="key"]').val();
            if ('change' == flag) {
                if ('relation' == type) {
                    $(this).html(ymlSelectRelations[key]);
                    $(this).show();
                }
                else {
                    $(this).val('');
                    $(this).hide();
                }
            }
        }

        return true;
    });
});