var Tabs = Widget.extend({
    _template: TABS,
    _modelClass: TabsModel,
    _userId: null,
    _isFiltered: null,

    _events: {
        'click: .tab.dialog': 'clickDialog',
        'click: .tab.list': 'clickList',
        'click: .icon': 'clickPlus',
        'click: .filter': 'clickFilter',
        'click: .show-templates': 'clickShowTemplates'
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
                    $.each(dialogModel.lists(), function(i, listId) {
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
                                    var index = $.inArray(item.id, dialogModel.lists());
                                    if (index == -1) {
                                        dialogModel.lists().push(item.id);
                                        t.trigger('addToList', item.id, dialogId);
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

    clickFilter: function(e) {
        var t = this;
        var $target = $(e.currentTarget);
        if (!t.filtered()) {
            t.filtered(true);
            $target.html($target.data('filtered'));
        } else {
            t.filtered(false);
            $target.html($target.data('not-filtered'));
        }
        t.trigger('clickFilter');
    },

    clickShowTemplates: function() {
        var t = this;
        var listTab = t.model().listTab();
        var listId = listTab.id();
        var box = new CreateTemplateBox(listId, false, false, function() {
            t.trigger('templatesUpdate');
        });
        box.show();
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
        if (t.model().listTab() && t.model().listTab().id() != listId) {
            t.filtered(false);
        }
        t.model().listTab(listTab);
        t.renderTemplate();
        //@todo привести к нормальному виду
        t.el().find('.show-templates').hide();
        var $filter = t.el().find('.filter');
        $filter.show();
        if (t.filtered()) {
            $filter.html($filter.data('filtered'));
        } else {
            $filter.html($filter.data('not-filtered'));
        }
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
        //@todo привести к нормальному виду
        t.el().find('.show-templates').show();
        var $filter = t.el().find('.filter');
        $filter.hide();
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
    },
    filtered: function(filtered) {
        if (arguments.length) {
            this._isFiltered = filtered;
            return this;
        } else {
            return !!this._isFiltered;
        }
    }
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
