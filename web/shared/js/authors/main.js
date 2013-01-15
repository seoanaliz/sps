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
var metricksCollection = new MetricsCollection();
var authorCollection  = new AuthorCollection();
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
//            if (!data) {
//                location.href = '/im/login/?' + encodeURIComponent('im/');
//                return;
//            }

//            var viewer = new UserModel(data);
//            userCollection.add(viewer.id(), viewer);

            t.el().fadeIn(500);
            t.renderTemplate();

            t._leftColumn = new LeftColumn({
                selector: t._leftColumnSelector
            });
            t._rightColumn = new RightColumn({
                selector: t._rightColumnSelector,
                model: new ListsModel()
            });
//            t.on('scroll', function(e) {
//                t._leftColumn.onScroll(e);
//            });

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

//            (function poll(ts) {
//                $.ajax({
//                    url: 'http://im.' + Configs.hostName + '/int/controls/watchDog/',
//                    dataType: 'jsonp',
//                    data: {
//                        userId: Configs.vkId,
//                        timeout: 15,
//                        ts: ts
//                    },
//                    success: function(data) {
//                        poll(data.response.ts);
//                        $.each(data.response.events, function(i, event) {
//                            t.fireEvent(event);
//                        });
//                    }
//                });
//            })();

//            t._leftColumn.showList(Configs.commonDialogsList);
        });
    },

    fireEvent: function(event) {
        var t = this;
        if (!event || !event.type) {
            console.log(['Bad Event: ', event]);
            return;
        }
//
//        switch (event.type) {
//            case 'inMessage':
//            case 'outMessage':
//                (function() {
//                    var isViewer = (event.type == 'outMessage');
//                    var message = Cleaner.longPollMessage(event.content, isViewer);
//                    var dialog = Cleaner.longPollDialog(event.content, isViewer);
//                    t._leftColumn.addMessage(new MessageModel(message));
//                    t._leftColumn.addDialog(new DialogModel(dialog));
//
//                    if (!message.isViewer) {
//                        t._rightColumn.update();
//                    }
//                })();
//                break;
//            case 'read':
//                (function() {
//                    var message = Cleaner.longPollRead(event.content);
//                    t._leftColumn.readMessage(message.id);
//                    t._leftColumn.readDialog(message.dialogId);
//                })();
//                break;
//            case 'online':
//            case 'offline':
//                (function() {
//                    var online = Cleaner.longPollOnline(event.content);
//                    if (online.isOnline) {
//                        t._leftColumn.setOnline(online.userId);
//                    } else {
//                        t._leftColumn.setOffline(online.userId);
//                    }
//                })();
//                break;
//        }
    }
});
//
var LeftColumn = Widget.extend({
    _template: LEFT_COLUMN,
    _authors: null,
    _dialogsSelector: '#list-dialogs',
    _authors: null,
    _messagesSelector: '#list-messages',
    _tabs: null,
    _tabsSelector: '.header',

    run: function() {
        var t = this;
        t.renderTemplate();
//        t._tabs = new Tabs({
//            selector: t._tabsSelector,
//            model: new TabsModel()
//        });
//        t._messages = new Messages({
//            selector: t._messagesSelector,
//            model: new MessagesModel()
//        });
        t._authors = new Authors({
            selector: t._dialogsSelector,
            model: new AuthorsModel()
        });

//        t._authors.on('clickDialog', function(e) {
//            var $author = $(e.currentTarget);
//            var $author = $dialog.data('id');
//            t.showDialog(dialogId, true);
//        });

//        t._dialogs.on('addList', function() {
//            t.trigger('addList');
//        });

//        t._messages.on('hoverMessage', function(e) {
//            var $message = $(e.currentTarget);
//            var messageId = $message.data('id');
//            var dialogId = t._messages.pageId();
//            if ($message.hasClass('viewer')) return;
//            t._messages.readMessage(messageId);
//            t._dialogs.readDialog(dialogId);
//            Events.fire('message_mark_as_read', messageId, dialogId, function() {
//                t.trigger('markAsRead');
//            });
//        });

//        t._tabs.on('clickDialog', function(e) {
//            var $tab = $(e.currentTarget);
//            var dialogId = $tab.data('id');
//            t.showDialog(dialogId, true);
//        });

//        t._tabs.on('clickList', function(e) {
//            var $tab = $(e.currentTarget);
//            var listId = $tab.data('id');
//            t.showList(listId, true);
//        });

//        t._tabs.on('addList', function() {
//            t.trigger('addList');
//        });

//        t._tabs.on('clickFilter', function() {
//            t._dialogs.toggleFilter();
//        });
    },
    onScroll: function(e) {
        var t = this;
        t._messages.onScroll(e);
        t._dialogs.onScroll(e);
    },

    showList: function(listId, isTrigger) {
        var t = this;
            t._authors.show();
//        if (!t._dialogs.isVisible()) {
//            t._messages.hide();
//            t._dialogs.show();
//        }
        t._authors.changePage(listId);
       // t._tabs.setList(listId);
        if (isTrigger) t.trigger('changeList', listId);
    },
//
//    showDialog: function(dialogId, isTrigger) {
//        var t = this;
//        if (!t._messages.isVisible()) {
//            t._dialogs.hide();
//            t._messages.show();
//        }
//        t._messages.changePage(dialogId);
//        t._tabs.setDialog(dialogId);
//        if (isTrigger) t.trigger('changeDialog', dialogId);
//    },
//
//    setOnline: function(userId) {
//        var t = this;
//        t._tabs.setOnline(userId);
//    },
//    setOffline: function(userId) {
//        var t = this;
//        t._tabs.setOffline(userId);
//    },
//    addMessage: function(messageModel) {
//        var t = this;
//        t._messages.addMessage(messageModel);
//    },
//    addDialog: function(dialog) {
//        var t = this;
//        t._dialogs.addDialog(dialog);
//    },
//    readMessage: function(messageId) {
//        var t = this;
//        t._messages.readMessage(messageId);
//    },
//    readDialog: function(dialogId) {
//        var t = this;
//        t._dialogs.readDialog(dialogId);
//    }
//});
});
    //page

