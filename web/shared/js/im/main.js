var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    commonDialogsList: 999999,
    viewer: {},
    disableAutocomplete: false
};

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

$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    if (!Configs.vkId) {
        return location.replace('/login/?' + btoa('im'));
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }
    if (!Configs.token) {
        return location.replace('/im/login/');
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
                url: 'http://im.' + hostname + '/int/controls/watchDog/',
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
        t.rightColumn.on('selectMessages', function(id, title) {
            t.leftColumn.initMessages(id, title);
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
        var dirtyMessage;
        var message;
        if (!event || !event.type) return;

        switch (event.type) {
            //@todo: объеденить
            case 'outMessage':
                dirtyMessage = event.content;
                message = {
                    lists: (dirtyMessage.groups == '-1') ? [Configs.commonDialogsList] : dirtyMessage.groups,

                    id: dirtyMessage.mid,
                    isNew: (dirtyMessage.read_state != 1),
                    isViewer: true,
                    text: dirtyMessage.body,
                    dialogId: dirtyMessage.dialog_id,
                    attachments: [],
                    timestamp: dirtyMessage.date,
                    user: dirtyMessage.out ? Configs.viewer : {}
                };
                t.leftColumn.addMessage(message);
            break;
            case 'inMessage':
                dirtyMessage = event.content;
                message = {
                    lists: (dirtyMessage.groups == '-1') ? [Configs.commonDialogsList] : dirtyMessage.groups,

                    id: dirtyMessage.mid,
                    isNew: (dirtyMessage.read_state != 1),
                    isViewer: (dirtyMessage.from_id.uid == Configs.vkId),
                    text: dirtyMessage.body,
                    dialogId: dirtyMessage.dialog_id,
                    attachments: [],
                    timestamp: dirtyMessage.date,
                    user: dirtyMessage.out ? Configs.viewer : {}
                };
                t.leftColumn.addMessage(message);

                if (!message.isViewer) {
                    t.rightColumn.addMessage(message);
                }
            break;
            case 'read':
                message = event.content;
                var messageId = message.mid;

                t.leftColumn.readMessage(messageId);
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

    readMessage: function(messageId) {
        var t = this;

        if (t.messages) {
            t.messages.readMessage(messageId);
        }
    },
    addMessage: function(message) {
        var t = this;

        if (t.messages) {
            t.messages.addMessage(message);
        }
        if (t.dialogs) {
            t.dialogs.addMessage(message);
        }
    },

    initTabs: function() {
        var t = this;

        t.tabs = new Tabs({
            el: t.$el.find('.header'),
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

                if (!t.tabs.getTab(tabId).length) {
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
                    t.tabs.appendTab(tabId, title);
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
                t.trigger('selectDialog', dialogId)
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
    }
});

var EndlessList = Widget.extend({
    template: null,
    tmplItem: null,
    tmplItemsBlock: null,
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
            return $(tmpl(t.tmplItemsBlock, {id:t.currentPage, list:data}));
        } else {
            return false;
        }
    },
    createItem: function(data) {
        var t = this;
        return $(tmpl(t.tmplItem, data));
    },
    fireEvent: function(page, callback) {
    },
    makeItems: function($el) {
    },
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
    trigger: function(events, obj, obj2) {
        var t = this;
        if (!t.isVisible()) {
            return t;
        } else {
            return t._super(events, obj, obj2);
        }
    }
});

var Dialogs = CachePage.extend({
    template: DIALOGS,
    tmplItem: DIALOGS_ITEM,
    tmplItemsBlock: DIALOGS_BLOCK,
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
                user: message.isViewer ? message.viewer : message.user
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
                return onScroll;
            }
        })());
    },
    addMessage: function(message) {
        var t = this;
        var $el = t.$el;
        var listId = t.listId;
        var isUpdate = false;

        $.each(message.lists, function(i, listId) {
            if (listId == t.listId) {
                isUpdate = true;
                return false;
            }
        });

        if (isUpdate) {
            t.getBlockData(0, function(data) {
                t.templateData = {id: listId, list: data};
                t.renderTemplate();
                t.bindEvents();
                t.scrollTop();
                t.makeItems($el);
                t.preload();
            });
        }
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
            return $(tmpl(t.tmplItemsBlock, {id: t.currentPage, list: data.messages}));
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
                // Автокомплит по отдельному слову
                //getValue: function() {
                //    var text = $textarea.val();
                //    var words = text.split(' ');
                //    var lastWord = words.pop();
                //    return lastWord;
                //},
                //onchange: function(item) {
                //    var text = $textarea.val();
                //    var words = text.split(' ');
                //    var lastWord = words.pop();
                //    words.push(item.title);
                //    text = words.join(' ');
                //    $textarea.val(text);
                //}
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
        $el.find(t.itemsListSelector).append($newMessage);
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
    readMessage: function(messageId) {
        var t = this;
        var $el = t.$el;
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
            $el.find(t.itemsListSelector).append($newMessage);
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
    dialogsLimit: 100,
    currentListId: null,
    currentDialogId: null,
    isEditMode: true,
    isDragging: false,

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
        if (t.isDragging) return;
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
            t.isDragging = true;
            $target.addClass('drag');
            $placeholder.height($target.height());
            $('html, body').addClass('no-select');
        }, 300);
        var startY = e.clientY;

        $(window).on('mousemove.list', (function update(e) {
            if (t.isDragging) {
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

            if (!t.isDragging) return;
            $('html, body').removeClass('no-select');
            $target.removeClass('drag').css({top: 0});
            setTimeout(function() {
                t.isDragging = false;
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

    events: {
        'click: .tab': 'clickTab'
    },

    clickTab: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var tabId = $target.data('id');
        if (!$target.hasClass(t.activeClass)) {
            t.selectTab(tabId, true);
        }
    },

    selectTab: function(id, isTrigger) {
        var t = this;
        var $tab = t.getTab(id);

        t.getSelectedTab().removeClass(t.activeClass);
        $tab.addClass(t.activeClass);

        if (isTrigger) t.trigger('select', id);
    },
    getTab: function(id) {
        var t = this;

        return t.$el.find('.tab[data-id="' + id + '"]');
    },
    getSelectedTab: function() {
        var t = this;
        return t.$el.find('.tab.' + t.activeClass);
    },
    appendTab: function(id, title) {
        var t = this;
        if (t.getTab(id).length) return;
        t.$el.find('.tab-bar').append(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    prependTab: function(id, title) {
        var t = this;
        if (t.getTab(id).length) return;
        t.$el.find('.tab-bar').prepend(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    removeTab: function(id) {
        var t = this;
        t.getTab(id).remove();
    }
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
            Events.fire('get_lists', function(dirtyLists) {
                var listsIds = [];
                var clearLists = [];
                var currentList = {};
                $.each(dirtyLists, function(i, list) {
                    if (list.id) {
                        clearLists.push(list);
                    }
                    if (listId == list.id) {
                        currentList = list;
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
