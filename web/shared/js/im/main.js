var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    hostName: hostname,
    commonDialogsList: 999999,
    disableAutocomplete: false,
    easyDateParams: {
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
    }
};

var userCollection = new UserCollection();
var messageCollection = new MessageCollection();
var dialogCollection = new DialogCollection();
var listCollection = new ListCollection();

var Main = Widget.extend({
    _template: MAIN,
    _leftColumn: null,
    _leftColumnSelector: '#left-column',
    _rightColumn: null,
    _rightColumnSelector: '#right-column',

    run: function() {
        var t = this;
        t.el().hide();

        Events.fire('get_viewer', function(data) {
            if (!data) {
                location.href = '/im/login/?' + encodeURIComponent('im/');
                return;
            }

            var viewer = new UserModel(data);
            userCollection.add(viewer.id(), viewer);

            t.el().fadeIn(500);
            t.renderTemplate();

            t._leftColumn = new LeftColumn({
                selector: t._leftColumnSelector
            });
            t._rightColumn = new RightColumn({
                selector: t._rightColumnSelector,
                model: new ListsModel()
            });

            t.on('scroll', function(e) {
                t._leftColumn.onScroll(e);
            });

            t._leftColumn.on('changeDialog', function(dialogId) {
                t._rightColumn.setDialog(dialogId);
            });
            t._leftColumn.on('changeList', function(listId) {
                t._rightColumn.setList(listId);
            });
            t._leftColumn.on('addList', function() {
                t._rightColumn.update();
            });
            t._leftColumn.on('markAsRead', function() {
                t._rightColumn.update();
            });
            t._rightColumn.on('changeDialog', function(dialogId) {
                t._leftColumn.showDialog(dialogId);
            });
            t._rightColumn.on('setList', function(listId) {
                t._leftColumn.showList(listId);
            });

            (function poll(ts) {
                $.ajax({
                    url: 'http://im.' + Configs.hostName + '/int/controls/watchDog/',
                    dataType: 'jsonp',
                    data: {
                        userId: Configs.vkId,
                        timeout: 15,
                        ts: ts
                    },
                    success: function(data) {
                        poll(data.response.ts);
                        $.each(data.response.events, function(i, event) {
                            t.fireEvent(event);
                        });
                    }
                });
            })();

            t._leftColumn.showList(Configs.commonDialogsList);
        });
    },

    fireEvent: function(event) {
        var t = this;
        if (!event || !event.type) {
            console.log(['Bad Event: ', event]);
            return;
        }

        switch (event.type) {
            case 'inMessage':
            case 'outMessage':
                (function() {
                    var isViewer = (event.type == 'outMessage');
                    var message = Cleaner.longPollMessage(event.content, isViewer);
                    var dialog = Cleaner.longPollDialog(event.content, isViewer);
                    t._leftColumn.addMessage(new MessageModel(message));
                    t._leftColumn.addDialog(new DialogModel(dialog));

                    if (!message.isViewer) {
                        t._rightColumn.update();
                    }
                })();
                break;
            case 'read':
                (function() {
                    var message = Cleaner.longPollRead(event.content);
                    t._leftColumn.readMessage(message.id);
                    t._leftColumn.readDialog(message.dialogId);
                })();
                break;
            case 'online':
            case 'offline':
                (function() {
                    var online = Cleaner.longPollOnline(event.content);
                    if (online.isOnline) {
                        t._leftColumn.setOnline(online.userId);
                    } else {
                        t._leftColumn.setOffline(online.userId);
                    }
                })();
                break;
        }
    }
});

