/**
 * @class MonitorPage
 * @extends Page
 */
MonitorPage = Page.extend({
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
        }

        Control.fire('get_monitor_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.offset
        }, function(data) {
            try {
                $listAddMonitor.slideDown(200);
                $listHeader.html(tmpl(REPORTS.MONITOR.LIST_HEADER));
                $results.html(tmpl(REPORTS.MONITOR.LIST, {items: data}));
                t.makeTime($results.find('.time'));
                t.makeDate($results.find('.date'));
                t.bindEvents();
            } catch(e) {
                new Box({
                    title: 'Ошибка',
                    html: 'Произошла ошибка загрузки мониторов :('
                }).show();
                throw e;
            } finally {
                $('#global-loader').fadeOut(200);
            }
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
                var timestampStop = null;

                if ($('#time-end').val()) {
                    var dirtyTimeEnd = ($('#time-end').val() || '__:__').split('_').join('0').split(':');
                    var dateEnd = dateStart;
                    dateEnd.setHours(dirtyTimeEnd[0]);
                    dateEnd.setMinutes(dirtyTimeEnd[1]);
                    timestampStop = Math.round(dateEnd.getTime() / 1000);
                }

                Control.fire('add_report', {
                    ourPublicId: ourPublicId,
                    publicId: publicId,
                    timestampStart: timestampStart,
                    timestampStop: timestampStop,
                    groupId: t.groupId
                }, function() {
                    t.update();
                });
            });
        }
    }
});
