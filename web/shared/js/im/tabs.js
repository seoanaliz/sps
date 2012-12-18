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
