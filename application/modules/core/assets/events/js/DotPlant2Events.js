/* globals $: false */

"use strict";

var DotPlant2Events = {
    EventGathenerUrl: '/events-beacon',
    EventsDeliveryInterval: 5000,

    EventQueue: [],

    RegisterTrigger: function(
        eventName,
        triggeringType,
        jsData,
        selector
    ) {
        var
            EventData = {
                'eventName': eventName,
                'data': jsData
            };

        switch (triggeringType) {
            case 'javascript_immediate':
                DotPlant2Events.sendEvent(EventData);
                break;
            case 'javascript_reveal':
                DotPlant2Events.setRevealAndTimeout(EventData, selector, 0);
                break;
            case 'javascript_reveal_5sec':
                DotPlant2Events.setRevealAndTimeout(EventData, selector, 5000);
                break;
            case 'javascript_reveal_15sec':
                DotPlant2Events.setRevealAndTimeout(EventData, selector, 15000);
                break;
            case 'javascript_reveal_30sec':
                DotPlant2Events.setRevealAndTimeout(EventData, selector, 30000);
                break;

        }
    },

    setRevealAndTimeout: function(eventData, selector, timeout) {
        if (timeout > 0) {
            timeout = 1; // 1ms is good timeout for not rewriting the code!
        }
        // timeout was specified
        $(selector).on('inview', function (e, isInView, visiblePartX, visiblePartY) {
            // code example same as in https://github.com/protonet/jquery.inview
            var elem = $(this);

            if (elem.data('inviewtimer')) {
                clearTimeout(elem.data('inviewtimer'));
                elem.removeData('inviewtimer');
            }

            if (isInView) {
                elem.data('inviewtimer', setTimeout(function () {
                    if (visiblePartY == 'top') {
                        elem.data('seenTop', true);
                    } else if (visiblePartY == 'bottom') {
                        elem.data('seenBottom', true);
                    } else {
                        elem.data('seenTop', true);
                        elem.data('seenBottom', true);
                    }

                    if (elem.data('seenTop') && elem.data('seenBottom')) {
                        elem.unbind('inview');

                        DotPlant2Events.sendEvent(eventData);

                    }
                }, timeout));
            }
        });

    },

    sendEvent: function(
        data,
        force
    ) {
        var localTimestamp = new Date(),
            UTCSeconds = (localTimestamp.getTime() + localTimestamp.getTimezoneOffset()*60*1000)/1000;

        DotPlant2Events.EventQueue.push(
            {
                timestamp: UTCSeconds,
                event: data.data,
                eventName: data.eventName
            }
        );

        if (force === true) {
            DotPlant2Events.deliverEvents();
        }
    },

    deliverEvents: function(allow_beacon)
    {
        if (DotPlant2Events.EventQueue.length === 0) {
            return;
        }

        var eventsData = JSON.stringify(DotPlant2Events.EventQueue);
        DotPlant2Events.EventQueue = [];

        if (navigator.sendBeacon && allow_beacon) {

            navigator.sendBeacon(DotPlant2Events.EventGathenerUrl, eventsData);

        } else {

            $.ajax({
                url: DotPlant2Events.EventGathenerUrl + '?ajax=1',
                type: 'POST',
                data: eventsData,
                dataType : "json",
                success: function(json) {
                    console.log(json);
                }
            });

        }

    }
};

window.addEventListener('unload', function(event) {
   DotPlant2Events.deliverEvents(true);
});

setInterval(
    DotPlant2Events.deliverEvents, DotPlant2Events.EventsDeliveryInterval
);