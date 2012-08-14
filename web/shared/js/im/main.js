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

        t.rightColumn.on('selectDialogs', function(id) {
            t.leftColumn.showDialogs(id);
        });
        t.rightColumn.on('selectMessages', function(id) {
            t.leftColumn.showMessages(id);
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

    run: function() {
        var t = this;

        t._super();
        t.dialogs = t.initDialogs();
        t.messages = t.initMessages();
        t.tabs = t.initTabs();
        t.showDialogs();
    },

    initTabs: function() {
        var t = this;
        var tabs = new Tabs({
            el: $(this.el).find('.header'),
            data: {tabs: []}
        });
        tabs.on('select', function(id) {
            if (id == 'messages') {
                t.showMessages(id);
            } else {
                t.showDialogs(id);
            }
        });

        return tabs;
    },
    initDialogs: function() {
        var t = this;
        var dialogs = new Dialogs({
            el: $(this.el).find('.list'),
            data: {list: Data.dialogs},
            run: function() {}
        });
        dialogs.on('select', function(id) {
            t.showMessages(id);
        });

        return dialogs;
    },
    initMessages: function() {
        var t = this;
        var messages = new Messages({
            el: $(this.el).find('.list'),
            data: {list: Data.messages},
            run: function() {}
        });

        return messages;
    },

    showDialogs: function(listId) {
        var t = this;

        t.dialogs.renderTemplate();
        t.tabs.addTab('dialogs', 'Диалоги');
        t.tabs.selectTab('dialogs');
    },
    showMessages: function(dialogId) {
        var t = this;

        t.messages.renderTemplate();
        t.tabs.addTab('messages', 'Сообщения');
        t.tabs.selectTab('messages');
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
        var t = this;
        var list = new List({
            el: $(this.el).find('.list'),
            data: {list: Data.lists}
        });
        list.on('selectDialogs', function(id) {
            t.trigger('selectDialogs');
        });
        list.on('selectMessages', function(id) {
            t.trigger('selectMessages');
        });

        return list;
    }
});

var Dialogs = Widget.extend({
    template: DIALOGS,

    events: {
        'click: .dialog': 'clickDialog'
    },

    clickDialog: function(e) {
        var t = this;
        t.selectDialog($(e.currentTarget).data('id'), true);
    },

    selectDialog: function(id, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', id);
    },

    renderTemplate: function() {
        var t = this;

        t._super();
        $(t.el).find('.date').easydate({
            live: true,
            date_parse: function(date) {
                date = intval(date) * 1000;
                if (!date) return;
                return new Date(date);
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
    }
});

var Messages = Widget.extend({
    template: MESSAGES,

    events: {
        'click: .message': 'clickMessage'
    },

    clickMessage: function(e) {
        var t = this;
        t.selectMessage($(e.currentTarget).data('id'), true);
    },

    selectMessage: function(id, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', id);
    },

    renderTemplate: function() {
        var t = this;

        t._super();
        $(t.el).find('.date').easydate({
            live: true,
            date_parse: function(date) {
                date = intval(date) * 1000;
                if (!date) return;
                return new Date(date);
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
    }
});

var List = Widget.extend({
    template: LIST,

    events: {
        'click: .item > .title': 'selectDialogs',
        'click: .public': 'selectMessages'
    },

    selectDialogs: function(e) {
        var t = this;
        t.trigger('selectDialogs', $(e.currentTarget).data('id'));
    },
    selectMessages: function(e) {
        var t = this;
        t.trigger('selectMessages', $(e.currentTarget).data('id'));
    }
});

var Tabs = Widget.extend({
    template: TABS,
    tabTemplate: TABS_ITEM,

    events: {
        'click: .tab': 'clickTab'
    },

    clickTab: function(e) {
        var t = this;
        t.selectTab($(e.currentTarget).data('id'), true);
    },

    selectTab: function(id, isTrigger) {
        var t = this;

        $(t.el).find('.tab.selected').removeClass('selected');
        t.getTab(id).addClass('selected');

        if (isTrigger) t.trigger('select', id);
    },
    getTab: function(id) {
        var t = this;

        return $(t.el).find('.tab[data-id="' + id + '"]');
    },
    getSelectedTab: function() {
        var t = this;

        return $(t.el).find('.tab.selected');
    },
    addTab: function(id, title) {
        var t = this;

        if (t.getTab(id).length) return;
        $(t.el).find('.tab-bar').append(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    removeTab: function(id) {
        var t = this;

        t.getTab(id).remove();
    }
});
