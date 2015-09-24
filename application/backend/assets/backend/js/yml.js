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
                switch (keyType) {
                    case 'field':
                        $(this).html(ymlSelectFields);
                        break;
                    case 'property':
                        $(this).html(ymlSelectProperties);
                        break;
                    case 'relation':
                        $(this).html(ymlSelectRelKeys);
                        break;
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

    if (true === $('#yml-offer_param').prop('checked')) {
        $('#yml-properties-section').show();
    } else {
        $('#yml-properties-section').hide();
    }
    $('#yml-offer_param').change(function(){
        if (true === $(this).prop('checked')) {
            $('#yml-properties-section').show();
        } else {
            $('#yml-properties-section').hide();
        }
    });
    $('input[name=use_in_file]').change(function () {
        var $data = {
                "id": $(this).data('id'),
                "name": $(this).attr('name'),
                "val": $(this).prop('checked') ? 1 : 0
            },
            $elem = $(this);
        justDoIt($data, $elem);
    });
    $('input[name=unit]').blur(function () {
        var $data = {
                "id": $(this).data('id'),
                "name": $(this).attr('name'),
                "val": $(this).val()
            },
            $elem = $(this);
        justDoIt($data, $elem);
    });
    function justDoIt($data, $elem) {
        $.post($url, $data, function (response) {
            $elem.parent().addClass('has-warning');
            if (1 === response) {
                $elem.parent().removeClass('has-warning').addClass('has-success');
            } else {
                $elem.parent().removeClass('has-warning').removeClass('has-success').addClass('has-error');
            }
        }, "json")
    }
});