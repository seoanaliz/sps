var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    hostName: hostname,
    commonDialogsList: 999999,
    viewer: {},
    disableAutocomplete: false
};

var Cleaner = {
    longPollMessage: function(rawContent, isOut) {
        var user = typeof rawContent.from_id == 'number' ? UsersCollection.get(rawContent.from_id) : this.user(rawContent.from_id);

        return {
            id: rawContent.mid,
            isNew: (rawContent.read_state != 1),
            isViewer: isOut,
            text: rawContent.body,
            attachments: [],
            timestamp: rawContent.date,
            user: user,
            viewer: Configs.viewer,
            lists: (rawContent.groups == '-1') ? [Configs.commonDialogsList] : rawContent.groups,
            dialogId: rawContent.dialog_id
        };
    },

    longPollDialog: function(rawContent, isOut) {
        var user = typeof rawContent.from_id == 'number' ? UsersCollection.get(rawContent.from_id) : this.user(rawContent.from_id);

        return {
            id: rawContent.dialog_id,
            isNew: (rawContent.read_state != 1),
            isViewer: isOut,
            text: makeDlg(rawContent.body || rawContent.title),
            attachments: [],
            timestamp: rawContent.date,
            user: user,
            viewer: Configs.viewer,
            lists: (rawContent.groups == '-1') ? [Configs.commonDialogsList] : rawContent.groups,
            messageId: rawContent.mid
        };
    },

    longPollOnline: function(rawContent) {
        var user = rawContent.userId;

        return {
            isOnline: user.online,
            userId: user.userId
        };
    },

    longPollRead: function(rawContent) {
        return {
            id: rawContent.mid
        }
    },

    user: function(rawUser) {
        var clearUser = {};

        if (rawUser && rawUser.userId) {
            clearUser = {
                id: rawUser.userId,
                name: rawUser.name,
                photo: rawUser.ava,
                isOnline: (rawUser.online != 0)
            };
        } else {
            clearUser = {};
        }
        return clearUser;
    },

    attachments: function(rawAttachments) {
        var clearAttachments = {};

        if (rawAttachments) {
            $.each(rawAttachments, function (i, attachment) {
                if (!clearAttachments[attachment.type]) {
                    clearAttachments[attachment.type] = {
                        type: attachment.type,
                        list: []
                    };
                }
                clearAttachments[attachment.type].list.push(attachment[attachment.type]);
            });
        }
        return clearAttachments;
    },

    message: function(rawMessage) {
        return {
            id: rawMessage.mid,
            isNew: (rawMessage.read_state != 1),
            isViewer: (rawMessage.out != 0),
            viewer: Configs.viewer,
            user: typeof rawMessage.from_id == 'number' ? UsersCollection.get(rawMessage.from_id) : this.user(rawMessage.from_id),
            text: makeMsg(rawMessage.body.split('<br>').join('\n'), true),
            timestamp: rawMessage.date,
            attachments: this.attachments(rawMessage.attachments),
            dialogId: rawMessage.dialog_id
        };
    },

    dialog: function(rawDialog) {
        return {
            id: rawDialog.id,
            isNew: (rawDialog.read_state != 1),
            isViewer: (rawDialog.out != 0),
            viewer: Configs.viewer,
            user: this.user(rawDialog.uid),
            text: makeDlg(rawDialog.body || rawDialog.title),
            timestamp: rawDialog.date,
            attachments: [],
            lists: (typeof rawDialog.groups == 'string') ? [] : rawDialog.groups,
            messageId: rawDialog.mid
        };
    },

    listItem: function(rawList) {
        return {
            id: rawList.group_id,
            title: rawList.name,
            count: rawList.unread,
            isRead: rawList.isRead
        };
    },

    template: function(rawTemplate) {
        return {
            id: rawTemplate.tmpl_id,
            title: rawTemplate.text
        };
    }
};

