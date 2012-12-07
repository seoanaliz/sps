var Configs = {
    limit: 150,
    controlsRoot: controlsRoot,
    eventsDelay: 0,
    eventsIsDebug: true
};

$.mask.definitions['2']='[012]';
$.mask.definitions['3']='[0123]';
$.mask.definitions['5']='[012345]';

$(document).ready(function() {
    new Pages();
});

var Pages = Class.extend({
    monitor: null,
    result: null,
    currentPage: null,

    init: function() {
        var t = this;
        $('#main').html(tmpl(REPORTS.MAIN));
        var $header = $('#header');
        $header.html(tmpl(REPORTS.HEADER));
        t.monitor = new Monitor();
        t.result = new Result();
        t.monitor.update();

        $('#tab-results').click(function() {
            t.showResults();
            $header.find('.tab').removeClass('selected');
            $(this).addClass('selected');
        });

        $('#tab-monitors').click(function() {
            t.showMonitors();
            $header.find('.tab').removeClass('selected');
            $(this).addClass('selected');
        });
    },

    showResults: function() {
        var t = this;
        t.result.update();
    },

    showMonitors: function() {
        var t = this;
        t.monitor.update();
    }
});

var Page = Event.extend({
    inited: null,

    update: function() {},
    bindEvents: function() {},
    makeTime: function($elements) {
        $elements.each(function() {
            var $date = $(this);
            var timestamp = $date.text();
            if (!intval(timestamp)) return;
            var currentDate = new Date();
            var date = new Date(timestamp * 1000);
            var m = date.getMonth() + 1;
            var y = date.getFullYear() + '';
            var d = date.getDate() + '';
            var h = date.getHours() + '';
            var min = date.getMinutes() + '';
            var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
            if (currentDate.getDate() != d) {
                text += ', ' + d + '.' + m + '.' + (y.substr(-2));
            }
            $date.html(text);
        });
    }
});

var Result = Page.extend({
    update: function() {
        var t = this;
        var $listAddMonitor = $('#list-add-monitor');
        var $listHeader = $('#list-header');
        var $results = $('#results');

        if (!t.inited) {
            t.inited = true;
        }

        Events.fire('get_result_list', Configs.limit, 0, function(data) {
            $listAddMonitor.slideUp(200);
            $listHeader.html(tmpl(REPORTS.RESULT.LIST_HEADER));
            $results.html(tmpl(REPORTS.RESULT.LIST, {items: data}));
            t.makeTime($results.find('.time'));
        });
    }
});

var Monitor = Page.extend({
    update: function() {
        var t = this;
        var $listAddMonitor = $('#list-add-monitor');
        var $listHeader = $('#list-header');
        var $results = $('#results');

        if (!t.inited) {
            t.inited = true;
            $listAddMonitor.html(tmpl(REPORTS.MONITOR.LIST_ADD_MONITOR));
            $('#time-start').mask('29:59');
            $('#time-end').mask('29:59');
            $('#datepicker').datepicker({
                dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
                monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                firstDay: 1,
                showAnim: '',
                dateFormat: 'd MM'
            });
            t.bindEvents();
        }

        Events.fire('get_monitor_list', Configs.limit, 0, function(data) {
            $listAddMonitor.slideDown(200);
            $listHeader.html(tmpl(REPORTS.MONITOR.LIST_HEADER));
            $results.html(tmpl(REPORTS.MONITOR.LIST, {items: data}));
            t.makeTime($results.find('.time'));
        });
    },

    bindEvents: function() {
        var t = this;

        $('#addReport').click(function() {
            var $inputs = $('#list-add-monitor').find('input');
            var $breakingInput = $(this);
            var isValid = true;
            $inputs.each(function() {
                if ($(this).data('required') && !$.trim($(this).val())) {
                    isValid = false;
                    $breakingInput = $(this);
                    return false;
                }
            });
            if (!isValid) {
                $breakingInput.focus();
                return;
            }

            var ourPublicId = $.trim($('#our-public-id').val());
            var publicId = $.trim($('#public-id').val());
            var dirtyTimeStart = ($('#time-start').val() || '__:__').split('_').join('0').split(':');
            var dateStart = $('#datepicker').datepicker('getDate');
            dateStart.setHours(dirtyTimeStart[0]);
            dateStart.setMinutes(dirtyTimeStart[1]);
            var timestampStart = Math.round(dateStart.getTime() / 1000);
            var timestampEnd = null;

            if ($('#time-end').val()) {
                var dirtyTimeEnd = ($('#time-end').val() || '__:__').split('_').join('0').split(':');
                var dateEnd = new Date();
                dateEnd.setHours(dirtyTimeEnd[0]);
                dateEnd.setMinutes(dirtyTimeEnd[1]);
                timestampEnd = Math.round(dateEnd.getTime() / 1000);
            }

            Events.fire('add_report', ourPublicId, publicId, timestampStart, timestampEnd, function() {
                t.update();
            });
        });
    }
});
