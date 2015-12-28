/* global $, interval, lastSendTS, heartbeatUrl, modelId, activate jQuery */
(function ($) {
    "use strict";

    window.onbeforeunload = function () {
        $.post(window.heartbeatUrl, {
            modelId: window.modelId,
            action : 'close'
        });
        //return false;
    }
    $(document).ready(function () {
        window.activate = window.activate || false;
        if (window.activate) {
            runHearbeatTrack();
        }
    });

    function runHearbeatTrack() {
        window.lastSendTS = Date.now();
        window.interval = window.interval || 1000;
        $('body').on('keypress.heartbeat mousedown.heartbeat mousemove.heartbeat click.heartbeat', function (e) {
            var current = Date.now();
            var diff = current - lastSendTS;
            if (diff >= interval / 2) {
                window.lastSendTS = current;
                return heartbeat();
            }
        });
    }

    function heartbeat() {
        try {
            $.post(window.heartbeatUrl, {
                modelId: window.modelId
            }, function (data) {
                if (data.hasOwnProperty('is_main')) {
                    if (!data.is_main) {
                        //modal show
                        $('#content form').submit(function (e) {
                            e.preventDefault();
                            $(this).off('submit');
                            return false;
                        });
                        $('#content button.btn').attr('disabled', 'disabled');
                        $('.fileinput-button,#elfinderFileInput_button').remove();
                        //$('body').off('.heartbeat');
                        return false;
                    }
                } else {
                    $('body').off('.heartbeat');
                }
            }).fail(function () {
                $('body').off('.heartbeat');
            });
        } catch (e) {
            $('body').off('.heartbeat');
            console.log(e);
            return true;
        }
    }
}(jQuery));