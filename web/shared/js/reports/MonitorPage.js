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
        var $filter = $('#filter');

        if (!t.inited) {
            t.inited = true;
            $listAddMonitor.html(tmpl(REPORTS.MONITOR.LIST_ADD_MONITOR));
            $('#time-start').mask('29:59');
            $('#time-end').mask('29:59');
            $('#datepicker').datepicker().datepicker('setDate', new Date().getTime());
            t.bindEvents();
        }

        t.pageLoaded = 0;
        t.isEnded = false;

        Control.fire('get_monitor_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.limit * t.pageLoaded,
            filter: t.filter
        }, function(data) {
            try {
                $listAddMonitor.slideDown(200);
                $filter.slideUp(200);
                $listHeader.html(tmpl(REPORTS.MONITOR.LIST_HEADER));
                $results.html(tmpl(REPORTS.MONITOR.LIST, {items: data}));
                t.makeTime($results.find('.time'));
                t.makeDate($results.find('.date'));
                if (data.length < t.limit) {
                    t.isEnded = true;
                }
                $(window).scroll();
                $('#load-more-table').remove();
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
        t.bindDeleteReportEvent();
        t.bindAddReportEvent();
    },

    bindDeleteReportEvent: function() {
        var t = this;
        var $results = $('#results');

        $results.delegate('.icon.delete', 'click', function() {
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
                Control.fire('delete_report', {
                    reportId: $row.data('report-id'),
                    groupId: t.groupId
                }, function() {
                    $row.slideUp(200);
                });
            }
        });
    },

    bindAddReportEvent: function() {
        var t = this;
        var $addReport = $('#addReport');

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
    },

    showMore: function() {
        var t = this;
        var $results = $('#results');

        if (t.isEnded) {
            return;
        }

        if (t.loaded) {
            return;
        } else {
            t.loaded = true;
        }

        t.pageLoaded++;

        Control.fire('get_monitor_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.limit * t.pageLoaded,
            filter: t.filter
        }).success(function(data) {
            t.loaded = false;
            $('#load-more-table').remove();

            if (data.length < t.limit) {
                t.isEnded = true;
            } else {
                var $tmpElement = $(document.createElement('div'));
                $tmpElement.html(tmpl(REPORTS.MONITOR.LIST, {items: data}));
                t.makeTime($tmpElement.find('.time'));
                t.makeDate($tmpElement.find('.date'));
                $results.append($tmpElement.html());
                $tmpElement.remove();
            }
        });
    }
});
