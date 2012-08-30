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
        var im = new IM({
            el: '#main'
        });
        $(window).on('scroll', function() {
            im.trigger('scroll');
        });
        $(window).on('resize', function() {
            im.trigger('resize');
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
        t.initLeftColumn();
        t.initRightColumn();
        t.bindEvents();
    },

    bindEvents: function() {
        var t = this;

        t.on('scroll', function() {
            t.leftColumn.trigger('scroll');
        });
        t.rightColumn.on('selectDialogs', function(id, title) {
            t.leftColumn.initDialogs(id, title);
        });
        t.rightColumn.on('selectMessages', function(id, title) {
            t.leftColumn.initMessages(id, title);
        });
    },

    initLeftColumn: function() {
        var t = this;
        t.leftColumn = new LeftColumn({
            el: $(t.el).find('> .left-column')
        });
    },
    initRightColumn: function() {
        var t = this;
        t.rightColumn = new RightColumn({
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
        t.initTabs();
        t.initDialogs(999999, 'Не в списке');
        t.bindEvents();
    },

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);
        var $header = $el.find('.header');

        $el.css('padding-top', $header.outerHeight());
        t.on('scroll', function() {
            $el.css('padding-top', $header.outerHeight());
            if ($(window).scrollTop() > 10) {
                $header.addClass('fixed');
            } else {
                $header.removeClass('fixed');
            }
        });
        t.on('scroll', function() {
            if (t.messages) t.messages.trigger('scroll');
            else if (t.dialogs) t.dialogs.trigger('scroll');
        });
    },

    initTabs: function() {
        var t = this;

        t.tabs = new Tabs({
            el: $(t.el).find('.header'),
            templateData: {tabs: []}
        });
        t.tabs.on('select', function(id, title) {
            if (id.indexOf('messages') == 0) {
                t.initMessages(id.substring(t.tabPrefixMessages.length), title);
            } else {
                t.initDialogs(id.substring(t.tabPrefixDialogs.length), title);
            }
        });
    },
    initDialogs: function(listId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixDialogs;

        t.messages = false;
        t.dialogs = new Dialogs({
            el: $(t.el).find('.list'),
            runData: {
                listId: listId
            }
        });
        t.dialogs.on('select', function(id, title) {
            t.initMessages(id, title);
        });

        if (t.curListId && t.curListId != listId) {
            t.tabs.removeTab(tabPrefix + t.curListId);
        }
        if (!t.tabs.getTab(tabPrefix + listId).length) {
            t.tabs.prependTab(tabPrefix + listId, title);
        }
        t.tabs.selectTab(tabPrefix + listId);
        t.curListId = listId;
    },
    initMessages: function(dialogId, title) {
        var t = this;
        var tabPrefix = t.tabPrefixMessages;

        t.dialogs = false;
        t.messages = new Messages({
            el: $(t.el).find('.list'),
            runData: {
                dialogId: dialogId
            }
        });

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
        t.initList();
    },

    initList: function() {
        var t = this;
        t.list = new List({
            el: $(t.el).find('.list')
        });
        t.list.on('selectDialogs', function(id, title) {
            t.trigger('selectDialogs', id, title);
        });
        t.list.on('selectMessages', function(id, title) {
            t.trigger('selectMessages', id, title);
        });
    }
});

var Dialogs = Widget.extend({
    template: DIALOGS,
    listId: null,
    itemsLimit: 20,
    currentPage: 1,

    events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon.plus': 'clickPlus'
    },

    run: function(params) {
        var t = this;
        var $el = $(t.el);
        var listId = t.listId = params.listId;

        Events.fire('get_dialogs', listId == 999999 ? undefined : listId, 0, t.itemsLimit, function(data) {
            t.templateData = {id: listId, list: data};
            t.listId = listId;
            t.renderTemplate();
            t.bindEvents();
            t.scrollTop();
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

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);

        t.on('scroll', function() {
            if ($(window).scrollTop() < 100) {
                t.showMore();
            }
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
                        Events.fire('add_to_list', dialogId, item.id, function() {});
                    },
                    onunselect: function(item) {
                        Events.fire('remove_from_list', dialogId, item.id, function() {});
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
    },

    scrollTop: function() {
        $(window).scrollTop(0);
    },

    showMore: function() {
        var t = this;
        var $el = $(t.el);

        Events.fire('get_dialogs', t.listId, (t.currentPage * t.itemsLimit), t.itemsLimit, function(data) {
            console.log(data);
        });
        t.currentPage++;
    }
});

var Messages = Widget.extend({
    template: MESSAGES,
    dialogId: null,
    itemsLimit: 20,
    currentPage: 1,
    tmplMessage: MESSAGES_ITEM,

    events: {
        'hover: .message.new': 'hoverMessage'
    },

    run: function(params) {
        var t = this;
        var dialogId = t.dialogId = params.dialogId;

        Events.fire('get_messages', dialogId, 0, t.itemsLimit, function(data) {
            var users = data.users;
            var messages = data.messages;
            var user = {};
            $.each(users, function(i, obj) {
                if (obj.id != Configs.vkId) {
                    user = obj;
                    return false;
                }
            });
            t.templateData = {
                id: dialogId,
                list: messages,
                viewer: users[Configs.vkId],
                user: user
            };
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
        });
    },

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);

        t.on('scroll', function() {
            t.updateInputBox();

            if ($(window).scrollTop() < 100) {
                t.showMore();
            }
        });
        $el.find('.button.send').click(function() {
            t.sendMessage();
        });
        $el.find('textarea').keydown(function(e) {
            if (!e.shiftKey && e.keyCode == KEY.ENTER) {
                t.sendMessage();
                return false;
            }
        });
    },

    hoverMessage: function(e) {
        if (e.type != 'mouseenter') return;
        var $message = $(e.currentTarget);
        if ($message.hasClass('viewer')) return;
        Events.fire('message_mark_as_read', $message.data('id'), function() {
            $message.removeClass('new');
        });
    },

    showMore: function() {
        var t = this;
        var $el = $(t.el);
        var $messages = $el.find('.messages');

        Events.fire('get_messages', t.dialogId, (t.currentPage * t.itemsLimit), t.itemsLimit, function(data) {
            console.log(data);
        });
        t.currentPage++;
    },

    updateInputBox: function() {
        var t = this;
        var $el = $(t.el);
        var $inputBox = $el.find('.post-message');

        if ($(window).scrollTop() + $(window).height() < $(document).height() - 10) {
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
                var $newMessage = $(tmpl(t.tmplMessage, data));
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
                $textarea.focus();
            });
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
