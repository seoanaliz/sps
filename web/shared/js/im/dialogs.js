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
    _isFiltered: false,
    UNREAD_PREFIX: 'unread',

    _events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon': 'clickPlus'
    },

    changePage: function(pageId, force) {
        var t = this;
        if (pageId != t.pageId()) {
            t.filtered(false);
        }
        return t._super.apply(this, arguments);
    },
    makeList: function($list) {
        $list.find('.date').each(function() {
            var $date = $(this);
            var timestamp = intval($date.text());
            $date.html(makeDate(timestamp));
        });
    },
    clickDialog: function(e) {
        var t = this;
        if (!$(e.target).closest('a').length) {
            t.trigger('clickDialog', e);
        }
    },
    clickPlus: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        var $dialog = $target.closest('.dialog');
        var dialogId = $dialog.data('id');
        var dialogModel = dialogCollection.get(dialogId);
        if (!$target.data('dropdown')) {
            (function updateDropdown() {

                function onCreate() {
                    $.each(dialogModel.lists(), function(i, listId) {
                        $target.dropdown('getItem', listId).addClass('active');
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
                                    var index = $.inArray(item.id, dialogModel.lists());
                                    if (index == -1) {
                                        dialogModel.lists().push(item.id);
                                        t.trigger('addToList', item.id, dialogId);
                                    }
                                    var pageId = t.pageId() == Configs.commonDialogsList ? null : t.pageId();
                                    var isAtCurrentList = false;
                                    if (!dialogModel.lists().length && !pageId) {
                                        isAtCurrentList = true;
                                    } else if ($.inArray(pageId, dialogModel.lists()) !== -1) {
                                        isAtCurrentList = true;
                                    }
                                    if (!isAtCurrentList) {
                                        $dialog.addClass('removed');
                                    } else {
                                        $dialog.removeClass('removed');
                                    }
                                });
                            }
                        },
                        onunselect: function(item) {
                            Events.fire('remove_from_list', dialogId, item.id, function() {
                                var index = $.inArray(item.id, dialogModel.lists());
                                if (index != -1) {
                                    dialogModel.lists().splice(index, 1);
                                    t.trigger('removeFromList', item.id, dialogId);
                                }
                                var pageId = t.pageId() == Configs.commonDialogsList ? null : t.pageId();
                                var isAtCurrentList = false;
                                if (!dialogModel.lists().length && !pageId) {
                                    isAtCurrentList = true;
                                } else if ($.inArray(pageId, dialogModel.lists()) !== -1) {
                                    isAtCurrentList = true;
                                }
                                if (!isAtCurrentList) {
                                    $dialog.addClass('removed');
                                } else {
                                    $dialog.removeClass('removed');
                                }
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
            t.ended(true);
        }
        t.model().id(t.pageId());
        t.model().list(t.model().list().concat(dialogs));

        for (var i in dialogs) {
            if (!dialogs.hasOwnProperty(i)) {
                continue;
            }
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
        if (t.checkAtBottom()) {
            $(window).trigger('scroll');
        }
    },
    addDialog: function(dialogModel) {
        var t = this;
        console.log(dialogModel);
        var isCommonList = (t.pageId() == Configs.commonDialogsList);
        if (!(dialogModel instanceof DialogModel)) {
            throw new TypeError('Dialog is not correct');
        }
        if ($.inArray(t.pageId(), dialogModel.lists()) == -1) {
            if (!isCommonList) {
                return false;
            }
            else if (dialogModel.lists().length) {
                return false;
            }
        }

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
    },
    toggleFilter: function() {
        var t = this;
        if (!t.filtered()) {
            t.filtered(true);
            t.changePage(t.pageId(), true);
        } else {
            t.filtered(false);
            t.changePage(t.pageId(), true);
        }
    },
    show: function() {
        this._super.apply(this, arguments);

        var t = this;
        t.el().find('.dialog.removed').remove();
    },
    filtered: function(filtered) {
        if (arguments.length) {
            this._isFiltered = filtered;
            return this;
        } else {
            return !!this._isFiltered;
        }
    },
    serviceParams: function() {
        var t = this;
        if (arguments.length) {
            return this._super.apply(this, arguments);
        } else {
            return {
                pageId: t.pageId() == Configs.commonDialogsList ? undefined : t.pageId(),
                limit: t.itemsLimit(),
                offset: t.pageLoaded() * t.itemsLimit(),
                filter: t.filtered()
            }
        }
    }
});
