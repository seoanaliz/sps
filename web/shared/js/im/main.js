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

        Events.fire('get_viewer', function(data) {
            var viewer = new UserModel(data);
            userCollection.add(viewer.data('id'), viewer);

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
            t._rightColumn.on('changeDialog', function(dialogId) {
                t._leftColumn.showDialog(dialogId);
            });
            t._rightColumn.on('setList', function(listId) {
                t._leftColumn.showList(listId);
            });

            (function poll(ts) {
                return;
                var timeout = 15;
                $.ajax({
                    url: 'http://im.' + Configs.hostName + '/int/controls/watchDog/',
                    dataType: 'jsonp',
                    data: {
                        userId: Configs.vkId,
                        timeout: timeout,
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
            case 'outMessage': {
                (function() {
                    var isViewer = (event.type == 'outMessage');
                    var message = Cleaner.longPollMessage(event.content, isViewer);
                    var dialog = Cleaner.longPollDialog(event.content, isViewer);
                    t._leftColumn.addMessage(message);

                    if (!message.isViewer) {
                        t._rightColumn.addMessage(message);
                    }
                })();
                break;
            }
            case 'read': {
                (function() {
                    var message = Cleaner.longPollRead(event.content);
                    t._leftColumn.readMessage(message);
                })();
                break;
            }
            case 'online':
            case 'offline': {
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

        t.showList(Configs.commonDialogsList, true);
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

    setOnline: function(userId) {},
    setOffline: function(userId) {},
    addMessage: function(message) {},
    readMessage: function() {}
});

var Page = Widget.extend({
    _isVisible: false,
    _templateLoading: '',
    _pageId: null,
    _cache: null,
    _service: 'get_dialogs',
    _isBlock: false,
    _isCache: true,
    _scroll: null,

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
                t.model().data(data);
                t.renderTemplate();
                t.unlock();
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
        $(window).scrollTop(t._scroll);
        return this;
    },
    hide: function() {
        var t = this;
        t._isVisible = false;
        t._scroll = $(window).scrollTop();
        t.el().hide();
        return this;
    },
    isLock: function() {
        return !!this._isBlock;
    },
    lock: function() {
        this._isBlock = true;
    },
    unlock: function() {
        this._isBlock = false;
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

    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t._pageId != pageId)) {
            t._pageLoaded = 0;
            t._preloadData = {};
        }
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    getData: function() {
        var t = this;
        if (!t.isLock()) {
            t.lock();
            var limit = t._itemsLimit;
            var offset = t._pageLoaded * limit;
            var nextPage = t._pageLoaded + 1;

            t.onShow();
            Events.fire(t._service, t._pageId, offset, limit, function(data) {
                t._pageLoaded = nextPage;

                t.onLoad(data);
                t.model().data(data);
                t.renderTemplate();
                t.makeList(t.el().find(t._itemsSelector));
                t.onRender();
                t.unlock();

                if (t._isPreload) {
                    t.preloadData(nextPage + 1);
                }
            });
        }
    },
    preloadData: function(pageNumber) {
        var t = this;
        var limit = t._itemsLimit;
        var offset = pageNumber * limit;

        if (!t._preloadData) t._preloadData = {};
        if (!t._preloadData[pageNumber]) {
            Events.fire(t._service, t._pageId, offset, limit, function(data) {
                t._preloadData[pageNumber] = data;
            });
        }
    },
    onShow: function() {},
    onLoad: function(data) {},
    onRender: function() {},
    makeList: function($list) {},
    showMore: function() {
        var t = this;
        var nextPage = t._pageLoaded + 1;
        var limit = t._itemsLimit;
        var offset = nextPage * limit;

        if (!t._preloadData) t._preloadData = {};
        if (t._preloadData[nextPage]) {
            setData(t._preloadData[nextPage]);
        } else {
            if (!t.isLock()) {
                t.lock();
                Events.fire(t._service, t._pageId, offset, limit, function(data) {
                    setData(data);
                });
            }
        }

        function setData(data) {
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
            t.unlock();
            t._pageLoaded = nextPage;
            if (t._isPreload) {
                t.preloadData(nextPage + 1);
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
        $list.find('.date').easydate(Configs.easyDateParams);
    },
    clickDialog: function(e) {
        var t = this;
        t.trigger('clickDialog', e);
    },
    clickPlus: function(e) {
        var t = this;
        t.trigger('clickPlus', e);
        return false;
    },
    onLoad: function(data) {
        var dialogs = data.list;
        for (var i in dialogs) {
            if (!dialogs.hasOwnProperty(i)) continue;
            var dialogModel = new DialogModel(dialogs[i]);
            dialogCollection.add(dialogModel.data('id'), dialogModel);
        }
    },
    addDialog: function(dialog) {
        if (!(dialog instanceof DialogModel)) throw new TypeError('Dialog is not correct');

        var t = this;
        var $el = t.el();
        var $dialog = $(t.tmpl()(t._templateItem, dialog));
        $dialog.prependTo($el.find(t._itemsSelector));
        t.makeList($dialog);
        return $dialog;
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
        var listId = t._pageId;
        var box = new CreateTemplateBox(listId, t.el().find('textarea').val());
        box.show();
    },
    hoverMessage: function(e) {},

    onShow: function() {
        var t = this;
        t.updateTopPadding();
        t.scrollBottom();
    },
    onLoad: function(data) {
        var t = this;
        var user = data.user;
        var messages = data.list;

        var userModel = new UserModel(user);
        userCollection.add(userModel.data('id'), userModel);

        for (var i in messages) {
            if (!messages.hasOwnProperty(i)) continue;
            if (messages[i].user && messages[i].user.id != Configs.vkId) {
                messages[i].user = userModel.data();
            }
            var messageModel = new MessageModel(messages[i]);
            messageCollection.add(messageModel.data('id'), messageModel);
        }
    },
    onRender: function() {
        var t = this;
        t.onShow();
        t.makeTextarea(t.el().find('textarea:first'));
    },
    makeList: function($list) {
        var t = this;
        $list.find('.videos').imageComposition({width: 500, height: 240});
        $list.find('.photos').imageComposition({width: 500, height: 300});
        $list.find('.date').easydate(Configs.easyDateParams);
    },
    makeTextarea: function($textarea) {
        var t = this;
        $textarea.placeholder();
        $textarea.autoResize();
        $textarea.inputMemory('message' + t._pageId);
        $textarea.focus();
        $textarea[0].scrollTop = $textarea[0].scrollHeight;
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
            var $newMessage = t.addMessage({
                id: 'loading',
                isNew: true,
                isViewer: true,
                text: makeMsg(text),
                timestamp: Math.floor(new Date().getTime() / 1000),
                user: Configs.viewer
            });
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
    addMessage: function(message) {
        if (!(message instanceof MessageModel)) throw new TypeError('Message is not correct');
        var t = this;
        var $el = t.el();
        var $message = $(t.tmpl()(t._templateItem, message));
        $message.appendTo($el.find(t._itemsSelector));
        t.makeList($message);
        return $message;
    }
});

var RightColumn = Widget.extend({
    _template: RIGHT_COLUMN,
    _modelClass: ListsModel,
    _isEditMode: true,
    _isDragging: false,

    _events: {
        'mousedown: .drag-wrap': 'mouseDownList',
        'click: .item': 'clickList'
    },

    run: function() {
        var t = this;
        var $el = t.el();
        Events.fire('get_lists', function(data) {
            var list = data.list;
            var isSetCommonList = false;
            for (var i in list) {
                if (!list.hasOwnProperty(i)) continue;
                var listModel = new ListModel(list[i]);
                listCollection.add(listModel.data('id'), listModel);
            }
            if (!isSetCommonList) {
                var commonListModel = new ListModel({
                    id: Configs.commonDialogsList,
                    title: 'Не в списке'
                });
                list.unshift(commonListModel);
                listCollection.add(commonListModel.data('id'), commonListModel);
            }
            t.model().data(data);
            t.renderTemplate();
        });
    },

    mouseDownList: function(e) {
        var t = this;
    },
    clickList: function(e) {
        var t = this;
        var $list = $(e.currentTarget);
        var listId = $list.data('id');
        t.setList(listId, true);
    },

    setList: function(listId, isTrigger) {
        var t = this;
        t.renderTemplate();
        if (isTrigger) t.trigger('setList', listId);
    },
    setDialog: function(dialogId, isTrigger) {
        var t = this;
        t.renderTemplate();
        if (isTrigger) t.trigger('setDialog', dialogId);
    },
    update: function() {}
});

var Tabs = Widget.extend({
    _template: TABS,
    _modelClass: TabsModel,

    _events: {
        'click: .tab.dialog': 'clickDialog',
        'click: .tab.list': 'clickList'
    },

    init: function() {
        var t = this;
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },

    clickDialog: function(e) {
        var t = this;
        t.trigger('clickDialog', e);
    },

    clickList: function(e) {
        var t = this;
        t.trigger('clickList', e);
    },

    setList: function(listId) {
        var t = this;
        var listModel = listCollection.get(listId) || new ListModel({
            id: Configs.commonDialogsList,
            title: 'Не в списке'
        });
        var label = listModel.data('title');
        var tabListModel = new TabModel({id: listId, label: label, isSelected: true});
        var tabDialogModelData = t.model().data('dialog');
        if (tabDialogModelData) tabDialogModelData['isSelected'] = false;
        t.model().data('list', tabListModel);
        t.renderTemplate();
    },

    setDialog: function(dialogId) {
        var t = this;
        var dialogModel = dialogCollection.get(dialogId) || new DialogModel();
        var label = dialogModel.data('user').data()['name'];
        var tabListModelData = t.model().data('list');
        var tabDialogModel = new TabModel({id: dialogId, label: label, isSelected: true});
        if (tabListModelData) tabListModelData['isSelected'] = false;
        t.model().data('dialog', tabDialogModel);
        t.renderTemplate();
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
        location.replace('/login/?' + btoa('im'));
        return;
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }

    if (!Configs.token) {
        location.replace('/im/login/? ' + btoa('im'));
        return;
    }

    var main = new Main({
        selector: '#main'
    });
    $(window).on('scroll resize', function() {
        main.trigger('scroll');
    });
});

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