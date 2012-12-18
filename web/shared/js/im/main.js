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

        t._dialogs.on('addList', function() {
            t.trigger('addList');
        });

        t._dialogs.on('addToList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._dialogs.on('removeFromList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._messages.on('hoverMessage', function(e) {
            var $message = $(e.currentTarget);
            var messageId = $message.data('id');
            var dialogId = t._messages.pageId();
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
            t._messages.updateAutocomplite();
        });

        t._tabs.on('clickFilter', function() {
            t._dialogs.toggleFilter();
        });

        t._tabs.on('addToList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._tabs.on('removeFromList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._tabs.on('templatesUpdate', function() {
            t._messages.updateAutocomplite();
        });
    },
    onScroll: function(e) {
        var t = this;
        t._messages.onScroll(e);
        t._dialogs.onScroll(e);
    },

    showList: function(listId, isTrigger) {
        var t = this;
        if (!t._dialogs.isVisible()) {
            t._messages.hide();
            t._dialogs.show();
        }
        t._dialogs.changePage(listId);
        t._tabs.setList(listId);
        if (isTrigger) t.trigger('changeList', listId);
    },

    showDialog: function(dialogId, isTrigger) {
        var t = this;
        if (!t._messages.isVisible()) {
            t._dialogs.hide();
            t._messages.show();
        }
        t._messages.changePage(dialogId);
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
        Events.fire('get_lists', function(data) {
            if (t._isFirstRun) {
                t._isFirstRun = false;
                $el.fadeIn(500);
            }
            var list = data.list;
            var commonList = data.commonList;
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
                    counter: commonList.counter,
                    isSelected: !isSetSelectedList,
                    isDraggable: false,
                    isRead: true
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
        if (t._isDragging) return;
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

/**
 * Initialization
 */
$(document).ready(function() {
    var $window = $(window);
    var $main = $('#main');

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

function CreateTemplateBox(listId, text, isFocused, onHide) {
    var box = new Box({
        title: 'Готовые ответы',
        html: tmpl(BOX_LOADING, {height: 100}),
        width: 600,
        onhide: function() {
            if ($.isFunction(onHide)) {
                onHide();
            }
        },
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
                                text: template.title.split('\n').join('<br>'),
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
