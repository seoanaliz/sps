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
