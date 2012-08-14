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
        this._super();

        var t = this;
        t.leftColumn = t.initLeftColumn();
        t.rightColumn = t.initRightColumn();

        t.rightColumn.on('selectDialogs', function(id, title) {
            t.leftColumn.showDialogs(id, title);
        });
        t.rightColumn.on('selectMessages', function(id, title) {
            t.leftColumn.showMessages(id, title);
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
    tabPrefixDialogs: 'dialogs',
    tabPrefixMessages: 'messages',
    curListId: null,
    curDialogId: null,

    run: function() {
        this._super();

        var t = this;
        t.messages = t.initMessages();
        t.dialogs = t.initDialogs();
        t.tabs = t.initTabs();
    },

    initTabs: function() {
        var t = this;
        var tabs = new Tabs({
            el: $(this.el).find('.header'),
            data: {tabs: []}
        });
        tabs.on('select', function(id, title) {
            if (id.indexOf('messages') == 0) {
                t.showMessages(id.substring(t.tabPrefixMessages.length), title);
            } else {
                t.showDialogs(id.substring(t.tabPrefixDialogs.length), title);
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
        dialogs.on('select', function(id, title) {
            t.showMessages(id, title);
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

    showDialogs: function(listId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixDialogs;

        t.dialogs.renderTemplate(listId);
        if (t.curListId && t.curListId != listId) {
            t.tabs.removeTab(tabPrefix + t.curListId);
        }
        if (!t.tabs.getTab(tabPrefix + listId).length) {
            t.tabs.prependTab(tabPrefix + listId, title);
        }
        t.tabs.selectTab(tabPrefix + listId);
        t.curListId = listId;
    },
    showMessages: function(dialogId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixMessages;

        t.messages.renderTemplate(dialogId);
        if (t.curDialogId && t.curDialogId != dialogId) {
            t.tabs.removeTab(tabPrefix + t.curDialogId);
        }
        if (!t.tabs.getTab(tabPrefix + dialogId).length) {
            t.tabs.appendTab(tabPrefix + dialogId, title);
        }
        t.tabs.selectTab(tabPrefix + dialogId);
        t.curDialogId = dialogId;
    }
});

var RightColumn = Widget.extend({
    template: RIGHT_COLUMN,
    list: null,

    run: function() {
        this._super();

        var t = this;
        t.list = t.initList();
    },

    initList: function() {
        var t = this;
        var list = new List({
            el: $(this.el).find('.list'),
            data: {list: Data.lists}
        });
        list.on('selectDialogs', function(id, title) {
            t.trigger('selectDialogs', id, title);
        });
        list.on('selectMessages', function(id, title) {
            t.trigger('selectMessages', id, title);
        });

        return list;
    }
});

var Dialogs = Widget.extend({
    template: DIALOGS,
    listId: null,

    events: {
        'click: .dialog': 'clickDialog'
    },

    renderTemplate: function(listId) {
        this._super();
        var t = this;
        t.listId = listId;

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
    },

    clickDialog: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var listId = $target.data('id');
        var title = $target.data('title');
        t.selectDialog(listId, title, true);
    },

    selectDialog: function(id, title, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', id, title);
    }
});

var Messages = Widget.extend({
    template: MESSAGES,
    templateMessage: MESSAGES_ITEM,
    dialogId: null,

    renderTemplate: function(dialogId) {
        this._super();
        var t = this;
        var $el = $(t.el);
        var $textarea = $el.find('textarea');
        t.dialogId = dialogId;

        $el.find('.date').easydate({
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
        $textarea.placeholder();
        $textarea.autoResize();
        $textarea.inputMemory('message' + dialogId);
        $textarea.focus();
        t.bindEvents();
    },

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);
        var $textarea = $el.find('textarea');

        $textarea.keydown(function(e) {
            if (!e.shiftKey && e.keyCode == KEY.ENTER) {
                t.sendMessage();
                return false;
            }
        });
        $el.find('.button.send').click(function() {
            t.sendMessage();
        });
    },

    sendMessage: function() {
        var t = this;
        var $el = $(t.el);
        var $textarea = $el.find('textarea');
        var text = $.trim($textarea.val());

        if (text) {
            $textarea.val('');
            //$el.find('.messages').append(tmpl(t.templateMessage, {}));
            $el.find('.messages').append('<div class="message">' + text + '</div>');
        } else {
            $textarea.focus();
        }
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
        var $target = $(e.currentTarget).closest('.item');
        var listId = $target.data('id');
        var title = $target.data('title');
        t.trigger('selectDialogs', listId, title);
    },
    selectMessages: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var dialogId = $target.data('id');
        var title = $target.data('title');
        t.trigger('selectMessages', dialogId, title);
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
        var $target = $(e.currentTarget);
        var tabId = $target.data('id');
        t.selectTab(tabId, true);
    },

    selectTab: function(id, isTrigger) {
        var t = this;
        var $tab = t.getTab(id);

        $(t.el).find('.tab.selected').removeClass('selected');
        $tab.addClass('selected');

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
    appendTab: function(id, title) {
        var t = this;

        if (t.getTab(id).length) return;
        $(t.el).find('.tab-bar').append(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    prependTab: function(id, title) {
        var t = this;

        if (t.getTab(id).length) return;
        $(t.el).find('.tab-bar').prepend(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    removeTab: function(id) {
        var t = this;

        t.getTab(id).remove();
    }
});
