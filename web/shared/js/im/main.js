var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot
};

$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    if (!Configs.vkId) {
        return location.replace('/login/');
    }
    if (!Configs.token) {
        return location.replace('/im/login/');
    }

    Events.fire('get_user', Configs.vkId, Configs.token, function(viewer) {
        new IM({
            el: '#main',
            viewer: viewer
        });
    });
});

/* Instant Messenger */
var IM = Widget.extend({
    template: MAIN,
    viewer: null,
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
        var t = this;
        return new LeftColumn({
            el: $(t.el).find('> .left-column')
        });
    },
    initRightColumn: function() {
        var t = this;
        return new RightColumn({
            el: $(t.el).find('> .right-column')
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
    keyListId: 'keyListId',
    keyDialogId: 'keyDialogId',

    run: function() {
        this._super();

        var t = this;
        var tab;
        var tabId;
        var tabTitle;
        t.initTabs();
        t.initDialogs();
        t.initMessages();

        t.showDialogs(999999, 'Не в списке');
//        if (localStorage.getItem(t.keyListId)) {
//            tab = localStorage.getItem(t.keyListId);
//            tabId = tab.split(':')[0];
//            tabTitle = tab.split(':')[1];
//            t.showDialogs(tabId, tabTitle);
//        }
//        if (localStorage.getItem(t.keyDialogId)) {
//            tab = localStorage.getItem(t.keyDialogId);
//            tabId = tab.split(':')[0];
//            tabTitle = tab.split(':')[1];
//            t.showMessages(tabId, tabTitle);
//        }
    },

    initTabs: function() {
        var t = this;
        t.tabs = new Tabs({
            el: $(t.el).find('.header'),
            data: {tabs: []}
        });
        t.tabs.on('select', function(id, title) {
            if (id.indexOf('messages') == 0) {
                t.showMessages(id.substring(t.tabPrefixMessages.length), title);
            } else {
                t.showDialogs(id.substring(t.tabPrefixDialogs.length), title);
            }
        });
    },
    initDialogs: function() {
        var t = this;
        t.dialogs = new Dialogs({
            el: $(t.el).find('.list')
        });
        t.dialogs.on('select', function(id, title) {
            t.showMessages(id, title);
        });
    },
    initMessages: function() {
        var t = this;
        t.messages = new Messages({
            el: $(t.el).find('.list')
        });
    },

    showDialogs: function(listId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixDialogs;

        t.dialogs.update(listId);
        if (t.curListId && t.curListId != listId) {
            t.tabs.removeTab(tabPrefix + t.curListId);
        }
        if (!t.tabs.getTab(tabPrefix + listId).length) {
            t.tabs.prependTab(tabPrefix + listId, title);
            localStorage.setItem(t.keyListId, listId + ':' + title);
        }
        t.tabs.selectTab(tabPrefix + listId);
        $(window).unbind('resize scroll', $.proxy(t.messages.updateInputBox, t));

        t.curListId = listId;
    },
    showMessages: function(dialogId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixMessages;

        t.messages.update(dialogId);
        if (t.curDialogId && t.curDialogId != dialogId) {
            t.tabs.removeTab(tabPrefix + t.curDialogId);
        }
        if (!t.tabs.getTab(tabPrefix + dialogId).length) {
            t.tabs.appendTab(tabPrefix + dialogId, title);
            localStorage.setItem(t.keyDialogId, dialogId + ':' + title);
        }
        t.tabs.selectTab(tabPrefix + dialogId);
        $(window).bind('resize scroll', $.proxy(t.messages.updateInputBox, t));

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
            el: $(t.el).find('.list')
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
        'click: .dialog': 'clickDialog',
        'click: .action.icon.plus': 'clickPlus'
    },

    run: function() {},

    update: function(listId) {
        var t = this;
        var $el = $(t.el);

        Events.fire('get_dialogs', listId == 999999 ? undefined : listId, function(data) {
            t.templateData = {id: listId, list: data};
            t.listId = listId;
            t.renderTemplate();
            $(t.el).find('.date').easydate({
                live: true,
                set_title: false,
                date_parse: function(date) {
                    date = intval(date) * 1000;
                    if (!date) return;
                    return new Date(date);
                },
                uneasy_format: function(date) {
                    return date.toLocaleDateString();
                }
            });
        });
    },

    clickPlus: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $dialog = $target.closest('.dialog');
        var dialogId = $dialog.data('id');
        if (!$target.data('dropdown')) {
            Events.fire('get_lists', function(lists) {
                $target.dropdown({
                    isShow: true,
                    position: 'right',
                    width: 'auto',
                    type: 'checkbox',
                    addClass: 'ui-dropdown-add-to-list',
                    oncreate: function() {
                        var $selectedItem = $(this).dropdown('getItem', t.listId);
                        $selectedItem.addClass('active');
                    },
                    onopen: function() {
                        $target.addClass('active');
                    },
                    onclose: function() {
                        $target.removeClass('active');
                    },
                    onchange: function(item) {
                        $(this).dropdown('open');
                    },
                    onselect: function(item) {
                        Events.fire('add_to_list', dialogId, item.id, function() {
                            //console.log('!!!');
                        });
                    },
                    onunselect: function(item) {
                        Events.fire('remove_from_list', dialogId, item.id, function() {
                            //console.log('!!!');
                        });
                    },
                    data: lists
                });
            });
        }
        return false;
    },

    clickDialog: function(e) {
        if ($(e.target).is('a')) return;

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

    events: {
        'hover: .message.new': 'hoverMessage'
    },

    run: function() {},

    update: function(dialogId) {
        var t = this;

        Events.fire('get_messages', dialogId, function(data) {
            t.templateData = {id: dialogId, list: data, viewer: Data.users[0], user: Data.users[1]};
            t.dialogId = dialogId;
            t.renderTemplate();
            var $el = $(t.el);
            var $textarea = $el.find('textarea');

            $el.find('.date').easydate({
                live: true,
                set_title: false,
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
            $textarea[0].scrollTop = $textarea[0].scrollHeight;
            t.bindEvents();
            t.scrollBottom();
            t.updateInputBox();
        });
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

    updateInputBox: function() {
        var t = this;
        var $el = $(t.el);
        var $inputBox = $el.find('.post-message');

        if ($(window).scrollTop() + $(window).height() < $(document).height() - 14) {
            $inputBox.addClass('fixed');
        } else {
            $inputBox.removeClass('fixed');
        }
    },

    scrollBottom: function() {
        $(window).scrollTop($(document).height());
    },

    sendMessage: function() {
        var t = this;
        var $el = $(t.el);
        var $textarea = $el.find('textarea');
        var text = $.trim($textarea.val());
        var isScroll = true;

        if (text) {
            Events.fire('send_message', t.dialogId, text, function(data) {
                $textarea.val('');
                var $newMessage = $(tmpl(MESSAGES_ITEM, data));
                $el.find('.messages').append($newMessage);
                $newMessage.find('.date').easydate({
                    live: true,
                    set_title: false,
                    date_parse: function(date) {
                        date = intval(date) * 1000;
                        if (!date) return;
                        return new Date(date);
                    },
                    uneasy_format: function(date) {
                        return date.toLocaleDateString();
                    }
                });
                if (isScroll) {
                    t.scrollBottom();
                }
                t.updateInputBox();
                $textarea.focus();
            });
        } else {
            $textarea.focus();
        }
    },

    hoverMessage: function(e) {
        if (e.type != 'mouseenter') return;
        var $message = $(e.currentTarget);
        Events.fire('message_mark_as_read', $message.data('id'), function() {
            $message.removeClass('new');
        });
    }
});

var List = Widget.extend({
    template: LIST,

    events: {
        'click: .item > .title': 'selectDialogs',
        'click: .public': 'selectMessages'
    },

    run: function() {
        var t = this;

        Events.fire('get_lists', function(data) {
            t.templateData = {list: data};
            t.renderTemplate();
        });
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
