var uriExp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
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
            im.trigger('scroll');
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
            return;
            console.log('poll...');
            $.ajax({
                url: 'http://im.openapi.lc/int/controls/watchDog/',
                data: {
                    userId: Configs.vkId
                },
                dataType: 'jsonp',
                //timeout: 30000,
                complete: poll,
                success: function(data) {
                    console.log(data);
                    var res = data.response;
                    if (!res || !res.length) return;
                    $.each(res, function(i, event) {
                        t.newEvent(event);
                    });
                }
            });
        })();

        t.on('scroll', function() {
            t.leftColumn.trigger('scroll');
        });
        t.leftColumn.on('addList', function() {
            t.rightColumn.run();
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
                var message = event.content;
                t.leftColumn.addMessage(message);
                t.rightColumn.addMessage(message);
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
    },

    addMessage: function(message) {
        var t = this;

        if (t.messages) {
            t.messages.addMessage(message);
        } else if (t.dialogs) {
            t.dialogs.addMessage();
        }
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
        t.dialogs.on('addList', function() {
            t.trigger('addList');
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

    addMessage: function(message) {
        var t = this;
        t.list.incrementCounter(message.groups, message.dialog_id);
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
    tmplDialog: DIALOGS_ITEM,
    tmplDialogsBlock: DIALOGS_BLOCK,
    listId: null,
    itemsLimit: 20,
    currentPage: 1,

    preloadData: {},
    isBlock: false,

    events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon': 'clickPlus'
    },

    run: function(params) {
        var t = this;
        var $el = $(t.el);
        var listId = t.listId = params.listId;

        t.templateData = {id: listId, list: [], isLoad: true};
        t.renderTemplate();
        t.getBlockData(0, function(data) {
            t.templateData = {id: listId, list: data};
            t.listId = listId;
            t.renderTemplate();
            t.bindEvents();
            t.scrollTop();
            t.makeDialogs($el);
            t.preload();
        });
    },

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);

        if ($(window).scrollTop() >= $(document).height() - $(window).height() - 1000) {
            t.showMore();
        }
        t.on('scroll', function() {
            if ($(window).scrollTop() >= $(document).height() - $(window).height() - 1000) {
                t.showMore();
            }
        });
    },

    addMessage: function() {
        var t = this;
        var $el = $(t.el);
        var listId = t.listId;

        t.getBlockData(0, function(data) {
            t.templateData = {id: listId, list: data};
            t.listId = listId;
            t.renderTemplate();
            t.bindEvents();
            t.scrollTop();
            t.makeDialogs($el);
            t.preload();
        });
    },

    clickPlus: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $dialog = $target.closest('.dialog');
        var dialogId = $dialog.data('id');
        if (!$target.data('dropdown')) {
            (function updateDropdown() {
                function onCreate() {
                    $.each(t.templateData.list, function(i, dialog) {
                        if (dialog.id == dialogId) {
                            $.each(dialog.lists, function(i, listId) {
                                $target.dropdown('getItem', listId).addClass('active');
                            });
                            return false;
                        }
                    });
                }

                Events.fire('get_lists', function(lists) {
                    $target.dropdown({
                        isShow: true,
                        position: 'right',
                        width: 'auto',
                        type: 'checkbox',
                        addClass: 'ui-dropdown-add-to-list',
                        oncreate: onCreate,
                        onupdate: onCreate,
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
                            if (item.id == 'add_list') {
                                var $item = $(this).dropdown('getItem', 'add_list');
                                var $menu = $(this).dropdown('getMenu');
                                var $input = $menu.find('input');
                                $item.removeClass('active');
                                if ($input.length) {
                                    $input.focus();
                                } else {
                                    $item.before('<div class="wrap"><input type="text" placeholder="Название списка..." /></div>');
                                    $input = $menu.find('input');
                                    $input.focus();
                                    $input.keydown(function(e) {
                                        if (e.keyCode == KEY.ENTER) {
                                            Events.fire('add_list', $input.val(), function() {
                                                updateDropdown();
                                                t.trigger('addList');
                                            });
                                        }
                                    });
                                    $(this).dropdown('refreshPosition');
                                }
                            } else {
                                Events.fire('add_to_list', dialogId, item.id, function() {});
                            }
                        },
                        onunselect: function(item) {
                            Events.fire('remove_from_list', dialogId, item.id, function() {});
                        },
                        data: $.merge(lists, [
                            {id: 'add_list', title: 'Создать список'}
                        ])
                    });
                });
            })();
        }
        return false;
    },

    clickDialog: function(e) {
        if ($(e.target).closest('a').length) return;

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

    createBlock: function(data) {
        var t = this;
        return $(tmpl(t.tmplDialogsBlock, {id: t.currentPage, list: data}));
    },

    createDialog: function(data) {
        var t = this;
        return $(tmpl(t.tmplDialog, data));
    },

    makeDialogs: function($el) {
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
    },

    preload: function() {
        var t = this;
        var page = t.currentPage;
        t.getBlockData(page, function(data) {
            if (!t.preloadData[t.listId]) t.preloadData[t.listId] = {};
            t.preloadData[t.listId][page] = data;
        });
    },

    getBlockData: function(page, callback) {
        var t = this;

        if (t.preloadData[t.listId] && t.preloadData[t.listId][page]) {
            if ($.isFunction(callback)) callback(t.preloadData[t.listId][page]);
        } else {
            Events.fire('get_dialogs', t.listId, (page * t.itemsLimit), t.itemsLimit, function(data) {
                if ($.isFunction(callback)) callback(data);
            });
        }
    },

    showMore: function() {
        var t = this;
        if (t.isLock()) return;
        var $el = $(t.el);
        var $dialogs = $el.find('.dialogs');

        t.lock();
        t.getBlockData(t.currentPage, function(data) {
            var $block = t.createBlock(data);
            $dialogs.append($block);
            t.makeDialogs($block);
            t.preload();
            t.unlock();
        });
        t.currentPage++;
    },

    isLock: function() {
        return this.isBlock;
    },
    lock: function() {
        this.isBlock = true;
    },
    unlock: function() {
        this.isBlock = false;
    }
});

