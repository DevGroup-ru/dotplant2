/* global $, bootbox, jQuery */

(function($){
    "use strict";

    $.fn.dialogAction = function(url, dialogData, method, postData) {
        var that = $(this);

        if (that.hasClass('ladda-button') === false) {
            that.addClass('ladda-button');
            that.wrapInner('<span class="ladda-label"></span>');
            that.attr('data-style', 'expand-right');
            that.data('spinnerColor', '#fff');
        }


        var l = that.ladda();
        l.ladda('start');

        if (method === undefined) {
            method = 'GET';
        }

        $.ajax({
            'url': url,
            'method': method,
            'data': postData,
            success: function(data) {

                dialogData.message = $(data);
                bootbox.dialog(dialogData);

                l.ladda('stop');
            },
            error: function(xhr, status, errorThrown) {
                l.ladda('stop');
            }
        });
        return this;
    };
}( jQuery ));