var LeftColumn = Widget.extend({
    _template: LEFT_COLUMN,
    _dialogs: null,
    _dialogsSelector: '#list-dialogs',
    _messages: null,
    _messagesSelector: '#list-messages',
    _tabs: null,
    _tabsSelector: '.header',

    run: function() {
        var t = this;
        t.renderTemplate();

        t._tabs = new Tabs({
            selector: t._tabsSelector,
            model: new TabsModel()
        });
        t._messages = new Messages({
            selector: t._messagesSelector,
            model: new MessagesModel()
        });
        t._dialogs = new Dialogs({
            selector: t._dialogsSelector,
            model: new DialogsModel()
        });

        t._dialogs.on('clickDialog', function(e) {
            var $dialog = $(e.currentTarget);
            var dialogId = $dialog.data('id');
            t.showDialog(dialogId, true);
        });

        t._dialogs.on('addList', function(e) {
            t.trigger('addList');
        });

        t._messages.on('hoverMessage', function(e) {
            var $message = $(e.currentTarget);
            var messageId = $message.data('id');
            var dialogId = t._messages._pageId;
            if ($message.hasClass('viewer')) return;
            t._messages.readMessage(messageId);
            t._dialogs.readDialog(dialogId);
            Events.fire('message_mark_as_read', messageId, dialogId, function() {
                t.trigger('markAsRead');
            });
        });

        t._tabs.on('clickDialog', function(e) {
            var $tab = $(e.currentTarget);
            var dialogId = $tab.data('id');
            t.showDialog(dialogId, true);
        });

        t._tabs.on('clickList', function(e) {
            var $tab = $(e.currentTarget);
            var listId = $tab.data('id');
            t.showList(listId, true);
        });

        t._tabs.on('addList', function() {
            t.trigger('addList');
        });
    },
    onScroll: function(e) {
        var t = this;
        t._messages.onScroll(e);
        t._dialogs.onScroll(e);
    },

    showList: function(listId, isTrigger) {
        var t = this;
        t._messages.hide();
        t._dialogs.show().changePage(listId);
        t._tabs.setList(listId);
        if (isTrigger) t.trigger('changeList', listId);
    },

    showDialog: function(dialogId, isTrigger) {
        var t = this;
        t._dialogs.hide();
        t._messages.show().changePage(dialogId);
        t._tabs.setDialog(dialogId);
        if (isTrigger) t.trigger('changeDialog', dialogId);
    },

    setOnline: function(userId) {
        var t = this;
        t._tabs.setOnline(userId);
    },
    setOffline: function(userId) {
        var t = this;
        t._tabs.setOffline(userId);
    },
    addMessage: function(messageModel) {
        var t = this;
        t._messages.addMessage(messageModel);
    },
    addDialog: function(dialog) {
        var t = this;
        t._dialogs.addDialog(dialog);
    },
    readMessage: function(messageId) {
        var t = this;
        t._messages.readMessage(messageId);
    },
    readDialog: function(dialogId) {
        var t = this;
        t._dialogs.readDialog(dialogId);
    }
});

var Page = Widget.extend({
    _isVisible: false,
    _templateLoading: '',
    _pageId: null,
    _cache: null,
    _service: 'get_dialogs',
    _isLock: false,
    _isCache: true,
    _scroll: null,
    _isBottom: false,

    run: function() {
        var t = this;
        if (t._pageId) {
            t.changePage(t._pageId, true);
        }
    },
    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t._pageId != pageId)) {
            t._pageId = pageId;
            t.renderTemplateLoading();
            t.getData();
        }
    },
    renderTemplateLoading: function() {
        var t = this;
        t.el().html(t.tmpl()(t._templateLoading, t.model()));
        return this;
    },
    getData: function() {
        var t = this;
        if (!t.isLock()) {
            t.lock();
            Events.fire(t._service, function(data) {
                t.unlock();
                t.model().data(data);
                t.renderTemplate();
            });
        }
    },
    isVisible: function() {
        return !!this._isVisible;
    },
    show: function() {
        var t = this;
        t._isVisible = true;
        t.el().show();
        if (t._isBottom) {
            $(window).scrollTop($(document).height() - $(window).height());
        } else {
            $(window).scrollTop(t._scroll);
        }
        return this;
    },
    hide: function() {
        var t = this;
        t._isVisible = false;
        t._scroll = $(window).scrollTop();
        t._isBottom = $(window).scrollTop() + $(window).height() == $(document).height();
        t.el().hide();
        return this;
    },
    isLock: function() {
        return !!this._isLock;
    },
    lock: function() {
        this._isLock = true;
    },
    unlock: function() {
        this._isLock = false;
    }
});

