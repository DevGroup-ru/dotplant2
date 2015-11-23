var chrt_border_color = "#efefef";
var chrt_grid_color = "#DDD"
var chrt_main = "#E24913";
/* red       */
var chrt_second = "#6595b4";
/* blue      */
var chrt_third = "#FF9F01";
/* orange    */
var chrt_fourth = "#7e9d3a";
/* green     */
var chrt_fifth = "#BD362F";
/* dark red  */
var chrt_mono = "#000";
$(function () {
    (function ($) {
        if ($("#saleschart").length) {
            var d = jsOrders;

            for (var i = 0; i < d.length; ++i)
                d[i][0] += 60 * 60 * 1000;

            function weekendAreas(axes) {
                var markings = [];
                var d = new Date(axes.xaxis.min);
                // go to the first Saturday
                d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
                d.setUTCSeconds(0);
                d.setUTCMinutes(0);
                d.setUTCHours(0);
                var i = d.getTime();
                do {
                    // when we don't set yaxis, the rectangle automatically
                    // extends to infinity upwards and downwards
                    markings.push({
                        xaxis: {
                            from: i,
                            to: i + 2 * 24 * 60 * 60 * 1000
                        }
                    });
                    i += 7 * 24 * 60 * 60 * 1000;
                } while (i < axes.xaxis.max);

                return markings;
            }

            var options = {
                xaxis: {
                    mode: "time",
                    tickLength: 5
                },
                series: {
                    lines: {
                        show: true,
                        lineWidth: 1,
                        fill: true,
                        fillColor: {
                            colors: [{
                                opacity: 0.1
                            }, {
                                opacity: 0.15
                            }]
                        }
                    },
                    points: {show: true},
                    shadowSize: 0
                },
                selection: {
                    mode: "x"
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    tickColor: chrt_border_color,
                    borderWidth: 0,
                    borderColor: chrt_border_color,
                },
                tooltip: true,
                tooltipOpts: {
                    content: tooltipTpl,
                    dateFormat: dateFormat,
                    defaultTheme: false
                },
                colors: [chrt_second],

            };

            var plot = $.plot($("#saleschart"), [d], options);
        };
    })(jQuery);
});
