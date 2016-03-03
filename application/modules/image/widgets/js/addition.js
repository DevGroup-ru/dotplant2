(function ($) {
    "use strict";
    window.editNames = function () {
        var template = '<p class="name_edit"><input class="name" value="{filename}" /><span class="edit_success">✔</span><span class="edit_remove">✘</span></p>';
        $('.dropzone p.name').on('click', function () {
            var oldName = $(this).text();
            var $paragraph = $(this);
            $paragraph.parent().append(template.replace('{filename}', $(this).text()));
            var $form = $paragraph.parent().find('p.name_edit');
            $paragraph.hide();
            $form.find('.edit_success').on('click', function () {
                $.post('/backend/dashboard/rename-image', {
                    oldname: oldName,
                    newname: $form.find('input').val()
                }).done(function (data) {
                    $paragraph.text(data);
                    $paragraph.show();
                    $form.remove();
                });
            });
            $form.find('.edit_remove').on('click', function () {
                $paragraph.show();
                $form.remove();
            });
        });
    }
}(jQuery));