var EndlessPage = Page.extend({
    _templateItem: '',
    _itemsLimit: 20,
    _itemsSelector: '',
    _pageLoaded: null,
    _isTop: false,
    _isPreload: true,
    _preloadData: null,
    _isEnded: false,

    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t._pageId != pageId)) {
            t._pageLoaded = 0;
            t._preloadData = {};
            t._isEnded = false;
            if (t.model() && t.model().list) {
                t.model().list([]);
            }
        }
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    getData: function() {
        var t = this;
        var limit = t._itemsLimit;
        var offset = t._pageLoaded * limit;
        var pageId = t._pageId;

        t.onShow();
        t.lock();
        Events.fire(t._service, pageId, offset, limit, function(data) {
            t.unlock();

            if (pageId == t._pageId) {
                t.onLoad(data);
                t.renderTemplate();
                t.makeList(t.el().find(t._itemsSelector));
                t.onRender();

                if (t._isPreload) {
                    t.preloadData(1);
                }
            }
        });
    },
    preloadData: function(pageNumber) {
        var t = this;
        var limit = t._itemsLimit;
        var offset = pageNumber * limit;
        var pageId = t._pageId;
        var preloadData = t._preloadData || {};

        if (!preloadData[pageNumber] && !t.isLock()) {
            Events.fire(t._service, pageId, offset, limit, function(data) {
                if (pageId == t._pageId) {
                    preloadData[pageNumber] = data;
                }
            });
        }
    },
    onShow: function() {},
    onLoad: function(data) {},
    onRender: function() {},
    makeList: function($list) {},
    showMore: function() {
        var t = this;
        var currentPage = t._pageLoaded;
        var nextPage = currentPage + 1;
        var limit = t._itemsLimit;
        var offset = nextPage * limit;
        var pageId = t._pageId;
        var preloadData = t._preloadData || {};

        if (t._isEnded) {
            return;
        }

        if (!t.isLock()) {
            t.lock();
            if (preloadData[nextPage]) {
                setData(preloadData[nextPage]);
            } else {
                Events.fire(t._service, pageId, offset, limit, function(data) {
                    setData(data);
                });
            }
        }

        function setData(data) {
            t.unlock();
            if (pageId == t._pageId) {
                t.onLoad(data);
                var $list = t.el().find(t._itemsSelector);
                var $block;
                var bottom = $(document).height() - $(window).scrollTop();
                var html = '';
                $.each(data.list, function(i, obj) {
                    html += t.tmpl()(t._templateItem, obj);
                });

                $block = $(html);
                if (t._isTop) {
                    $list.prepend($block);
                    $(window).scrollTop($(document).height() - bottom);
                } else {
                    $list.append($block);
                }
                t.makeList($block);
                t._pageLoaded = nextPage;
                if (t._isPreload) {
                    t.preloadData(nextPage + 1);
                }
            }
        }
    },
    checkAtTop: function() {
        var t = this;
        return !!($(window).scrollTop() < 300);
    },
    checkAtBottom: function() {
        var t = this;
        return !!($(window).scrollTop() >= $(document).height() - $(window).height() - 300);
    },
    onScroll: function() {
        var t = this;
        if (!t.isVisible()) return;
        if (t.checkAtTop() && t._isTop || t.checkAtBottom() && !t._isTop) {
            t.showMore();
        }
    }
});

