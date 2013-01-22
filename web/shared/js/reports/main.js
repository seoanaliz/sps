Configs = {
    limit: 150,
    controlsRoot: controlsRoot,
    eventsDelay: 0,
    eventsIsDebug: true
};

/**
 * @class GroupModel
 * @extends Model
 */
GroupModel = Model.extend({
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    name: function(name) {
        if (arguments.length) name += '';
        return this.data('name', name);
    },
    place: function(place) {
        if (arguments.length) intval(place);
        return this.data('place', place);
    },
    type: function(type) {
        if (arguments.length) intval(type);
        return this.data('type', type);
    }
});

/**
 * @class GroupCollection
 * @extends Collection
 */
GroupCollection = Collection.extend({
    modelClass: GroupModel
});

/**
 * @class GroupListModel
 * @extends Model
 */
GroupListModel = Model.extend({
    _groupCollectionClass: GroupCollection,

    defaultLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('defaultLists', setValue);
    },

    userLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('userLists', setValue);
    },

    sharedLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('sharedLists', setValue);
    }
});

/**
 * @class GroupListWidget
 * @extends Widget
 */
GroupListWidget = Widget.extend({
    _template: REPORTS.GROUP_LIST,
    _modelClass: GroupListModel,
    _events: {
        'click: .item': 'clickItem',
        'keydown: input': 'keydownInput'
    },

    _groupId: null,

    run: function() {
        var t = this;
        Control.fire('get_group_list', {}, function(data) {
            $.each(data.default_list, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                defaultGroupCollection.add(groupModel.id(), groupModel);
            });
            $.each(data.shared_lists, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                sharedGroupCollection.add(groupModel.id(), groupModel);
            });
            $.each(data.user_lists, function(i, group) {
                var groupModel = new GroupModel({
                    id: group.group_id,
                    name: group.name,
                    place: group.place,
                    type: group.type
                });
                userGroupCollection.add(groupModel.id(), groupModel);
            });
            groupListModel.defaultLists(defaultGroupCollection);
            groupListModel.sharedLists(sharedGroupCollection);
            groupListModel.userLists(userGroupCollection);
            t.render();

            if (!t._groupId) {
                t.el().find('.item[data-id]:first').addClass('selected');
            } else {
                t.el().find('.item[data-id=' + t._groupId + ']').addClass('selected');
            }
            t._groupId = t.el().find('.item.selected').data('id');
        });
    },

    clickItem: function(e) {
        var t = this;
        var $target = $(e.target);
        var $list = $target.closest('.list');
        var $input = $list.find('input');

        var groupId = $target.data('id');
        if (groupId) {
            $input.hide();
            t.el().find('.item').removeClass('selected');
            $target.addClass('selected');
            t.trigger('change', groupId);
            t._groupId = groupId;
        } else {
            $input.show();
            $input.focus();
        }
    },

    keydownInput: function(e) {
        var t = this;
        var $input = $(e.currentTarget);
        if (e.keyCode == KEY.ENTER) {
            Control.fire('add_group', {name: $input.val()}, function() {
                t.run();
            });
        }
    }
});

/**
 * @class Pages
 * @singleton
 */
Pages = Class.extend({
    monitor: null,
    result: null,
    currentPage: null,
    groupListWidget: null,

    init: function() {
        var t = this;
        $('#main').html(tmpl(REPORTS.MAIN));
        var $header = $('#header');
        $header.html(tmpl(REPORTS.HEADER));
        t.monitor = new MonitorPage();
        t.result = new ResultPage();

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

        t.showMonitors();
        t.showRightColumn();
    },

    showResults: function() {
        var t = this;
        t.currentPage = t.result;
        t.currentPage.update();
    },

    showMonitors: function() {
        var t = this;
        t.currentPage = t.monitor;
        t.currentPage.update();
    },

    showRightColumn: function() {
        var t = this;

        if (!t.groupListWidget) {
            t.groupListWidget = new GroupListWidget({
                model: groupListModel,
                selector: '#group-list'
            });
            t.groupListWidget.on('change', function(groupId) {
                t.currentPage.groupId = groupId;
                t.currentPage.update();
            });
        } else {
            t.groupListWidget.render();
        }
    }
});

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

                Control.fire('add_report', {
                    ourPublicId: ourPublicId,
                    publicId: publicId,
                    timestampStart: timestampStart,
                    timestampEnd: timestampEnd
                }, function() {
                    t.update();
                });
            });
        }
    }
});

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

defaultGroupCollection = new GroupCollection();
sharedGroupCollection = new GroupCollection();
userGroupCollection = new GroupCollection();
groupListModel = new GroupListModel();

$(document).ready(function() {
    new Pages();
});
