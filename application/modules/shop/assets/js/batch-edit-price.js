var peModalForm = '#batch-edit-price-form ',
    peConfirmForm = '#batch-edit-price-form-confirm ';

$(peModalForm + '#el_charge_kind').change(function () {
    if ($(this).val() == 'fixed')
        $(peModalForm + '.percent_text').hide();
    else
        $(peModalForm + '.percent_text').show();
});

$(peModalForm + '#el_type').change(function () {
    var store = $(peModalForm + 'div#to_hidden'),
        element = $(peModalForm + '#el_apply_for');

    $(peModalForm + '#apply_for > span').hide();
    if ($(this).val() == 'normal') {
        store.find('option[value="all"]').appendTo(element);
        $(peModalForm + '#apply_for > span.normal').show();
    } else {
        element.find('option[value="all"]').appendTo(store);
        $(peModalForm + '#apply_for > span.relative').show();
    }
});

$(peModalForm + '#el_round').click(function () {
    if ($(this).prop('checked')) {
        $(peModalForm + '.round_options').show();
    } else {
        $(peModalForm + '.round_options').hide();
    }
});

$(peModalForm + '[data-action="edit-prices"]').click(function () {
    // validation
    var isValid = true,
        value = $(peModalForm + '#el_value').val(),
        round = $(peModalForm + '#el_round_val').val();
    
    $(peModalForm + '#el_value').removeClass('error');
    $(peModalForm + '#el_round_val').removeClass('error');

    value = parseFloat(value.replace(',', '.'));
    if (isNaN(value)) {
        $(peModalForm + '#el_value').addClass('error');
        isValid = false;
    }

    round = parseInt(round);
    if ($(peModalForm + '#el_round').prop('checked') && isNaN(round)) {
        $(peModalForm + '#el_round_val').addClass('error');
        isValid = false;
    }

    if (isValid) {
        // prepare
        var data = {
            context: $(peModalForm + '#el_context').val(),
            kind: $(peModalForm + '#el_charge_kind').val(),
            type: $(peModalForm + '#el_type').val(),
            operation: $(peModalForm + '#el_operation').val(),
            apply_for: $(peModalForm + '#el_apply_for').val(),
            value: value,
            currency_id: $(peModalForm + '#el_currency').val(),
            is_round: $(peModalForm + '#el_round').prop('checked') ? 1 : 0,
            round_val: round,
            is_child_inc: $(peModalForm + '#el_child').prop('checked') ? 1 : 0,
            items: $(peModalForm).data('items'),
        };

        $(peConfirmForm).data('send', data);
        $(peConfirmForm + ' .alert-info').html(
            $(peModalForm).data('lang').confirm_edit
        );

        $(peModalForm).modal('hide');
        $(peConfirmForm + '#main_actions').show();
        $(peConfirmForm + '.alert-info').show();
        $(peConfirmForm + '#close_rep').hide();
        $(peConfirmForm + '.alert-success').hide();
        $(peConfirmForm + '.alert-danger').hide();
        $(peConfirmForm).modal('show');
    }
});

$(peConfirmForm + '[data-dismiss="edit-prices-cancel"]').click(function () {
    $(peConfirmForm).modal('hide');
    $(peModalForm).modal('show');
});

$(peConfirmForm + '[data-action="edit-prices-confirm"]').click(function () {
    lang = $(peModalForm).data('lang');
    $(peConfirmForm + '#main_actions').hide();
    $(peConfirmForm + '.alert-info').html(lang.wait);
    $.ajax({
        url: $(peModalForm).data('url'),
        type: 'post',
        data: $(peConfirmForm).data('send'),
        success: function (response) {
            $(peConfirmForm + '.alert-info').hide();
            $(peConfirmForm + '.alert-success').html(
                lang.total + ': ' + response.all + '<br>'
                + lang.updated + ': ' + response.success + '<br>'
            ).show();

            $(peConfirmForm + '#close_rep').show();
        },
        error: function (response) {
            $(peConfirmForm + '.alert-info').hide();
            $(peConfirmForm + '.alert-danger').html(response.responseText).show();
            $(peConfirmForm + '#close_rep').show();
        }
    });
});

$(peConfirmForm).on('hide.bs.modal', function() {
    window.location.reload();
});