var Dialogs = EndlessPage.extend({
    _template: DIALOGS,
    _modelClass: DialogsModel,
    _templateItem: DIALOGS_ITEM,
    _templateLoading: DIALOGS_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.dialogs',
    _service: 'get_dialogs',
    _pageLoaded: null,
    _pageId: Configs.commonDialogsList,

    _events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon': 'clickPlus'
    },

    makeList: function($list) {
        $list.find('.date').each(function() {
            var $date = $(this);
            var timestamp = $date.text();
            var currentDate = new Date();
            var date = new Date(timestamp * 1000);
            var m = date.getMonth() + 1;
            var y = date.getFullYear() + '';
            var d = date.getDate() + '';
            var h = date.getHours() + '';
            var min = date.getMinutes() + '';
            var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
            if (currentDate.getDate() != d) {
                text += ', ' + d + '.' + m + '.' + (y.substr(-2));
            }
            $date.html(text);
        });
    },
    clickDialog: function(e) {
        var t = this;
        t.trigger('clickDialog', e);
    },
    clickPlus: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $dialog = $target.closest('.dialog');
        var dialogId = $dialog.data('id');
        if (!$target.data('dropdown')) {
            (function updateDropdown() {

                function onCreate() {
                    var dialogs = t.model().list();

                    $.each(dialogs, function(i, dialog) {
                        if (dialog.id == dialogId) {
                            $.each(dialog.lists, function(i, listId) {
                                $target.dropdown('getItem', listId).addClass('active');
                            });
                            return false;
                        }
                    });
                }

                Events.fire('get_lists', function(data) {
                    var list = data.list;
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
                        data: $.merge(list, [
                            {id: 'add_list', title: 'Создать список'}
                        ])
                    });
                });
            })();
        }
        t.trigger('clickPlus', e);
        return false;
    },
    onLoad: function(data) {
        var t = this;
        var dialogs = data.list;
        if (!dialogs.length) {
            t._isEnded = true;
        }
        t.model().id(t._pageId);
        t.model().list(t.model().list().concat(dialogs));

        for (var i in dialogs) {
            if (!dialogs.hasOwnProperty(i)) continue;
            var dialogModel = new DialogModel(dialogs[i]);
            var messageModel = new MessageModel(dialogs[i]);
            var userModel = new UserModel(dialogModel.user());

            if (dialogModel) {
                dialogCollection.add(dialogModel.id(), dialogModel);
            }
            if (userModel) {
                userCollection.add(userModel.id(), userModel);
            }
            if (dialogModel.messageId()) {
                messageCollection.add(dialogModel.messageId(), messageModel);
            }
        }
    },
    onRender: function() {
        var t = this;
        t.model().list([]);
        if (t.checkAtBottom()) {
            $(window).trigger('scroll');
        }
    },
    addDialog: function(dialogModel) {
        var t = this;
        if (!(dialogModel instanceof DialogModel)) throw new TypeError('Dialog is not correct');
        if ($.inArray(t._pageId, dialogModel.lists()) == -1) return false;

        var $el = t.el();
        var $dialog = $(t.tmpl()(t._templateItem, dialogModel));
        var $oldDialog = $el.find('[data-id=' + dialogModel.id() + ']');
        if ($oldDialog.length) {
            $oldDialog.remove();
        }
        $dialog.prependTo($el.find(t._itemsSelector));
        t.makeList($dialog);
        return $dialog;
    },
    readDialog: function(dialogId) {
        var t = this;
        var $el = t.el();
        var $dialog = $el.find('.dialog[data-id=' + dialogId + ']');
        var $dialogMessage = $dialog.find('.from-me');
        $dialog.removeClass('new');
        $dialogMessage.removeClass('new');
    }
});

