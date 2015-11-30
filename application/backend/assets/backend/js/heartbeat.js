/* global $, interval, lastSendTS, heartbeatUrl jQuery */
(function ($) {
    "use strict";
    function runHearbeatTrack() {
        window.lastSendTS = Date.now();
        window.interval = window.interval || 1000;
        $('body').on('keypress mousedown mousemove click', function (e) {
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
            $.post(window.heartbeatUrl, function (data) {
                if (!data.is_main) {
                    console.log('oh no!');
                    return false;
                }

            });
        } catch (e) {
            return true;
        }
    }

    runHearbeatTrack();
}(jQuery));