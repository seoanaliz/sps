var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    viewer: {}
};

$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    (function() {
        return;
        var code = 'return {' +
            'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0],' +
            'longPoll: API.messages.getLongPollServer()' +
        '};';

        $.ajax({
            url: 'https://api.vk.com/method/execute',
            dataType: 'jsonp',
            data: {
                access_token: Configs.token,
                code: code
            },
            success: function(data) {
                console.log(data);
                initVK(data.response);
            }
        });

        function initVK(data) {
            (function poll(ts) {
                console.log('poll...');
                var longPoll = data.longPoll;
                var ts = ts || longPoll.ts;
                var url = longPoll.server;
                $.ajax({
                    url: 'http://' + url,
                    dataType: 'json',
                    data: {
                        ts: ts,
                        key: longPoll.key,
                        act: 'a_check',
                        wait: 25,
                        mode: 2
                    },
                    success: function(data) {}
                });
            })();
        }
    })();

    if (!Configs.vkId) {
        return location.replace('/login/');
    }
    if (!Configs.token) {
        return location.replace('/im/login/');
    }

    Events.fire('get_user', Configs.vkId, Configs.token, function(viewer) {
        Configs.viewer = viewer;
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

        (function poll() {
            return;
            console.log('poll...');
            setTimeout(function() {
                $.ajax({
                    url: Configs.controlsRoot + 'watchDog/',
                    dataType: 'json',
                    data: {
                        userId: Configs.vkId
                    },
                    success: function(data) {
                        $.each(data.response, function(i, event) {
                            t.newEvent(event);
                        });
                    }
                });
                poll();
            }, 5000);
        })();
        (function poll() {
            return false;
            console.log('poll...');
            $.ajax({
                url: Configs.controlsRoot + 'watchDog/',
                data: {
                    userId: Configs.vkId
                },
                dataType: 'json',
                complete: poll,
                timeout: 30000,
                success: function(data) {
                    var res = data.response[0];
                    if (!res || !res.type) return;
                    switch (res.type) {
                        case 'inMessage':
                            console.log(['new message: ', res.content, res.content.body]);
                        break;
                        case 'online':
                            console.log(['online: ', res.content]);
                        break;
                        case 'offline':
                            console.log(['online: ', res.content]);
                        break;
                    }
                }
            });
        })();

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
    },

    newEvent: function(event) {
        var t = this;

        switch (event.type) {
            case 'inMessage':
                t.leftColumn.trigger('addMessage', event.content);
            break;
        }
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
        t.on('addMessage', function(message) {
            console.log('addMessage: ');
            console.log(message);
            if (t.messages) {
                t.messages.trigger('addMessage', message);
            } else if (t.dialogs) {
                t.dialogs.trigger('addMessage', message);
            }
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
    tmplDialog: DIALOGS_ITEM,

    isBlock: false,

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
            if ($(window).scrollTop() >= $(document).height() - $(window).height() - 1000) {
                t.showMore();
            }
        });
        t.on('addMessage', function() {
            var t = this;
            var listId = t.listId;

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
        if (t.isBlock) return;
        var $el = $(t.el);
        var $dialogs = $el.find('.dialogs');

        t.isBlock = true;
        Events.fire('get_dialogs', t.listId == 999999 ? undefined : t.listId, (t.currentPage * t.itemsLimit), t.itemsLimit, function(data) {
            var blockId = 'dialogsBlock' + t.currentPage;
            var html = '<div id="' + blockId + '">';
            $.each(data, function(i, data) {
                html += (tmpl(t.tmplDialog, data));
            });
            html += '</div>';
            $dialogs.append(html);
            $('#' + blockId).find('.dialog').first().remove();
            $('#' + blockId).find('.date').easydate({
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
            t.isBlock = false;
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

    user: {},
    isBlock: false,

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
                viewer: Configs.viewer,
                user: user
            };
            t.user = user;
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
            $el.find('.attachments').imageComposition();
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

            if ($(window).scrollTop() < 600) {
                t.showMore();
            }
        });
        t.on('addMessage', function(message) {
            var t = this;

            if (message.dialog_id != t.dialogId) return;
            var data = {
                id: message.mid,
                text: message.body,
                user: t.user,
                isNew: true,
                timestamp: message.date
            };
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
            t.scrollBottom();
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
        if (t.isBlock) return;
        var $el = $(t.el);
        var $messages = $el.find('.messages');
        //@todo: изменить этот стыд

        t.isBlock = true;
        Events.fire('get_messages', t.dialogId, (t.currentPage * t.itemsLimit), t.itemsLimit, function(data) {
            var blockId = 'messagesBlock' + t.currentPage;
            var html = '<div id="' + blockId + '">';
            $.each(data.messages, function(i, data) {
                html += (tmpl(t.tmplMessage, data));
            });
            html += '</div>';
            $messages.prepend(html);
            $('#' + blockId).find('.date').easydate({
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
            $(window).scrollTop($(window).scrollTop() + $('#' + blockId).outerHeight(true));
            t.isBlock = false;
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
        if ($target.data('dialogs')) {
            t.showDialogs($target);
        } else {
            Events.fire('get_dialogs', listId, 0, 20, function(data) {
                $target.data('dialogs', data);
                t.showDialogs($target);
            });
        }
        //t.trigger('selectDialogs', listId, title);
    },
    selectMessages: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var dialogId = $target.data('id');
        var title = $target.data('title');
        t.trigger('selectMessages', dialogId, title);
    },

    showDialogs: function($el) {
        var dialogs = $el.data('dialogs');
        if (dialogs.length) {
            var list = $el.find('> .list');
            if (list.length) {
                list.slideUp(100, function() {
                    $(this).remove();
                });
            } else {
                var html = '<div class="list">';
                $.each(dialogs, function(i, dialog) {
                    html += tmpl(LIST_ITEM_DIALOG, dialog);
                });
                html += '</div>';
                var $dialogs = $(html);
                $el.append($dialogs);
                var cssHeight = $dialogs.css('height');
                var height = $dialogs.height();
                $dialogs.css({height: 0, opacity: 0}).animate({height: height, opacity: 1}, 100, function() {
                    $(this).css({height: cssHeight})
                });
            }
        }
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