var Messages = EndlessPage.extend({
    _template: MESSAGES,
    _modelClass: MessagesModel,
    _templateItem: MESSAGES_ITEM,
    _templateLoading: MESSAGES_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.messages',
    _service: 'get_messages',
    _pageLoaded: null,
    _isTop: true,

    _events: {
        'click: .button.send': 'clickSend',
        'click: .save-template': 'clickSaveTmpl',
        'hover: .message.new': 'hoverMessage',
        'keydown: textarea': 'keyDownTextarea'
    },

    clickSend: function(e) {
        var t = this;
        t.sendMessage();
    },
    keyDownTextarea: function(e) {
        var t = this;
        if ((e.ctrlKey || e.metaKey) && e.keyCode == KEY.ENTER) {
            t.sendMessage();
        }
    },
    clickSaveTmpl: function(e) {
        var t = this;
        var dialogId = t._pageId;
        var dialogModel = dialogCollection.get(dialogId);
        var listId = dialogModel.lists()[0];
        var box = new CreateTemplateBox(listId, t.el().find('textarea').val(), function() {
            t.updateAutocomplite();
        });
        box.show();
    },
    hoverMessage: function(e) {
        var t = this;
        t.trigger('hoverMessage', e);
    },

    renderTemplateLoading: function() {
        var t = this;
        var dialogId = t._pageId;
        var messageId = dialogCollection.get(dialogId).messageId();
        var userId = new UserModel(dialogCollection.get(dialogId).user()).id();
        t.model().user(userCollection.get(userId));
        t.model().viewer(userCollection.get(Configs.vkId));
        if (messageCollection.get(messageId)) {
            t.model().preloadList([messageCollection.get(messageId).data()]);
        }

        t._super.apply(this, Array.prototype.slice.call(arguments, 0));
        t.makeList(t.el().find(t._itemsSelector));
    },
    onShow: function() {
        var t = this;
        t.updateTopPadding();
        t.scrollBottom();
    },
    onLoad: function(data) {
        var t = this;
        var user = data.user;
        var viewer = data.viewer;
        var messages = data.list;
        if (!messages.length) {
            t._isEnded = true;
        }
        t.model().id(t._pageId);
        t.model().user(user);
        t.model().viewer(viewer);
        t.model().list(t.model().list().concat(messages));

        var userModel = new UserModel(user);
        userCollection.add(userModel.id(), userModel);

        for (var i in messages) {
            if (!messages.hasOwnProperty(i)) continue;
            if (!messages[i].isViewer) messages[i].user = userModel.data();
            var messageModel = new MessageModel(messages[i]);
            messageCollection.add(messageModel.id(), messageModel);
        }
    },
    onRender: function() {
        var t = this;
        t.onShow();
        t.makeTextarea(t.el().find('textarea:first'));
        if (t.checkAtTop()) {
            $(window).trigger('scroll');
        }
    },
    makeList: function($list) {
        var t = this;
        $list.find('.videos').imageComposition({width: 500, height: 240});
        $list.find('.photos').imageComposition({width: 500, height: 300});
        $list.find('.date').each(function() {
            var $date = $(this);
            var timestamp = $date.text();
            var currentDate = new Date();
            var date = new Date(timestamp * 1000);
            var m = date.getMonth() + 1;
            var y = date.getFullYear() + '';
            var d = date.getDate() + '';
            var h = date.getHours() + '';
            var min = date.getMinutes() + '';
            var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
            if (currentDate.getDate() != d) {
                text += ', ' + d + '.' + m + '.' + (y.substr(-2));
            }
            $date.html(text);
        });
    },
    makeTextarea: function($textarea) {
        var t = this;
        $textarea.placeholder();
        $textarea.autoResize();
        $textarea.inputMemory('message' + t._pageId);
        $textarea.focus();
        $textarea[0].scrollTop = $textarea[0].scrollHeight;
        t.updateAutocomplite();
    },
    updateAutocomplite: function() {
        var t = this;
        var $textarea = t.el().find('textarea:first');
        var dialogId = t._pageId;
        var dialogModel = dialogCollection.get(dialogId);
        var listId = dialogModel.lists()[0];
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
    updateTopPadding: function() {
        var t = this;
        if (!t.isVisible()) return;
        var $messages = t.el().find(t._itemsSelector);
        $messages.css('padding-top', $(window).height() - $messages.height() - 152);
    },
    scrollBottom: function() {
        var t = this;
        if (!t.isVisible()) return;
        $(window).scrollTop($(document).height());
    },
    sendMessage: function() {
        var t = this;
        var $el = t.el();
        var $textarea = $el.find('textarea');
        var text = $.trim($textarea.val());

        if (text) {
            $textarea.val('');
            var newMessageModel = new MessageModel({
                id: 'loading',
                isNew: true,
                isViewer: true,
                text: makeMsg(text),
                timestamp: Math.floor(new Date().getTime() / 1000),
                viewer: userCollection.get(Configs.vkId),
                dialogId: t._pageId
            });
            var $newMessage = t.addMessage(newMessageModel);
            $newMessage.addClass('loading');
            t.scrollBottom();
            $textarea.focus();
            Events.fire('send_message', t._pageId, text, function(messageId) {
                if (!messageId) {
                    $textarea.val(text);
                    $newMessage.remove();
                    return;
                }
                var $oldMessage = $el.find('[data-id=' + messageId + ']');
                if ($oldMessage.length) {
                    $newMessage.remove();
                } else {
                    $newMessage.removeClass('loading').attr('data-id', messageId);
                }
            });
        } else {
            $textarea.focus();
        }
    },
    addMessage: function(messageModel) {
        var t = this;
        if (!(messageModel instanceof MessageModel)) throw new TypeError('Message is not correct');
        if (messageModel.dialogId() != t._pageId) return false;

        var $el = t.el();
        var $message = $(t.tmpl()(t._templateItem, messageModel));
        var $oldMessage = $el.find('[data-id=' + messageModel.id() + ']');
        if ($oldMessage.length) {
            $oldMessage.remove();
        }
        $message.appendTo($el.find(t._itemsSelector));
        t.makeList($message);
        t.scrollBottom();
        return $message;
    },
    readMessage: function(messageId) {
        var t = this;
        var $el = t.el();
        var $message = $el.find('.message[data-id=' + messageId + ']');
        $message.removeClass('new');
    },
    onScroll: function() {
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
        var t = this;
        t.updateTopPadding();
    }
});

var RightColumn = Widget.extend({
    _template: RIGHT_COLUMN,
    _modelClass: ListsModel,
    _isDragging: false,
    _isFirstRun: true,

    _events: {
        'mousedown: .drag-wrap': 'mouseDownList',
        'click: .item': 'clickList'
    },

    run: function() {
        var t = this;
        var $el = t.el();

        if (t._isFirstRun) {
            $el.hide();
        }
        Events.fire('get_lists', function(data) {
            if (t._isFirstRun) {
                t._isFirstRun = false;
                $el.fadeIn(500);
            }
            var list = data.list;
            var isSetCommonList = false;
            var isSetSelectedList = false;
            for (var i in list) {
                if (!list.hasOwnProperty(i)) continue;
                list[i] = new ListModel(list[i]);
                listCollection.add(list[i].id(), list[i]);

                var currentList = t.model().list();
                for (var i2 in currentList) {
                    if (!currentList.hasOwnProperty(i2)) continue;
                    var currentItem = currentList[i2];
                    if (currentItem && currentItem.id() == list[i].id()) {
                        if (currentItem.isSelected()) {
                            list[i].isSelected(currentItem.isSelected());
                            isSetSelectedList = true;
                        }
                    }
                }
            }
            if (!isSetCommonList) {
                var commonListModel = new ListModel({
                    id: Configs.commonDialogsList,
                    title: 'Не в списке',
                    isSelected: !isSetSelectedList,
                    isDraggable: false
                });
                list.unshift(commonListModel);
                listCollection.add(commonListModel.id(), commonListModel);
            }
            t.model().counter(data.counter);
            t.model().list(list);
            t.renderTemplate();
        });
    },

    mouseDownList: function(e) {
        var t = this;
        var $placeholder = $(e.currentTarget);
        var $target = $placeholder.find('.item:first');
        var startY = 0;
        var timeout = setTimeout(function() {
            t._isDragging = true;
            startY = e.pageY;
            $target.addClass('drag');
            $placeholder.height($target.height());
        }, 200);
        $('body').addClass('no-select');

        $(window).on('mousemove.list', (function update(e) {
            if (t._isDragging) {
                var top = e.pageY - startY;
                var height = $placeholder.height();
                var position = intval((e.pageY - $placeholder.offset().top) / height);
                var $next = $placeholder.next('.drag-wrap');
                var $prev = $placeholder.prev('.drag-wrap');

                if (position > 0 && $next.length) {
                    $placeholder.before($next);
                    startY += height;
                } else if (position < 0 && $prev.length) {
                    $placeholder.after($prev);
                    startY -= height;
                }
                top = e.pageY - startY;
                $target.css({top: top});
            }

            return update;
        })(e));

        $(window).on('mouseup.list', function(e) {
            $(window).off('mousemove.list mouseup.list');
            $('body').removeClass('no-select');
            clearTimeout(timeout);

            if (!t._isDragging) return;
            $target.removeClass('drag').css({top: 0});
            setTimeout(function() {
                t._isDragging = false;
                t.saveOrder();
            }, 0);
        });
    },
    clickList: function(e) {
        var t = this;
        if (t._isDragging) return;
        var $list = $(e.currentTarget);
        var listId = $list.data('id');
        t.setList(listId, true);
    },

    saveOrder: function() {
        var t = this;
        var $el = t.el();
        var listIds = [];
        var list = [];
        $el.find('.item').each(function() {
            var listId = $(this).data('id');
            if (listId != Configs.commonDialogsList) {
                listIds.push(listId);
            }
            if (listCollection.get(listId)) {
                list.push(new ListModel(listCollection.get(listId).data()));
            }
        });
        t.model().list(list);
        Events.fire('set_list_order', listIds.join(','), function() {});
    },

    setList: function(listId, isTrigger) {
        var t = this;
        var list = t.model().list();
        var item;
        var setAsRead = false;
        for (var i in list) {
            if (!list.hasOwnProperty(i)) continue;
            item = list[i];
            if (item.id() == listId) {
                item.isSelected(true);
                if (!item.isRead()) {
                    setAsRead = true;
                    item.isRead(true);
                }
            } else {
                item.isSelected(false);
            }
        }
        t.renderTemplate();
        if (setAsRead) {
            Events.fire('set_list_as_read', listId, function() {});
        }
        if (isTrigger) t.trigger('setList', listId);
    },
    setDialog: function(dialogId, isTrigger) {
        var t = this;
        t.renderTemplate();
        if (isTrigger) t.trigger('setDialog', dialogId);
    },
    update: function() {
        var t = this;
        clearTimeout(t._timer);
        t._timer = setTimeout(function() {
            if (!t._isDragging) {
                t.run();
            }
        }, 200);
    }
});

var Tabs = Widget.extend({
    _template: TABS,
    _modelClass: TabsModel,
    _userId: null,

    _events: {
        'click: .tab.dialog': 'clickDialog',
        'click: .tab.list': 'clickList',
        'click: .icon': 'clickPlus'
    },

    clickDialog: function(e) {
        var t = this;
        //@todo: do well
        var $target = $(e.currentTarget);
        if (!$target.hasClass('.selected')) {
            t.trigger('clickDialog', e);
        }
    },
    clickList: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        if (!$target.hasClass('.selected')) {
            t.trigger('clickList', e);
        }
    },
    clickPlus: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var dialogTab = t.model().dialogTab();
        var dialogId = dialogTab.id();
        var dialogModel = dialogCollection.get(dialogId);
        if (!$target.data('dropdown')) {
            (function updateDropdown() {

                function onCreate() {
                    var lists = dialogModel.lists();

                    $.each(lists, function(i, listId) {
                        $target.dropdown('getItem', listId).addClass('active');
                    });
                }

                Events.fire('get_lists', function(data) {
                    var list = data.list;
                    $target.dropdown({
                        isShow: true,
                        position: 'left',
                        width: 'auto',
                        type: 'checkbox',
                        addClass: 'ui-dropdown-add-to-list-tabs',
                        oncreate: onCreate,
                        onupdate: onCreate,
                        onopen: function() {
                            //@todo: do well
                            $target.css('display', 'inline');
                            $target.parent().find('.offline, .online').hide();
                            $(this).dropdown('refreshPosition');

                            $target.addClass('active');
                        },
                        onclose: function() {
                            $target.removeAttr('style');
                            $target.parent().find('.offline, .online').removeAttr('style');

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
                        data: $.merge(list, [
                            {id: 'add_list', title: 'Создать список'}
                        ])
                    });
                });
            })();
        }
        t.trigger('clickPlus', e);
        return false;
    },

    setList: function(listId) {
        var t = this;
        var listModel = listCollection.get(listId) || new ListModel({
            id: Configs.commonDialogsList,
            title: 'Не в списке'
        });
        var label = listModel.title();
        var listTab = new TabModel({id: listId, label: label, isSelected: true});
        var dialogTab = t.model().dialogTab();
        if (dialogTab) {
            dialogTab.isSelected(false);
        }
        t.model().listTab(listTab);
        t.renderTemplate();
    },

    setDialog: function(dialogId) {
        var t = this;
        var dialogModel = dialogCollection.get(dialogId) || new DialogModel();
        var userModel = new UserModel(dialogModel.user());
        t._userId = userModel.id();
        var label = userModel.name();
        var isOnline = userModel.isOnline();
        var listTab = t.model().listTab();
        var dialogTab = new TabModel({
            id: dialogId,
            label: label,
            isSelected: true,
            isOnline: isOnline,
            isOnList: false
        });
        if (listTab) {
            listTab.isSelected(false);
        }
        t.model().dialogTab(dialogTab);
        t.renderTemplate();
    },

    setOnline: function(userId) {
        var t = this;
        //@todo привести к нормальному виду
        if (t._userId == userId) {
            t.el().find('.tab.dialog > .icon.offline').removeClass('offline').addClass('online');
        }
    },
    setOffline: function(userId) {
        var t = this;
        //@todo привести к нормальному виду
        if (t._userId == userId) {
            t.el().find('.tab.dialog > .icon.online').removeClass('online').addClass('offline');
        }
    }
});

/**
 * Initialization
 */
$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    if (!Configs.vkId) {
        location.href = '/login/?' + encodeURIComponent('im/');
        return;
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }

    var main = new Main({
        selector: '#main'
    });
    $(window).on('scroll resize', function() {
        main.trigger('scroll');
    });
});

function CreateTemplateBox(listId, text, onSuccess) {
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
            Events.fire('get_lists', function(data) {
                var list = data.list;
                var listsIds = [];
                var clearLists = [];
                var currentList = {};
                $.each(list, function(i, listItem) {
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
                        if ($.isFunction(onSuccess)) onSuccess();
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