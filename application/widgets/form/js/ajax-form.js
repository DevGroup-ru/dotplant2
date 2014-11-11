jQuery(function() {
    jQuery('form[data-type=form-widget]').submit(function() {
        var $form = jQuery(this);
        var $responseModal = jQuery('#modal-form-info-' + $form.attr('id'));
        var $response = jQuery('#form-info-' + $form.attr('id'));
        var $modal = jQuery('#modal-form-' + $form.attr('id'));
        var $btn = $form.find('[type=submit]').attr('disabled', 'disabled');
        jQuery.ajax({
            'complete' : function() {
                $btn.removeAttr('disabled');
            },
            'data' : $form.serialize(),
            'dataType' : 'text',
            'error' : function(error) {
                alert(error.statusText);
            },
            'type' : 'post',
            'url' : $form.attr('action'),
            'success' : function(response) {
                if (response == '1') {
                    $form.trigger('reset');
                    $modal.modal('hide');
                    if ($responseModal.length > 0) {
                        $responseModal.modal('show');
                    } else {
                        $response.fadeIn();
                    }
                } else {
                    alert('Ошибка при отправке формы. Проверьте правильность заполнения полей.');
                }
            }
        });
        return false;
    });
});