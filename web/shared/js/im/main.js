var Configs = {
    vkId: 4718705,
    appId: vk_appId,
    controlsRoot: controlsRoot
};

var cur = {
    dataUser: {}
};

$(document).ready(function() {
    new IM({
        el: '#main'
    });
});

/* Instant Messenger */
var IM = Widget.extend({
    template: MAIN,
    leftColumn: null,
    rightColumn: null,

    run: function() {
        var t = this;

        t._super();
        t.leftColumn = t.initLeftColumn();
        t.rightColumn = t.initRightColumn();

        t.initEvents();
    },

    initEvents: function() {
        var t = this;

        t.rightColumn.on('selectList', function() {
            t.leftColumn.showDialogs();
        });
        t.rightColumn.on('selectDialog', function(a) {
            t.leftColumn.showMessages();
        });
    },
    initLeftColumn: function() {
        return new LeftColumn({
            el: '.left-column'
        });
    },
    initRightColumn: function() {
        return new RightColumn({
            el: '.right-column'
        });
    }
});

var LeftColumn = Widget.extend({
    template: LEFT_COLUMN,
    dialogs: null,
    messages: null,
    tabs: null,

    events: {
        'click: .list .dialog': 'showMessages',
        'click: .list .message': 'showDialogs'
    },

    run: function() {
        var t = this;

        t._super();
        t.tabs = t.initTabs();
        t.dialogs = t.initDialogs();
        t.messages = t.initMessages();
        t.showDialogs();
    },

    initTabs: function() {
        return new Tabs({
            el: $(this.el).find('.header'),
            data: {tabs: [{id: 1, title: 'Диалоги', isSelected: true}]}
        });
    },
    initDialogs: function() {
        return new Dialogs({
            el: $(this.el).find('.list'),
            data: {list: Data.dialogs},
            run: function() {}
        });
    },
    initMessages: function() {
        return new Messages({
            el: $(this.el).find('.list'),
            data: {list: Data.dialogs},
            run: function() {}
        });
    },

    showDialogs: function(listId) {
        var t = this;

        t.dialogs.renderTemplate();
    },
    showMessages: function(dialogId) {
        var t = this;

        t.messages.renderTemplate();
    },

    addTab: function(id, title) {
        var t = this;
    },
    removeTab: function(id) {
        var t = this;
    },
    selectTab: function(id) {
        var t = this;
    }
});

var RightColumn = Widget.extend({
    template: RIGHT_COLUMN,
    list: null,

    run: function() {
        var t = this;

        t._super();
        t.list = t.initList();
    },

    initList: function() {
        return new List({
            el: $(this.el).find('.list'),
            data: {list: Data.lists}
        });
    },

    selectList: function(e) {},
    selectDialog: function(e) {}
});

var Dialogs = Widget.extend({
    template: DIALOGS
});

var Messages = Widget.extend({
    template: MESSAGES
});

var List = Widget.extend({
    template: LIST,

    events: {
        'click: .item > .title': 'selectList',
        'click: .public': 'selectDialog'
    },

    selectList: function(e) {
        var t = this;

        t.trigger('selectList');
    },
    selectDialog: function(e) {
        var t = this;

        t.trigger('selectDialog');
    }
});

var Tabs = Widget.extend({
    template: TABS,

    events: {
        'click: .tab': 'selectTab'
    },

    selectTab: function(e) {
        var t = this;

        $(t.el).find('.tab.selected').removeClass('selected');
        $(e.currentTarget).addClass('selected');
    },

    getSelectedTab: function() {
        var t = this;

        return $()
    },

    addTab: function() {

    }
});