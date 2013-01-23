/**
 * @class ResultPage
 * @extends Page
 */
ResultPage = Page.extend({
    update: function() {
        var t = this;
        var $listAddMonitor = $('#list-add-monitor');
        var $listHeader = $('#list-header');
        var $results = $('#results');

        if (!t.inited) {
            t.inited = true;
        }

        Control.fire('get_result_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.offset
        }, function(data) {
            try {
                $listAddMonitor.slideUp(200);
                $listHeader.html(tmpl(REPORTS.RESULT.LIST_HEADER));
                $results.html(tmpl(REPORTS.RESULT.LIST, {items: data}));
                t.makeFullTime($results.find('.time'));
                t.makeDate($results.find('.date'));
                t.makeDiffTime($results.find('.diff-time'));
                t.bindEvents();
            } catch(e) {
                new Box({
                    title: 'Ошибка',
                    html: 'Произошла ошибка загрузки результатов :('
                }).show();
                throw e;
            } finally {
                $('#global-loader').fadeOut(200);
            }
        });
    },

    bindEvents: function() {
        var t = this;
        t.bindDeleteEvent();
    }
});