var Messages = Widget.extend({
    template: MESSAGES,
    tmplMessage: MESSAGES_ITEM,
    tmplMessagesBlock: MESSAGES_BLOCK,
    dialogId: null,
    itemsLimit: 20,
    currentPage: 1,

    preloadData: {},
    user: {},
    isBlock: false,

    events: {
        'hover: .message.new': 'hoverMessage'
    },

    run: function(params) {
        var t = this;
        var dialogId = t.dialogId = params.dialogId;

        t.templateData = {
            id: dialogId,
            list: [],
            isLoad: true,
            viewer: Data.users[0],
            user: Data.users[0]
        };
        t.renderTemplate();
        t.getBlockData(0, function(data) {
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

            t.makeMessages($el);
            $textarea.placeholder();
            $textarea.autoResize();
            $textarea.inputMemory('message' + dialogId);
            $textarea.focus();
            $textarea[0].scrollTop = $textarea[0].scrollHeight;
            t.bindEvents();
            t.scrollBottom();
            t.preload();
        });
    },

    bindEvents: function() {
        var t = this;
        var $el = $(t.el);

        if ($(window).scrollTop() < 600) {
            t.showMore();
        }
        t.on('scroll', function() {
            t.updateInputBox();

            if ($(window).scrollTop() < 600) {
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

    addMessage: function(message) {
        var t = this;
        var $el = $(t.el);

        if (message.dialog_id != t.dialogId) return;
        var clearMessage = {
            id: message.mid,
            text: message.body,
            user: t.user,
            isNew: true,
            timestamp: message.date
        };
        var $oldMessage = $el.find('[data-id=' + clearMessage.id + ']');
        if ($oldMessage.length) return;
        var $newMessage = t.createMessage(clearMessage);
        $el.find('.messages').append($newMessage);
        t.makeMessages($newMessage);
        t.scrollBottom();
    },

    hoverMessage: function(e) {
        if (e.type != 'mouseenter') return;
        var $message = $(e.currentTarget);
        if ($message.hasClass('viewer')) return;
        Events.fire('message_mark_as_read', $message.data('id'), function() {
            $message.removeClass('new');
        });
    },

    preload: function() {
        var t = this;
        var page = t.currentPage;
        t.getBlockData(page, function(data) {
            if (!t.preloadData[t.dialogId]) t.preloadData[t.dialogId] = {};
            t.preloadData[t.dialogId][page] = data;
        });
    },

    getBlockData: function(page, callback) {
        var t = this;

        if (t.preloadData[t.dialogId] && t.preloadData[t.dialogId][page]) {
            if ($.isFunction(callback)) callback(t.preloadData[t.dialogId][page]);
        } else {
            Events.fire('get_messages', t.dialogId, (page * t.itemsLimit), t.itemsLimit, function(data) {
                if ($.isFunction(callback)) callback(data);
            });
        }
    },

    showMore: function() {
        var t = this;
        if (t.isLock()) return;
        var $el = $(t.el);
        var $messages = $el.find('.messages');

        t.lock();
        t.getBlockData(t.currentPage, function(data) {
            var blockId = t.currentPage;
            var $block = t.createBlock(data);
            $messages.prepend($block);
            t.makeMessages($block);
            t.preload();
            t.unlock();
            $(window).scrollTop($(window).scrollTop() + $block.outerHeight(true));
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

    createBlock: function(data) {
        var t = this;
        return $(tmpl(t.tmplMessagesBlock, {id: t.currentPage, list: data.messages}));
    },

    createMessage: function(data) {
        var t = this;
        return $(tmpl(t.tmplMessage, data));
    },

    makeMessages: function($el) {
        $el.find('.videos').imageComposition({width: 500, height: 240});
        $el.find('.photos').imageComposition({width: 500, height: 300});
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
                var $newMessage = t.createMessage(data);
                $el.find('.messages').append($newMessage);
                t.makeMessages($newMessage);
                if (isScroll) {
                    t.scrollBottom();
                }
                $textarea.focus();
            });
        } else {
            $textarea.focus();
        }
    },

    isLock: function() {
        return this.isBlock;
    },
    lock: function() {
        this.isBlock = true;
    },
    unlock: function() {
        this.isBlock = false;
    }
});

var List = Widget.extend({
    template: LIST,
    dialogsLimit: 100,

    events: {
        'click: .item > .title > .icon': 'clickIcon',
        'click: .item > .title': 'selectDialogs',
        'click: .dialog': 'selectMessages'
    },

    run: function() {
        var t = this;

        Events.fire('get_lists', function(data) {
            t.templateData = {list: data};
            t.renderTemplate();
        });
    },

    clickIcon: function(e) {
        var t = this;
        var $target = $(e.currentTarget).closest('.item');
        var listId = $target.data('id');
        if ($target.data('dialogs')) {
            t.showDialogs($target);
        } else {
            Events.fire('get_dialogs_list', listId, 0, t.dialogsLimit, function(data) {
                $target.data('dialogs', data);
                t.showDialogs($target);
            });
        }
        return false;
    },

    selectDialogs: function(e) {
        var t = this;
        var $target = $(e.currentTarget).closest('.item');
        var title = $target.data('title');
        var listId = $target.data('id');
        $(t.el).find('.title.active, .dialog.active').removeClass('active');
        $target.find('.title').addClass('active');
        t.trigger('selectDialogs', listId, title);
    },

    selectMessages: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var dialogId = $target.data('id');
        var title = $target.data('title');
        $(t.el).find('.title.active, .dialog.active').removeClass('active');
        $target.closest('.dialog').addClass('active');
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
    },

    incrementCounter: function(listIds, dialogId) {
        var t = this;
        var $el = $(t.el);

        $.each(listIds, function(i, listId) {
            var $item = $el.find('.item[data-id=' + listId + ']');
            increment($item.find('> .title'));
        });

        var $dialog = $el.find('.dialog[data-id=' + dialogId + ']');
        increment($dialog.find('> .title'));

        function increment($item) {
            if ($item.length) {
                var $counter = $item.find('.counter');
                if (!$counter.data('counter')) {
                    $counter.counter({prefix: '+'});
                    $item.click(function() {
                        $counter.counter('setCounter', 0);
                    });
                }
                $counter.counter('increment');
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
