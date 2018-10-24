/**
 * @copyright Copyright (c) 2016 www.tigren.com
 */
(function ($) {
    $.fn.dealcountdown = function (options) {
        return this.each(function () {
            var thisJNode = $(this);
            var timer = setTimeout(function () {
                dealcountdown(thisJNode, options);
            }, 1000);
        });
    }
    function dealcountdown(timeleft_selector, options) {
        var now_time = (new Date()).getTime() - (new Date().getTimezoneOffset() * 60 * 1000);
        var now = new Date(now_time);
        var to_time = (parseInt(timeleft_selector.data('totime')) * 1000);
        var toDate = new Date(to_time);

        var sec = toDate.getSeconds() - now.getSeconds();
        var min = toDate.getMinutes() - now.getMinutes();
        var hr = toDate.getHours() - now.getHours();
        var dy = toDate.getDate() - now.getDate();
        var mnth = toDate.getMonth() - now.getMonth();
        var yr = toDate.getFullYear() - now.getFullYear();

        var daysinmnth = 32 - new Date(now.getFullYear(), now.getMonth(), 32).getDate();
        if (sec < 0) {
            sec = (sec + 60) % 60;
            min--;
        }
        if (min < 0) {
            min = (min + 60) % 60;
            hr--;
        }
        if (hr < 0) {
            hr = (hr + 24) % 24;
            dy--;
        }
        if (dy < 0) {
            dy = (dy + daysinmnth) % daysinmnth;
            mnth--;
        }
        if (mnth < 0) {
            mnth = (mnth + 12) % 12;
            yr--;
        }
        var sectext = "secs";
        var mintext = "mins";
        var hrtext = "hours";
        var dytext = " days";
        var mnthtext = " months";
        var yrtext = " years";
        if (yr == 1)
            yrtext = " year";
        if (mnth == 1)
            mnthtext = " month";
        if (dy == 1)
            dytext = " day";
        if (hr == 1)
            hrtext = "hour";
        if (min == 1)
            mintext = "min";
        if (sec == 1)
            sectext = "sec";

        if (dy < 10)
            dy = '0' + dy;
        if (hr < 10)
            hr = '0' + hr;
        if (min < 10)
            min = '0' + min;
        if (sec < 10)
            sec = '0' + sec;

        if (yr <= 0)
            yrtext = ''
        else
            yrtext = '<li><span class="timeleft-value">' + yr + '</span><span class="timeleft-label">' + yrtext + '</span></li>'
        if ((mnth <= 0))
            mnthtext = ''
        else
            mnthtext = '<li><span class="timeleft-value">' + mnth + '</span><span class="timeleft-label">' + mnthtext + '</span></li>';

        if (dy <= 0)
            dytext = ''
        else
            dytext = '<li><span class="timeleft-value">' + dy + '</span><span class="timeleft-label">' + dytext + '</span></li>';

        if (hr <= 0)
            hrtext = ''
        else
            hrtext = '<li><span class="timeleft-value">' + hr + '</span><span class="timeleft-label">' + hrtext + '</span></li>';

        if (min < 0)
            mintext = ''
        else
            mintext = '<li><span class="timeleft-value">' + min + '</span><span class="timeleft-label">' + mintext + '</span></li>';

        if (sec < 0)
            sectext = ''
        else
            sectext = '<li><span class="timeleft-value">' + sec + '</span><span class="timeleft-label">' + sectext + '</span></li>';
        if (now_time >= to_time) {
            timeleft_selector.html('');
        } else {
            timeleft_selector.html('<ul class="dailydeal-countdown">' + yrtext + mnthtext + dytext + hrtext + mintext + sectext + '</ul>');
            var timer = setTimeout(function () {
                dealcountdown(timeleft_selector, options);
            }, 1000);
        }
        
        if (now_time == to_time) {
            location.reload(true);
            return;
        }
    }
})(jQuery)
