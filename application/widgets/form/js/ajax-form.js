jQuery(function() {
    jQuery('form[data-type=form-widget]').on('beforeSubmit', function(e) {
        e.preventDefault();
        var $form = jQuery(this);
        var $responseModal = jQuery('#modal-form-info-' + $form.attr('id'));
        var $response = jQuery('#form-info-' + $form.attr('id'));
        var $modal = jQuery('#modal-form-' + $form.attr('id'));
        var $btn = $form.find('[type=submit]').attr('disabled', 'disabled');
        var $formData = new FormData($form[0]);
        var dataType = $form.attr('enctype') == 'multipart/form-data' ? 'file' : 'text';
        if(dataType == 'file'){
            $form.find(':file').each(function(indx, input){
                $formData.append($(input).attr('name'), input.files[0]);
            });
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', $form.attr('action'), true );
        xhr.send($formData);
        xhr.onreadystatechange = function(response) {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    if (xhr.responseText != '0') {
                        $form.trigger('reset');
                        $modal.modal('hide');
                        if ($responseModal.length > 0) {
                            $responseModal.modal('show');
                        } else {
                            $response.fadeIn();
                        }
                        $form.trigger('DP:formAfterSubmit', [xhr.responseText]);
                    } else {
                        alert('Ошибка при отправке формы. Проверьте правильность заполнения полей.');
                    }
                } else {
                    alert(xhr.statusText)
                }
                $btn.removeAttr('disabled');
            }
        };
        return false;
    });
});
