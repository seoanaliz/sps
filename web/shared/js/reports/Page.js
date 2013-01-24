/**
 * @class Page
 * @extends Event
 */
Page = Event.extend({
    inited: null,
    sort: '',
    sortReverse: false,
    offset: 0,
    limit: Configs.limit,
    groupId: 0,

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
                Control.fire('delete_report', {
                    reportId: $row.data('report-id'),
                    groupId: t.groupId
                }, function() {
                    $row.slideUp(200);
                });
            }
        });
    },

    showMore: function() {}
});