$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    if (!Configs.vkId) {
        location.replace('/login/?' + btoa('im'));
        return;
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }

    if (!Configs.token) {
        location.replace('/im/login/? ' + btoa('im'));
        return;
    }

    Events.fire('get_user', function(viewer) {
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

var Collection = Class.extend({
    get: function(itemId) {
        var _items = this._items || (this._items = {});
        return itemId ? _items[itemId] : _items;
    },
    add: function(id, item) {
        var _items = this._items || (this._items = {});
        return _items[id] = item;
    }
});
var UsersCollection = new Collection();
var MessagesCollection = new Collection();

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

        (function poll(ts) {
            var timeout = 15;
            $.ajax({
                url: 'http://im.' + Configs.hostName + '/int/controls/watchDog/',
                data: {
                    userId: Configs.vkId,
                    timeout: timeout,
                    ts: ts
                },
                dataType: 'jsonp',
                success: function(data) {
                    poll(data.response.ts);
                    $.each(data.response.events, function(i, event) {
                        t.newEvent(event);
                    });
                }
            });
        })();

        t.on('scroll', function() {
            t.leftColumn.trigger('scroll');
        });
        t.leftColumn.on('updateList', function() {
            t.rightColumn.update();
        });
        t.leftColumn.on('selectDialog', function(dialogId) {
            t.rightColumn.trigger('selectDialog', dialogId);
        });
        t.rightColumn.on('selectDialogs', function(id, title) {
            t.leftColumn.initDialogs(id, title);
        });
    },

    initLeftColumn: function() {
        var t = this;
        t.leftColumn = new LeftColumn({
            el: t.$el.find('> .left-column')
        });
    },
    initRightColumn: function() {
        var t = this;
        t.rightColumn = new RightColumn({
            el: t.$el.find('> .right-column')
        });
    },

    newEvent: function(event) {
        var t = this;
        if (!event || !event.type) return;

        switch (event.type) {
            case 'inMessage':
            case 'outMessage':
                (function() {
                    var isViewer = (event.type == 'outMessage');
                    var message = Cleaner.longPollMessage(event.content, isViewer);
                    var dialog = Cleaner.longPollDialog(event.content, isViewer);
                    t.leftColumn.addMessage(message);
                    t.leftColumn.addDialog(dialog);

                    if (!message.isViewer) {
                        t.rightColumn.addMessage(message);
                    }
                })();
            break;
            case 'read':
                (function() {
                    var message = Cleaner.longPollRead(event.content);
                    t.leftColumn.readMessage(message);
                })();
            break;
            case 'online':
            case 'offline':
                (function() {
                    var online = Cleaner.longPollOnline(event.content);
                    if (online.isOnline) {
                        t.leftColumn.setOnline(online.userId);
                    } else {
                        t.leftColumn.setOffline(online.userId);
                    }
                })();
            break;
        }
    }
});

/* Left Column */
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
        t.initDialogs(Configs.commonDialogsList, 'Не в списке');
        t.bindEvents();
    },

    bindEvents: function() {
        var t = this;
        var $el = t.$el;

        t.on('scroll', function() {
            if (t.messages) {
                t.messages.trigger('scroll');
            }
            if (t.dialogs) {
                t.dialogs.trigger('scroll');
            }
        });
    },

    readMessage: function(message) {
        var t = this;

        if (t.messages) {
            t.messages.readMessage(message);
        }
        if (t.dialogs) {
            t.dialogs.readMessage(message);
        }
    },

    addMessage: function(message) {
        var t = this;

        if (t.messages) {
            t.messages.addMessage(message);
        }
    },

    addDialog: function(dialog) {
        var t = this;

        if (t.dialogs) {
            t.dialogs.addDialog(dialog);
        }
    },

    initTabs: function() {
        var t = this;

        t.tabs = new MessengerTabs({
            el: t.$el.find('.header'),
            templateData: {tabs: []}
        });
        t.tabs.on('select', function(tab) {
            var id = tab.id();
            var title = tab.label();
            if (id.indexOf('messages') == 0) {
                t.initMessages(id.substring(t.tabPrefixMessages.length), title);
            } else {
                t.initDialogs(id.substring(t.tabPrefixDialogs.length), title);
            }
        });
    },
    showPage: function(pageName, title, params) {
        var t = this;
        var tabId, tabPrefix;
        title = $.trim(title) || '...';

        switch(pageName) {
            case 'dialogs':
                if (t.messages) {
                    t.messages.hide();
                }
                var listId = params.listId;
                tabPrefix = t.tabPrefixDialogs;
                tabId = tabPrefix + listId;

                if (!t.tabs.getTab(tabId)) {
                    t.tabs.removeTab(tabPrefix + t.curListId);
                    t.tabs.prependTab(tabId, title);
                }

                if (t.dialogs && t.dialogs.listId != listId) {
                    t.dialogs.destroy();
                    t.dialogs = null;
                }
                if (t.dialogs) {
                    t.dialogs.show();
                }
                if (!t.dialogs) {
                    t.$el.find('#list-dialogs').show();
                    t.dialogs = new Dialogs({
                        el: t.$el.find('#list-dialogs'),
                        listId: listId
                    });
                    t.dialogs.on('select', function(dialogId, title, userId) {
                        t.initMessages(dialogId, title, userId);
                    });
                    t.dialogs.on('addList', function() {
                        t.trigger('updateList');
                    });
                }

                t.curListId = listId;
            break;
            case 'messages':
                if (t.dialogs) {
                    t.dialogs.hide();
                }
                var dialogId = params.dialogId;
                var userId = params.userId;
                tabPrefix = t.tabPrefixMessages;
                tabId = tabPrefix + dialogId;

                if (!t.tabs.getTab(tabId).length) {
                    t.tabs.removeTab(tabPrefix + t.curDialogId);
                    t.tabs.appendTab(tabId, title, {userId: userId});
                }

                if (t.messages && t.messages.dialogId != dialogId) {
                    t.messages.destroy();
                    t.messages = null;
                }
                if (t.messages) {
                    t.messages.show();
                }
                if (!t.messages) {
                    t.$el.find('#list-messages').show();
                    t.messages = new Messages({
                        el: t.$el.find('#list-messages'),
                        dialogId: dialogId,
                        userId: userId
                    });
                    t.messages.on('markAsRead', function() {
                        t.trigger('updateList');
                    });
                }

                t.curDialogId = dialogId;
                t.trigger('selectDialog', dialogId);
            break;
        }

        t.tabs.selectTab(tabId);
        t.curTabId = tabId;
        t.curTabPrefix = tabPrefix;
    },
    initDialogs: function(listId, title) {
        var t = this;
        return t.showPage('dialogs', title, {listId: listId});
    },
    initMessages: function(dialogId, title, userId) {
        var t = this;
        return t.showPage('messages', title, {dialogId: dialogId, userId: userId});
    },
    setOnline: function(userId) {
        var t = this;
        if (t.tabs) {
            t.tabs.setOnline(userId);
        }
    },
    setOffline: function(userId) {
        var t = this;
        if (t.tabs) {
            t.tabs.setOffline(userId);
        }
    }
});

