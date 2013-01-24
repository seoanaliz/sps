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
            t.bindEvents();
        }

        t.pageLoaded = 0;
        t.isEnded = false;

        Control.fire('get_result_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.limit * t.pageLoaded
        }, function(data) {
            try {
                $listAddMonitor.slideUp(200);
                $listHeader.html(tmpl(REPORTS.RESULT.LIST_HEADER));
                $results.html(tmpl(REPORTS.RESULT.LIST, {items: data}));
                t.makeFullTime($results.find('.time'));
                t.makeDate($results.find('.date'));
                t.makeDiffTime($results.find('.diff-time'));
                $(window).scroll();
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
    },

    showMore: function() {
        var t = this;
        var $results = $('#results');

        if (t.loaded) {
            return;
        } else {
            t.loaded = true;
        }

        if (t.isEnded) {
            return;
        }

        t.pageLoaded++;

        Control.fire('get_result_list', {
            groupId: t.groupId,
            limit: t.limit,
            offset: t.limit * t.pageLoaded
        }).success(function(data) {
            t.loaded = false;
            $('#load-more-table').remove();

            if (!data.length) {
                t.isEnded = true;
            } else {
                var $tmpElement = $(document.createElement('div'));
                $tmpElement.html(tmpl(REPORTS.RESULT.LIST, {items: data}));
                t.makeFullTime($tmpElement.find('.time'));
                t.makeDate($tmpElement.find('.date'));
                t.makeDiffTime($tmpElement.find('.diff-time'));
                $results.append($tmpElement.html());
                $tmpElement.remove();
            }
        });
    }
});
