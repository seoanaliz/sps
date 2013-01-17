var Configs = {
    limit: 150,
    controlsRoot: controlsRoot,
    eventsDelay: 0,
    eventsIsDebug: true
};

$.mask.definitions['2']='[012]';
$.mask.definitions['3']='[0123]';
$.mask.definitions['5']='[012345]';
$.datepicker.setDefaults({
    dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
    dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
    monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
    monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
    firstDay: 1,
    showAnim: '',
    dateFormat: 'd MM'
});

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
    sort: '',
    sortReverse: false,
    offset: 0,
    limit: Configs.limit,

    update: function() {},
    bindEvents: function() {},
    getTime: function(timestamp) {
        var date = timestamp ? new Date(timestamp * 1000) : new Date();
        var h = date.getHours() + '';
        var min = date.getMinutes() + '';
        return (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
    },
    getDate: function(timestamp) {
        var date = timestamp ? new Date(timestamp * 1000) : new Date();
        var m = date.getMonth() + 1;
        var y = date.getFullYear() + '';
        var d = date.getDate() + '';
        return d + '.' + m + '.' + (y.substr(-2));
    },
    getDiffTime: function(time) {
        var h = Math.floor(time / 3600);
        var m = Math.floor(time / 60) - h * 60;
        return h + ' ч. ' + m + ' м.';
    },
    makeTime: function($elements) {
        var t = this;
        var tmpDate = new Date();
        $elements.each(function() {
            var $date = $(this);
            var timestamp = $date.text() * 1;
            var time_shift = tmpDate.getTimezoneOffset() * 60 + 14400;
            timestamp += time_shift;
            if (!intval(timestamp)) return;
            $date.html(t.getTime(timestamp));
        });
    },
    makeDate: function($elements) {
        var t = this;
        var tmpDate = new Date();
        $elements.each(function() {
            var $date = $(this);
            var timestamp = $date.text() * 1;
            var time_shift = tmpDate.getTimezoneOffset() * 60 + 14400;
            timestamp += time_shift;
            if (!intval(timestamp)) return;
            $date.html(t.getDate(timestamp));
        });
    },
    makeFullTime: function($elements) {
        var t = this;
        var tmpDate = new Date();
        $elements.each(function() {

            var $date = $(this);
            var timestamp = $date.text() * 1;
            var time_shift = tmpDate.getTimezoneOffset() * 60 + 14400;
            timestamp += time_shift;
            if (!intval(timestamp)) return;
            $date.html(t.getTime(timestamp) + ', ' + t.getDate(timestamp));
        });
    },
    makeDiffTime: function($elements) {
        var t = this;
        $elements.each(function() {
            var $date = $(this);
            var time = $date.text();
            if (!intval(time)) return;
            $date.html(t.getDiffTime(time));
        });
    },
    bindDeleteEvent: function() {
        var t = this;
        $('.icon.delete').click(function() {
            var $row = $(this).closest('.row');
            var confirmBox = new Box({
                title: 'Удаление',
                html: 'Вы уверены, что хотите удалить отчет?',
                buttons: [
                    {label: 'Удалить', onclick: deleteReport},
                    {label: 'Отмена', isWhite: true}
                ]
            }).show();

            function deleteReport() {
                confirmBox.hide();
                Events.fire('delete_report', $row.data('report-id'),12, function() {
                    $row.slideUp(200);
                });
            }
        });
    },
    showMore: function() {}
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
            $('#datepicker').datepicker().datepicker('setDate', new Date().getTime());
            $('#filter_datepicker').datepicker().datepicker('setDate', new Date().getTime());
        }

        Events.fire('get_monitor_list', t.limit, t.offset, function(data) {
            $listAddMonitor.slideDown(200);
            $listHeader.html(tmpl(REPORTS.MONITOR.LIST_HEADER));
            $results.html(tmpl(REPORTS.MONITOR.LIST, {items: data}));
            t.makeTime($results.find('.time'));
            t.makeDate($results.find('.date'));
            t.bindEvents();
        });
    },

    bindEvents: function() {
        var t = this;
        var $addReport = $('#addReport');

        t.bindDeleteEvent();

        if (!$addReport.data('inited')) {
            $addReport.data('inited', true);
            $addReport.click(function() {
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
                    var dateEnd = dateStart;
                    dateEnd.setHours(dirtyTimeEnd[0]);
                    dateEnd.setMinutes(dirtyTimeEnd[1]);
                    timestampEnd = Math.round(dateEnd.getTime() / 1000);
                }

                Events.fire('add_report', ourPublicId, publicId, timestampStart, timestampEnd, function() {
                    t.update();
                });
            });
        }
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

        Events.fire('get_result_list', t.limit, t.offset, function(data) {
            $listAddMonitor.slideUp(200);
            $listHeader.html(tmpl(REPORTS.RESULT.LIST_HEADER));
            $results.html(tmpl(REPORTS.RESULT.LIST, {items: data}));
            t.makeFullTime($results.find('.time'));
            t.makeDate($results.find('.date'));
            t.makeDiffTime($results.find('.diff-time'));
            t.bindEvents();
        });
    },

    bindEvents: function() {
        var t = this;
        t.bindDeleteEvent();
    }
});
	