var Page = Widget.extend({
    _isVisible: false,
    _templateLoading: '',
    _pageId: null,
    _cache: null,
    _service: 'get_authors',
    _isLock: false,
    _isCache: true,
    _scroll: null,
    _isBottom: false,

    run: function() {
        var t = this;
        if (t.pageId()) {
            t.changePage(t.pageId(), true);
        }
    },
    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t.pageId() != pageId)) {
            t.pageId(pageId);
            t.renderTemplateLoading();
            t.scrollTop();
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
    scrollTop: function() {
        $(window).scrollTop(0);
    },
    scrollBottom: function() {
        $(window).scrollTop($(document).height() - $(window).height());
    },
    show: function() {
        var t = this;
        t.visible(true);
        t.el().show();
        if (t._isBottom) {
            t.scrollBottom();
        } else {
            $(window).scrollTop(t._scroll);
        }
        return this;
    },
    hide: function() {
        var t = this;
        t.visible(false);
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
    },
    isVisible: function() {
        return this.visible();
    },
    visible: function(visible) {
        if (arguments.length) {
            this._isVisible = visible;
            return this;
        } else {
            return !!this._isVisible;
        }
    },
    pageId: function(pageId) {
        if (arguments.length) {
            this._pageId = pageId;
            return this;
        } else {
            return this._pageId;
        }
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
        if (force || (t._isCache && t.pageId() != pageId)) {
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
        var pageId = t.pageId();

        t.onShow();
        t.lock();
        Events.fire(t._service, pageId, offset, limit, function(data) {
            t.unlock();

            if (pageId == t.pageId()) {
                t.onLoad(data);

                t.renderTemplate();
                t.makeList(t.el().find(t._itemsSelector));
                t.onRender();
                t.preloadData(1);
            }
        });
    },
    preloadData: function(pageNumber) {
        var t = this;
        if (!t._isPreload || t._isEnded) {
            return;
        }
        var limit = t._itemsLimit;
        var offset = pageNumber * limit;
        var pageId = t.pageId();
        var preloadData = t._preloadData || {};

        if (!preloadData[pageNumber] && !t.isLock()) {
            Events.fire(t._service, pageId, offset, limit, function(data) {
                if (pageId == t.pageId()) {
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
        var pageId = t.pageId();
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
            if (pageId == t.pageId()) {
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

var Authors = EndlessPage.extend({
    _template: AUTHORS,
    _modelClass: AuthorsModel,
    _templateItem: AUTHORS_ITEM,
    _templateLoading: AUTHORS_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.dialogs',
    _service: 'get_authors',
    _pageLoaded: null,
    _pageId: Configs.commonDialogsList,
    _isFiltered: false,

    changePage: function() {
        var t = this;
        t._isFiltered = false;
        t._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    makeList: function($list) {
        $list.find('.date').each(function() {
            var $date = $(this);
            var timestamp = intval($date.text());
            $date.html(makeDate(timestamp));
        });
    },
//    clickPlus: function(e) {
//        var t = this;
//        var $target = $(e.currentTarget);
//        var $dialog = $target.closest('.dialog');
//        var dialogId = $dialog.data('id');
//        var dialogModel = dialogCollection.get(dialogId);
//        if (!$target.data('dropdown')) {
//            (function updateDropdown() {
//
//                function onCreate() {
//                    $.each(dialogModel.lists(), function(i, listId) {
//                        $target.dropdown('getItem', listId).addClass('active');
//                    });
//                }
//
//                Events.fire('get_lists', function(data) {
//                    var list = data.list;
//                    $target.dropdown({
//                        isShow: true,
//                        position: 'right',
//                        width: 'auto',
//                        type: 'checkbox',
//                        addClass: 'ui-dropdown-add-to-list',
//                        oncreate: onCreate,
//                        onupdate: onCreate,
//                        onopen: function() {
//                            $target.addClass('active');
//                        },
//                        onclose: function() {
//                            $target.removeClass('active');
//                        },
//                        onchange: function(item) {
//                            $(this).dropdown('open');
//
//                            var $menu = $(this).dropdown('getMenu');
//                            var $selectedItems = $menu.find('.ui-dropdown-menu-item.active');
//                            if ($selectedItems.length) {
//                                $target.addClass('select').removeClass('plus');
//                            } else {
//                                $target.addClass('plus').removeClass('select');
//                            }
//                        },
//                        onselect: function(item) {
//                            if (item.id == 'add_list') {
//                                var $item = $(this).dropdown('getItem', 'add_list');
//                                var $menu = $(this).dropdown('getMenu');
//                                var $input = $menu.find('input');
//                                $item.removeClass('active');
//                                if ($input.length) {
//                                    $input.focus();
//                                } else {
//                                    $item.before('<div class="wrap"><input type="text" placeholder="Название списка..." /></div>');
//                                    $input = $menu.find('input');
//                                    $input.focus();
//                                    $input.keydown(function(e) {
//                                        if (e.keyCode == KEY.ENTER) {
//                                            Events.fire('add_list', $input.val(), function() {
//                                                updateDropdown();
//                                                t.trigger('addList');
//                                            });
//                                        }
//                                    });
//                                    $(this).dropdown('refreshPosition');
//                                }
//                            } else {
//                                Events.fire('add_to_list', dialogId, item.id, function() {
//                                    var index = $.inArray(item.id, dialogModel.lists());
//                                    if (index == -1) {
//                                        dialogModel.lists().push(item.id);
//                                    }
//                                });
//                            }
//                        },
//                        onunselect: function(item) {
//                            Events.fire('remove_from_list', dialogId, item.id, function() {
//                                var index = $.inArray(item.id, dialogModel.lists());
//                                if (index != -1) {
//                                    dialogModel.lists().splice(index, 1);
//                                }
//                            });
//                        },
//                        data: $.merge(list, [
//                            {id: 'add_list', title: 'Создать список'}
//                        ])
//                    });
//                });
//            })();
//        }
//        t.trigger('clickPlus', e);
//        return false;
//    },
    onLoad: function(data) {
        var t = this;
        var authors = data.list;
        if (!authors.length) {
            t._isEnded = true;
        }
        t.model().id(t.pageId());
        t.model().list(t.model().list().concat(authors));

        for (var i in authors) {
            if (!authors.hasOwnProperty(i)) continue;
            var authorModel   = new AuthorModel(authors[i]);
            var metricksModel = new MetricsModel( authors[i].metricks1 );
            var userModel     = new UserModel(authors[i].user);

            if (authorModel) {
                authorCollection.add(authorModel.id(), authorModel);
            }
            if (userModel) {
                userCollection.add(userModel.id(), userModel);
            }

            if (authorModel.metrick1()) {
                metricksCollection.add(authorModel.metrick1(), metricksModel);
            }
        }
    },
    onRender: function() {
        var t = this;
        if (t.checkAtBottom()) {
            $(window).trigger('scroll');
        }
    }
//    addDialog: function(dialogModel) {
//        var t = this;
//        var isCommonList = (t.pageId() == Configs.commonDialogsList);
//        if (!(dialogModel instanceof DialogModel)) throw new TypeError('Dialog is not correct');
//        if ($.inArray(t.pageId(), dialogModel.lists()) == -1) {
//            if (!isCommonList) return false;
//            else if (dialogModel.lists().length) return false;
//        }
//
//        var $el = t.el();
//        var $dialog = $(t.tmpl()(t._templateItem, dialogModel));
//        var $oldDialog = $el.find('[data-id=' + dialogModel.id() + ']');
//        if ($oldDialog.length) {
//            $oldDialog.remove();
//        }
//        $dialog.prependTo($el.find(t._itemsSelector));
//        t.makeList($dialog);
//        return $dialog;
//    },
//    readDialog: function(dialogId) {
//        var t = this;
//        var $el = t.el();
//        var $dialog = $el.find('.dialog[data-id=' + dialogId + ']');
//        var $dialogMessage = $dialog.find('.from-me');
//        $dialog.removeClass('new');
//        $dialogMessage.removeClass('new');
//    },
//    toggleFilter: function() {
//        var t = this;
//        if (t._isFiltered) {
//            t.changePage(t.pageId().substr('unread'.length));
//        } else {
//            t._isFiltered = true;
//            t.pageId('unread' + t.pageId());
//            t.renderTemplateLoading();
//            t.lock();
//            Events.fire(t._service, {listId: t.pageId().substr('unread'.length), offset: 0, limit: 40, filter: true}, function(data) {
//                t.model().list([]);
//                t.onLoad(data);
//                t.renderTemplate();
//                t.makeList(t.el().find(t._itemsSelector));
//                t.onRender();
//            });
//        }
//    }
});

var RightColumn = Widget.extend({
    _template: RIGHT_COLUMN,
    _modelClass: ListsModel,
    _isDragging: false,
    _isFirstRun: true,

    _events: {
        'mousedown: .drag-wrap': 'mouseDownList',
        'click: .item': 'clickList',
        'click: .icon.delete': 'clickIconDelete',
        'click: .icon.edit': 'clickIconEdit',
        'click: input': 'clickInput'
    },

    run: function() {
        var t = this;
        var $el = t.el();

        if (t._isFirstRun) {
            $el.hide();
        }
        Events.fire('get_publics', function(data) {
            if (t._isFirstRun) {
                t._isFirstRun = false;
                $el.fadeIn(500);
            }
            var list = data.list;
            var commonList = data.commonList;
            var isSetCommonList = true;
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
                    counter: commonList.counter,
                    isSelected: !isSetSelectedList,
                    isDraggable: false,
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
                var top;
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

        $(window).on('mouseup.list', function() {
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
//        if (t._isDragging) return;
        var $list = $(e.currentTarget);
        var listId = $list.data('id');
        t.setList(listId, true);
    },
    clickIconDelete: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $list = $target.closest('.item');
        var listId = $list.data('id');
        var box = new Box({
            id: 'deleteList' + listId,
            title: 'Удаление',
            html: 'Вы уверены, что хотите удалить список?',
            buttons: [
                {label: 'Удалить', onclick: deleteList},
                {label: 'Отмена', isWhite: true}
            ]
        }).show();

        function deleteList() {
            this.hide();
            $list.closest('.drag-wrap').slideUp(200);
            Events.fire('remove_list', listId, function() {
                t.update();
            });
        }
        return false;
    },
    clickIconEdit: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $list = $target.closest('.item');
        var listId = $list.data('id');
        var $text = $list.find('.text');
        var input = $('<input type="text" />').width($text.width() + 20).val($text.text());

        $text.replaceWith(input);
        input.focus().on('keyup.editList', saveList);

        function saveList(e) {
            var listName = $.trim(input.val());
            if (e.keyCode == KEY.ENTER && listName) {
                input.replaceWith($text).off('keyup.editList');
                $text.text(input.val());
                Events.fire('update_list', listId, listName, function() {
                    t.update();
                });
            }
        }
        return false;
    },
    clickInput: function() {
        return false;
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
            } else {
                item.isSelected(false);
            }
        }
        var scroll_lock = $('#right-column .scroll-like-mac').scrollTop();
        t.renderTemplate();
        $('#right-column .scroll-like-mac').scrollTop(scroll_lock);
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

/**
 * Initialization
 */
$(document).ready(function() {
    var $window = $(window);
    var $body = $('body');
    var $main = $('#main');
    var $scrollFix = $('#scroll-fix');

    if (!$main.length) {
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
    $window.on('scroll resize', function() {
        main.trigger('scroll');
    });
});

function CreateTemplateBox(listId, text, isFocused) {
    var box = new Box({
        title: 'Готовые ответы',
        html: tmpl(BOX_LOADING, {height: 100}),
        width: 600,
        onshow: function() {
            Events.fire('get_lists', function(data) {
                var list = data.list;
                var listsIds = [];
                var clearLists = [];
                var currentList = {};
                var additionFormInited = false;

                $.each(list, function(i, listItem) {
                    if (listItem.id) {
                        clearLists.push(listItem);
                    }
                    if (listId == listItem.id) {
                        currentList = listItem;
                    }
                });

                box.setHTML(tmpl(SAVE_TEMPLATE_BOX, {text: text}));

                var $input = box.$el.find('.lists');
                var $textarea = box.$el.find('.template-text');
                var templateText = $textarea.val();
                var $templateList = box.$el.find('.template-list');
                var $addTemplateBtn = box.$el.find('.save-template');
                var $openedAdditionForm = box.$el.find('.add-template-opened');
                var $closedAdditionForm = box.$el.find('.add-template-closed');
                var $openAdditionFormTrigger = $closedAdditionForm.find('input');
                var $closeAdditionFormTrigger = $openedAdditionForm.find('.button.cancel');

                $openAdditionFormTrigger.focus(openAdditionForm);
                $closeAdditionFormTrigger.click(closeAdditionForm);
                $addTemplateBtn.click(addTemplate);

                $textarea.keydown(function(e) {
                    if (e.keyCode == KEY.ENTER && (e.ctrlKey || e.metaKey)) {
                        addTemplate();
                    }
                });

                $templateList.delegate('.icon.delete', 'click', function() {
                    var $target = $(this);
                    var $message = $target.closest('.message');
                    var messageId = $message.data('id');
                    var deleteBox = new Box({
                        id: 'templateDeleteBox' + messageId,
                        title: 'Удаление',
                        html: 'Вы уверены, что хотите удалить шаблон?',
                        buttons: [
                            {label: 'Удалить', onclick: function() {
                                deleteTemplate.call(this, messageId);
                            }},
                            {label: 'Отмена', isWhite: true}
                        ]
                    }).show();
                });

                $templateList.delegate('.icon.edit', 'click', function() {
                    var $target = $(this);
                    var $message = $target.closest('.message');
                    var messageId = $message.data('id');
                    var $text = $message.find('.content > .text');
                    var $textarea = $message.find('.content textarea');
                    var $actions = $message.find('.content > .actions');

                    if (!$text.data('textarea')) {
                        $text.data('textarea', true);
                        $textarea.on('keydown', function(e) {
                            if (e.keyCode == KEY.ENTER && (e.ctrlKey || e.metaKey)) {
                                saveTemplate();
                            }
                        });
                        $actions.find('.button.save-template').click(function(e) {
                            saveTemplate();
                        });
                        $actions.find('.button.cancel').click(function(e) {
                            editModeOff();
                        });
                        $text.after($textarea);
                        $textarea.autoResize().wrap('<div class="input-wrap" />');
                    }

                    $templateList.delegate('.tag-plus', 'click', function() {
                        var tags = getTags($message);
                        $target = $(this);
                        $target.dropdown({
                            isShow: true,
                            addClass: 'ui-dropdown-add-to-list-tabs',
                            data: $.grep(clearLists, function(u) {
                                var isFound = false;
                                $.each(tags, function(i, tag) {
                                    if (tag == u.id) {
                                        isFound = true;
                                        return false;
                                    }
                                });
                                return !isFound;
                            }),
                            width: 200,
                            onchange: function(item) {
                                $target.before($(tmpl(TEMPLATE_LIST_ITEM_LISTS, item)));
                            }
                        });
                    });

                    editModeOn();

                    function editModeOn() {
                        $message.addClass('edit-mode');
                        $textarea.val($text[0].innerText).focus().selectRange($text[0].innerText.length, $text[0].innerText.length);
                    }
                    function editModeOff() {
                        $message.removeClass('edit-mode');
                    }
                    function saveTemplate() {
                        if (!$textarea.val()) {
                            $textarea.focus();
                            return;
                        }
                        editModeOff();
                        $text.html(makeMsg($textarea.val())).show();
                        Events.fire('edit_template', messageId, $textarea.val(), getTags($message).join(','), function() {});
                    }
                });

                $templateList.delegate('.tag > .delete', 'click', function() {
                    var $target = $(this);
                    var $message = $target.closest('.message');
                    var $tag = $target.closest('.tag');
                    $tag.remove();
                    var tags = getTags($message);
                    var messageId = $message.data('id');
                    var $text = $message.find('.content > .text');
                    Events.fire('edit_template', messageId, $text[0].innerText, tags.join(','), function() {});
                });


                function getTags($message) {
                    var $tags = $message.find('.title > .tag');
                    var tags = [];
                    $.each($tags, function() {
                        var $tag = $(this);
                        if ($tag.data('id')) {
                            tags.push($tag.data('id'));
                        }
                    });
                    return tags;
                }

                function addTemplate() {
                    var text = $.trim($textarea.val());
                    if (!text) {
                        $textarea.focus();
                        return;
                    }
                    $textarea.val('').focus();
                    $templateList.prepend(tmpl(TEMPLATE_LIST_ITEM, {
                        id: 'loading',
                        text: text,
                        timestamp: '12:21',
                        user: userCollection.get(Configs.vkId).data(),
                        lists: []
                    }));
                    box.refreshTop();

                    Events.fire('add_template', text, listsIds.join(','), function() {
                        updateTemplateList();
                    });
                }

                function deleteTemplate(templateId) {
                    var box = this;
                    box.hide();
                    Events.fire('delete_template', templateId, function() {
                        $templateList.find('.message[data-id=' + templateId + ']').slideUp(200);
                    });
                }

                function openAdditionForm(e) {
                    $openedAdditionForm.show();
                    $closedAdditionForm.hide();
                    $textarea.focus();
                    $textarea.selectRange(templateText.length, templateText.length);
                    box.refreshTop();

                    if (!additionFormInited) {
                        additionFormInited = true;
                        $input.tags({
                            onadd: function(tag) {
                                listsIds.push(parseInt(tag.id));
                            },
                            onremove: function(tagId) {
                                listsIds = $.grep(listsIds, function(listsIds) {
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
                    }
                }

                function closeAdditionForm(e) {
                    $openedAdditionForm.hide();
                    $closedAdditionForm.show();
                    box.refreshTop();
                }

                function updateTemplateList() {
                    Events.fire('get_templates', null, function(data) {
                        var clearData = [];
                        $.each(data, function(i, template) {
                            var clearLists = [];

                            $.each(template.lists, function(i, listId) {
                                var listModel = listCollection.get(listId);
                                if (listModel) {
                                    clearLists.push(listModel.data());
                                }
                            });

                            clearData.push({
                                id: template.id,
                                text: template.title,
                                user: userCollection.get(Configs.vkId).data(),
                                lists: clearLists
                            });
                        });
                        $templateList.removeClass('loading');
                        $templateList.html(tmpl(TEMPLATE_LIST, {list: clearData}));
                        box.refreshTop();
                    });
                }

                if (isFocused) {
                    $openAdditionFormTrigger.focus();
                }

                updateTemplateList();
            });
        }
    });
    return box;
}

function makeDate(timestamp) {
    var monthNames = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
    var currentDate = new Date();
    var date = new Date(timestamp * 1000);
    var m = date.getMonth();
    var y = date.getFullYear() + '';
    var d = date.getDate() + '';
    var h = date.getHours() + '';
    var min = date.getMinutes() + '';
    var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
    if (currentDate.getDate() != d || currentDate.getMonth() != m || currentDate.getFullYear() != y) {
        text = d + ' ' + monthNames[m].toLowerCase() + ' ' + y + ' в ' + text;
    }
    return text;
}