var EndlessList = Widget.extend({
    template: null,
    tmplItem: null,
    tmplItemsBlock: null,
    itemsBlockSelector: '.items-block',
    itemsListSelector: '.items',
    listId: null,
    itemsLimit: 20,
    currentPage: 0,
    eventName: 'get_items',
    isBlock: false,
    isDown: true,
    isPreload: true,
    preloadData: {},

    init: function(options) {
        this._super(options);

        var t = this;
        t.preloadData = {};
    },
    createBlock: function(data) {
        var t = this;
        if (data.length) {
            return $(t.tmpl(t.tmplItemsBlock, {id: t.currentPage, list: data}));
        } else {
            return false;
        }
    },
    createItem: function(data) {
        var t = this;
        return $(t.tmpl(t.tmplItem, data));
    },
    fireEvent: function(page, callback) {},
    makeItems: function($el) {},
    preload: function() {
        var t = this;
        if (!t.isPreload) return;
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
            t.fireEvent(page, function(data) {
                if ($.isFunction(callback)) callback(data);
            });
        }
    },
    showMore: function() {
        var t = this;
        if (t.isLock()) return;
        var $el = t.$el;
        var $items = $el.find(t.itemsListSelector);

        t.lock();
        t.getBlockData(t.currentPage, function(data) {
            var $block = t.createBlock(data);
            if (!$block) return;

            if (t.isDown) {
                $items.append($block);
                t.makeItems($block);
                t.unlock();
            } else {
                $items.prepend($block);
                t.makeItems($block);
                t.unlock();
                $(window).scrollTop($(window).scrollTop() + $block.outerHeight(true));
            }
        });
        t.currentPage++;
        t.preload();
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

var CachePage = EndlessList.extend({
    _isVisible: true,
    _isScrollBottom: false,
    _scroll: null,
    _html: null,

    isVisible: function() {
        return !!this._isVisible;
    },
    show: function() {
        var t = this;
        t._isVisible = true;
        t.$el.show();
        if (t._isScrollBottom) {
            $(window).scrollTop($(document).height() - $(window).height());
        } else {
            $(window).scrollTop(t._scroll);
        }
    },
    hide: function() {
        var t = this;
        t._isVisible = false;
        t._scroll = $(window).scrollTop();
        t._isScrollBottom = ($(document).height() - $(window).height() == $(window).scrollTop());
        t.$el.hide();
    },
    trigger: function(events, obj, obj2, obj3) {
        var t = this;
        if (!t.isVisible()) {
            return t;
        } else {
            return t._super(events, obj, obj2, obj3);
        }
    }
});

var Dialogs = CachePage.extend({
    template: DIALOGS,
    tmplItem: DIALOGS_ITEM,
    tmplItemsBlock: DIALOGS_BLOCK,
    itemsBlockSelector: '.dialogs-block',
    itemsListSelector: '.dialogs',
    itemsLimit: 20,
    eventName: 'get_dialogs',
    events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon': 'clickPlus'
    },

    fireEvent: function(page, callback) {
        var t = this;
        Events.fire(t.eventName, t.listId, (page * t.itemsLimit), t.itemsLimit, function(data) {
            callback(data);
            t.addToCollection(data);
        });
    },
    makeItems: function($el) {
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

    run: function(params) {
        var t = this;
        var $el = t.$el;
        var listId = t.listId = params.listId;

        t.templateData = {id: listId, list: [], isLoad: true};
        t.renderTemplate();
        t.getBlockData(t.currentPage, function(data) {
            t.templateData = {id: listId, list: data};
            t.listId = listId;
            t.renderTemplate();
            t.bindEvents();
            t.scrollTop();
            t.makeItems($el);
        });
        t.currentPage++;
        t.preload();
    },
    addToCollection: function(data) {
        $.each(data, function(i, message) {
            MessagesCollection.add(message.id, {
                id: 'loading',
                isNew: message.isNew,
                isViewer: message.isViewer,
                text: message.text,
                timestamp: message.timestamp,
                user: message.isViewer ? Configs.viewer : message.user
            });
        });
    },
    bindEvents: function() {
        var t = this;
        var $el = t.$el;
        t.on('scroll', (function onScroll() {
            if (t.isVisible()) {
                if ($(window).scrollTop() >= $(document).height() - $(window).height() - 300) {
                    t.showMore();
                }
            }
            return onScroll;
        })());
    },
    addDialog: function(dialog) {
        var t = this;
        var $el = t.$el;
        var isCurrentList = false;

        $.each(dialog.lists, function(i, listId) {
            if (listId == t.listId) {
                isCurrentList = true;
                return false;
            }
        });

        if (!isCurrentList) return;

        var $oldDialog = $el.find('.dialog[data-id=' + dialog.id + ']');
        if ($oldDialog.length) $oldDialog.remove();
        var $newDialog = t.createItem(dialog);
        $el.find(t.itemsBlockSelector).first().prepend($newDialog);
        t.makeItems($newDialog);
    },
    readMessage: function(message) {
        var t = this;
        var $el = t.$el;
        var messageId = message.id;
        var $dialog = $el.find('.dialog[data-message-id=' + messageId + ']');
        var $dialogMessage = $dialog.find('.from-me');
        $dialog.removeClass('new');
        $dialogMessage.removeClass('new');
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

                            var $menu = $(this).dropdown('getMenu');
                            var $selectedItems = $menu.find('.ui-dropdown-menu-item.active');
                            if ($selectedItems.length) {
                                $target.addClass('select').removeClass('plus');
                            } else {
                                $target.addClass('plus').removeClass('select');
                            }
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
                                Events.fire('add_to_list', dialogId, item.id, function() {
                                    t.trigger('addToList');
                                });
                            }
                        },
                        onunselect: function(item) {
                            Events.fire('remove_from_list', dialogId, item.id, function() {
                                t.trigger('removeFromList');
                            });
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
        var userId = $target.data('user-id');
        t.selectDialog(listId, title, userId, true);
    },
    selectDialog: function(dialogId, title, userId, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', dialogId, title, userId);
    },
    scrollTop: function() {
        var t = this;
        if (t.isVisible()) {
            $(window).scrollTop(0);
        }
    }
});

var Messages = CachePage.extend({
    template: MESSAGES,
    tmplItem: MESSAGES_ITEM,
    tmplItemsBlock: MESSAGES_BLOCK,
    itemsBlockSelector: '.messages-block',
    itemsListSelector: '.messages',
    itemsLimit: 30,
    eventName: 'get_messages',
    isDown: false,
    events: {
        'hover: .message.new': 'hoverMessage'
    },

    userId: null,
    listId: null,
    dialogId: null,
    user: {},

    fireEvent: function(page, callback) {
        var t = this;
        Events.fire(t.eventName, t.dialogId, (page * t.itemsLimit), t.itemsLimit, function(data) {
            callback(data);
        });
    },
    makeItems: function($el) {
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
    createBlock: function(data) {
        var t = this;
        if (data.messages.length) {
            return $(t.tmpl(t.tmplItemsBlock, {id: t.currentPage, list: data.messages}));
        } else {
            return false;
        }
    },

    run: function(params) {
        var t = this;
        var userId = t.userId = params.userId;
        var dialogId = t.dialogId = params.dialogId;
        var lastMessage = MessagesCollection.get(dialogId);

        t.templateData = {
            isLoad: true,

            id: dialogId,
            list: !lastMessage ? [] : [lastMessage],
            viewer: Configs.viewer,
            user: UsersCollection.get(userId) || Data.users[0]
        };
        t.renderTemplate();
        t.makeItems(t.$el.find(t.itemsListSelector));
        t.updateTop();
        t.scrollBottom();

        t.getBlockData(t.currentPage, function(data) {
            t.listId = data.lists[0];
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
            t.makeItems(t.$el.find(t.itemsListSelector));
            t.initTextarea();
            t.updateTop();
            t.scrollBottom();
            t.bindEvents();
            setTimeout(function() {
                t.scrollBottom();
            }, 100);
        });
        t.currentPage++;
        t.preload();
    },
    bindEvents: function() {
        var t = this;
        var $el = t.$el;
        var listId = t.listId;

        t.on('scroll', (function onScroll() {
            if (t.isVisible()) {
                t.updateTop();
                if ($(window).scrollTop() < 300) {
                    t.showMore();
                }
            }
            return onScroll;
        })());
        $el.find('.button.send').click(function() {
            t.sendMessage();
        });
        $el.find('.save-template').click(function() {
            var box = new CreateTemplateBox(listId, $el.find('textarea').val());
            box.show();
        });
        $el.find('textarea').keydown(function(e) {
            if ((e.ctrlKey || e.metaKey) && e.keyCode == KEY.ENTER) {
                t.sendMessage();
                return false;
            }
        });
    },
    initTextarea: function() {
        var t = this;
        var $el = t.$el;
        var $textarea = $el.find('textarea');
        var dialogId = t.dialogId;
        var listId = t.listId;
        $textarea.placeholder();
        $textarea.autoResize();
        $textarea.inputMemory('message' + dialogId);
        $textarea.focus();
        $textarea[0].scrollTop = $textarea[0].scrollHeight;
        Events.fire('get_templates', listId, function(data) {
            $textarea.autocomplete({
                position: 'top',
                notFoundText: '',
                data: data,
                strictSearch: true,
                getValue: function() {
                    var text = $.trim($textarea.val());
                    return text ? text : 'notShowAllItems';
                },
                onchange: function(item) {
                    $textarea.val(item.title);
                }
            });
        });
    },
    addMessage: function(message) {
        var t = this;
        var $el = t.$el;

        if (message.dialogId != t.dialogId) return;
        if (!message.isViewer) message.user = t.user;

        var $oldMessage = $el.find('.message[data-id=' + message.id + ']');
        if ($oldMessage.length) return;
        var $newMessage = t.createItem(message);
        $el.find(t.itemsBlockSelector).last().append($newMessage);
        t.makeItems($newMessage);
        t.scrollBottom();
    },
    hoverMessage: function(e) {
        var t = this;
        if (e.type != 'mouseenter') return;
        var $message = $(e.currentTarget);
        if ($message.hasClass('viewer')) return;
        $message.removeClass('new');
        Events.fire('message_mark_as_read', $message.data('id'), this.dialogId, function() {
            t.trigger('markAsRead');
        });
    },
    readMessage: function(message) {
        var t = this;
        var $el = t.$el;
        var messageId = message.id;
        var $message = $el.find('.message[data-id=' + messageId + ']');
        $message.removeClass('new');
    },
    updateTop: function() {
        var t = this;
        if (t.isVisible()) {
            var $el = t.$el;
            var $messages = $el.find(t.itemsListSelector);
            $messages.css('padding-top', $(window).height() - $messages.height() - 152);
        }
    },
    scrollBottom: function() {
        var t = this;
        if (t.isVisible()) {
            $(window).scrollTop($(document).height());
        }
    },
    sendMessage: function() {
        var t = this;
        var $el = t.$el;
        var $textarea = $el.find('textarea');
        var text = $.trim($textarea.val());

        if (text) {
            $textarea.val('');
            var $newMessage = t.createItem({
                id: 'loading',
                isNew: true,
                isViewer: true,
                text: makeMsg(text),
                timestamp: Math.floor(new Date().getTime() / 1000),
                user: Configs.viewer
            });
            $newMessage.addClass('loading');
            $el.find(t.itemsBlockSelector).last().append($newMessage);
            t.makeItems($newMessage);
            t.scrollBottom();
            $textarea.focus();
            Events.fire('send_message', t.dialogId, text, function(messageId) {
                if (!messageId) {
                    $textarea.val(text);
                    $newMessage.remove();
                    return;
                }
                var $oldMessage = $el.find('[data-id=' + messageId + ']');
                if ($oldMessage.length) {
                    return $newMessage.remove();
                } else {
                    $newMessage.removeClass('loading').attr('data-id', messageId);
                }
            });
        } else {
            $textarea.focus();
        }
    }
});

/* Right Column */
var RightColumn = Widget.extend({
    template: RIGHT_COLUMN,
    list: null,

    run: function() {
        this._super();

        var t = this;
        t.initList();
    },

    update: function() {
        var t = this;
        t.list.update();
    },

    addMessage: function(message) {
        var t = this;
        t.list.addMessage(message);
    },

    initList: function() {
        var t = this;
        t.list = new List({
            el: t.$el.find('.list')
        });
        t.list.on('selectDialogs', function(listId, title) {
            t.trigger('selectDialogs', listId, title);
        });
        t.list.on('selectMessages', function(dialogId, title) {
            t.trigger('selectMessages', dialogId, title);
        });
        t.on('selectDialog', function(dialogId) {
            t.list.currentDialogId = dialogId;
        });
    }
});

var List = Widget.extend({
    template: LIST,
    currentListId: null,
    currentDialogId: null,
    isEditMode: true,
    _isDragging: false,

    events: {
        'mousedown: .drag-wrap': 'mouseDownList',
        'click: .item > .title': 'clickList'
    },

    run: function() {
        var t = this;
        var $el = t.$el;

        Events.fire('get_lists', function(data, count) {
            t.templateData = {list: data, count: count};
            t.renderTemplate();
            if (t.currentListId) {
                $el.find('.title.active, .dialog.active').removeClass('active');
                $el.find('.item[data-id=' + t.currentListId + ']').find('.title').addClass('active');
            }
        });
    },

    clickList: function(e) {
        var t = this;
        if (t._isDragging) return;
        var $target = $(e.currentTarget).closest('.item');
        var title = $target.data('title');
        var listId = $target.data('id');
        t.$el.find('.title.active, .dialog.active').removeClass('active');
        $target.find('.title').addClass('active');
        $target.find('.title').removeClass('new');
        t.trigger('selectDialogs', listId, title);
        t.currentListId = listId;
        Events.fire('set_list_as_read', listId, function() {});
    },

    mouseDownList: function(e) {
        var t = this;
        if (!t.isEditMode) return;
        var $placeholder = $(e.currentTarget);
        var $target = $placeholder.find('.item:first');
        var timeout = setTimeout(function() {
            t._isDragging = true;
            $target.addClass('drag');
            $placeholder.height($target.height());
            $('html, body').addClass('no-select');
        }, 300);
        var startY = e.clientY;

        $(window).on('mousemove.list', (function update(e) {
            if (t._isDragging) {
                var top = e.clientY - startY;
                var height = $placeholder.height();
                var position = intval((e.clientY - $placeholder.offset().top) / height);
                var $next = $placeholder.next('.drag-wrap');
                var $prev = $placeholder.prev('.drag-wrap');

                if (position > 0 && $next.length) {
                    $placeholder.before($next);
                    startY += height;
                } else if (position < 0 && $prev.length) {
                    $placeholder.after($prev);
                    startY -= height;
                }
                top = e.clientY - startY;
                $target.css({top: top});
            }

            return update;
        })(e));

        $(window).on('mouseup.list', function(e) {
            $(this).off('mousemove.list mouseup.list');
            clearTimeout(timeout);

            if (!t._isDragging) return;
            $('html, body').removeClass('no-select');
            $target.removeClass('drag').css({top: 0});
            setTimeout(function() {
                t._isDragging = false;
                t.setOrder();
            }, 0);
        });
    },

    update: function() {
        var t = this;
        t.run();
    },

    setOrder: function() {
        var t = this;
        var $el = t.$el;
        var listIds = [];
        $el.find('.item').each(function() {
            var listId = $(this).data('id');
            if (listId != Configs.commonDialogsList) listIds.push(listId);
        });
        Events.fire('set_list_order', listIds.join(','), function() {});
    },

    addMessage: function(message) {
        var t = this;
        if (message.dialogId != t.currentDialogId) {
            Events.fire('set_list_as_new', message.lists.join(','), function() {
                t.update();
            });
        } else {
            t.update();
        }
    }
});

var Tabs = Widget.extend({
    template: TABS,
    tabTemplate: TABS_ITEM,
    activeClass: 'selected',
    barClass: 'tab-bar',
    tabClass: 'tab',
    _activeTab: null,

    events: {
        'click: .tab': 'clickTab'
    },

    clickTab: function(e) {
        var t = this;
        var tabId = $(e.currentTarget).data('tab').id;
        var tab = t.getTab(tabId);
        if (tab) {
            t.activeTab(tab);
        }
    },

    selectTab: function(tabId) {
        var t = this;
        var tab = t.getTab(tabId);
        if (tab) {
            t.activeTab(tab);
            t.trigger('select', tab);
        }
    },
    activeTab: function(activeTab) {
        var t = this;
        if (!arguments.length) {
            return t._activeTab;
        } else {
            if (t._activeTab) {
                t._activeTab.unSelect();
            }
            t._activeTab = activeTab;
            activeTab.select();
            return t;
        }
    },
    getTabByParam: function(key, value) {
        var t = this;
        var tabsCollection = t._tabsCollection || (t._tabsCollection = {});
        for (var tabId in tabsCollection) {
            if (!tabsCollection.hasOwnProperty(tabId)) continue;

            var tab = tabsCollection[tabId];
            if (tab.data()[key] && tab.data()[key] == value) {
                return tab;
            }
        }
    },
    getTab: function(tabId) {
        var t = this;
        var tabsCollection = t._tabsCollection || (t._tabsCollection = {});
        return tabsCollection[tabId];
    },
    createTab: function(tabId, label, data) {
        var t = this;
        var $tab;
        var tabsCollection = t._tabsCollection || (t._tabsCollection = {});
        if (t.getTab(tabId)) {
            $tab = t.getTab(tabId).$el;
        } else {
            $tab = new Tab({
                el: $('<div>'),
                id: tabId,
                label: label,
                data: data
            });
        }
        tabsCollection[tabId] = $tab;
        return $tab;
    },
    appendTab: function(tabId, title, data) {
        var t = this;
        var $tab = t.createTab(tabId, title, data);
        t.$el.find('.' + t.barClass).append($tab.$el);
    },
    prependTab: function(tabId, title, data) {
        var t = this;
        var $tab = t.createTab(tabId, title, data);
        t.$el.find('.' + t.barClass).prepend($tab.$el);
    },
    removeTab: function(tabId) {
        var t = this;
        var tab = t.getTab(tabId);
        var tabsCollection = t._tabsCollection || (t._tabsCollection = {});
        if (tab) {
            delete tabsCollection[tabId];
            tab.remove();
        }
    },
    getData: function(tabId) {
        var t = this;
        return t.getTab(tabId).data();
    }
});

var Tab = Widget.extend({
    _id: null,
    _label: null,
    _dataKey: 'tab',
    selectedClass: 'selected',
    template: TABS_ITEM,

    run: function(options) {
        var t = this;
        t._id = options.id;
        t._label = options.label;
        t.templateData = {
            id: t._id,
            label: t._label
        };
        t.data(options.data);
        t.renderTemplate();
    },

    id: function(id) {
        var t = this;
        if (!arguments.length) {
            return t._id;
        } else {
            t._id = id;
            t.renderTemplate();
            return t;
        }
    },

    label: function(label) {
        var t = this;
        if (!arguments.length) {
            return t._label;
        } else {
            t._label = label;
            t.renderTemplate();
            return t;
        }
    },

    select: function() {
        var t = this;
        t.$el.addClass(t.selectedClass);
    },

    unSelect: function() {
        var t = this;
        t.$el.removeClass(t.selectedClass);
    },

    data: function(data) {
        var t = this;
        if (!arguments.length) {
            return t.$el.data(t._dataKey);
        } else {
            t.$el.data(t._dataKey, $.extend(data, {id: t._id, label: t._label}));
            return t;
        }
    },

    remove: function() {
        var t = this;
        t.$el.remove();
    }
});

var MessengerTab = Tab.extend({
    _online: null,

    run: function(options) {
        var t = this;
        t._super(options);
    },

    online: function(online) {
        var t = this;
        if (!arguments.length) {
            return t._online;
        } else {
            t._online = online;
            t.renderTemplate();
            return t;
        }
    }
});

var MessengerTabs = Tabs.extend({
});

/* Useful */
function makeMsg(msg, isNotClean) {
    function clean(str) {
        if (isNotClean) {
            return str;
        }
        else {
            return str ? str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;') : '';
        }
    }

    function indexOf(arr, value, from) {
        for (var i = from || 0, l = (arr || []).length; i < l; i++) {
            if (arr[i] == value) return i;
        }
        return -1;
    }

    return clean(msg).replace(/\n/g, '<br>').replace(/(@)?((https?:\/\/)?)((([A-Za-z0-9][A-Za-z0-9\-\_\.]*[A-Za-z0-9])|(([а-яА-Я0-9\-\_\.]+\.рф)))(\/([A-Za-zА-Яа-я0-9\-\_#%&?+\/\.=;:~]*[^\.\,;\(\)\?\<\&\s:])?)?)/ig, function () {
        var domain = arguments[5],
            url = arguments[4],
            full = arguments[0],
            protocol = arguments[2] || 'http://';
        var pre = arguments[1];

        if (domain.indexOf('.') == -1) return full;
        var topDomain = domain.split('.').pop();
        if (topDomain.length > 5 || indexOf('aero,asia,biz,com,coop,edu,gov,info,int,jobs,mil,mobi,name,net,org,pro,tel,travel,xxx,ru,ua,su,рф,fi,fr,uk,cn,gr,ie,nl,au,co,gd,im,cc,si,ly,gl,be,eu,tv,to,me,io'.split(','), topDomain) == -1) return full;

        if (pre == '@') {
            return full;
        }
        try {
            full = decodeURIComponent(full);
        } catch (e){}

        if (full.length > 255) {
            full = full.substr(0, 253) + '..';
        }

        if (domain.match(/^([a-zA-Z0-9\.\_\-]+\.)?(vkontakte\.ru|vk\.com|vk\.cc|vkadre\.ru|vshtate\.ru|userapi\.com)$/)) {
            url = url.replace(/[^a-zA-Z0-9#%;_\-.\/?&=\[\]]/g, encodeURIComponent);
            return '<a href="'+ (protocol + url).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '" target="_blank">' + full + '</a>';
        }
        return '<a href="http://vk.com/away.php?utf=1&to=' + encodeURIComponent(protocol + url) + '" target="_blank">' + full + '</a>';
    })
}

function makeDlg(text) {
    var clearText = text;
    clearText = clearText.split('<br>');
    if (clearText.length > 2) {
        clearText = clearText.slice(0, 2).join('<br>') + '...';
    } else {
        clearText = clearText.join('<br>');
    }
    if (clearText.length > 250) {
        clearText = clearText.substring(0, 200) + '...';
    }
    return clearText;
}

function CreateTemplateBox(listId, text) {
    var SAVE_TEMPLATE_BOX =
    '<div class="box-templates">' +
        '<div class="title">' +
            'Выберите списки' +
        '</div>' +
        '<div class="input-wrap">' +
            '<input class="lists" type="text"/>' +
        '</div>' +
        '<div class="title">' +
            'Введите текст шаблона' +
        '</div>' +
        '<div class="input-wrap">' +
            '<textarea class="template-text"><?=text?></textarea>' +
        '</div>' +
    '</div>';

    var box = new Box({
        title: 'Добавление нового шаблона',
        html: tmpl(BOX_LOADING, {height: 100}),
        buttons: [
            {label: 'Закрыть'}
        ],
        onshow: function() {
            Events.fire('get_lists', function(lists) {
                var listsIds = [];
                var clearLists = [];
                var currentList = {};
                $.each(lists, function(i, listItem) {

                    if (listItem.id) {
                        clearLists.push(listItem);
                    }
                    if (listId == listItem.id) {
                        currentList = listItem;
                    }
                });

                box.setHTML(tmpl(SAVE_TEMPLATE_BOX, {text: text}));
                box.setButtons([
                    {label: 'Сохранить', onclick: saveTemplate},
                    {label: 'Отменить', isWhite: true}
                ]);

                var $input = box.$el.find('.lists');
                var $textarea = box.$el.find('.template-text');
                var templateText = $textarea.val();
                $textarea.focus();
                $textarea.selectRange(templateText.length, templateText.length);

                $input.tags({
                    onadd: function(tag) {
                        listsIds.push(parseInt(tag.id));
                    },
                    onremove: function(tagId) {
                        listsIds = jQuery.grep(listsIds, function(listsIds) {
                            return listsIds != tagId;
                        });
                    }
                }).autocomplete({
                    data: clearLists,
                    target: $input.closest('.ui-tags'),
                    onchange: function(item) {
                        $(this).tags('addTag', item).val('').focus();
                    }
                }).keydown(function(e) {
                    if (e.keyCode == KEY.DEL && !$(this).val()) {
                        $(this).tags('removeLastTag');
                    }
                });

                if (currentList.id) {
                    $input.tags('addTag', currentList);
                }

                function saveTemplate() {
                    var $textarea = box.$el.find('textarea');
                    var text = $textarea.val();
                    box.setHTML(tmpl(BOX_LOADING, {height: 100}));
                    box.setButtons([{label: 'Закрыть'}]);
                    Events.fire('add_template', text, listsIds.join(','), function() {
                        box.hide();
                    });
                }
            });
        },
        onhide: function() {
            box.remove();
        }
    });
    return box;
}