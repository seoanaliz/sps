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
