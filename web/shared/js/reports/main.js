Configs = {
    appId: vk_appId,
    limit: 50,
    controlsRoot: controlsRoot,
    eventsDelay: 0,
    eventsIsDebug: true
};

/**
 * @class GroupModel
 * @extends Model
 */
GroupModel = Model.extend({
    id: function(id) {
        return strval(this.data('id', id));
    },
    name: function(name) {
        return strval(this.data('name', name));
    },
    place: function(place) {
        return intval(this.data('place', place));
    },
    type: function(type) {
        return intval(this.data('type', type));
    }
});

/**
 * @class GroupCollection
 * @extends Collection
 */
GroupCollection = Collection.extend({
    modelClass: GroupModel
});

/**
 * @class GroupListModel
 * @extends Model
 */
GroupListModel = Model.extend({
    _groupCollectionClass: GroupCollection,

    defaultLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('defaultLists', setValue);
    },

    userLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('userLists', setValue);
    },

    sharedLists: function(setValue) {
        if (arguments.length) setValue = setValue instanceof this._groupCollectionClass ? setValue : new this._groupCollectionClass();
        return this.data('sharedLists', setValue);
    }
});

/**
 * @class Pages
 * @singleton
 */
Pages = Event.extend({
    monitor: null,
    result: null,
    currentPage: null,
    groupListWidget: null,

    init: function() {
        var t = this;
        $('#main').html(tmpl(REPORTS.MAIN));
        var $header = $('#header');
        $header.html(tmpl(REPORTS.HEADER));
        t.monitor = new MonitorPage();
        t.result = new ResultPage();

        $('#share-list').click(function() {
            t.showShareBox();
        });

        $('#delete-list').click(function() {
            t.showDeleteBox();
        });

        $('#tab-results').click(function() {
            t.showResults();
            $header.find('.tab').removeClass('selected');
            $(this).addClass('selected');
        });

        $('#filter').delegate('input', 'change', function() {
            var filter = $(this).val();
            t.monitor.filter = filter;
            t.result.filter = filter;
            t.currentPage.update();
        });

        $('#tab-monitors').click(function() {
            t.showMonitors();
            $header.find('.tab').removeClass('selected');
            $(this).addClass('selected');
        });

        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                t.currentPage.showMore();
            }
        });

        t.showMonitors();
        t.showRightColumn();
    },

    showResults: function() {
        var t = this;
        t.currentPage = t.result;
        t.currentPage.update();
    },

    showMonitors: function() {
        var t = this;
        t.currentPage = t.monitor;
        t.currentPage.update();
    },

    showRightColumn: function() {
        var t = this;

        if (!t.groupListWidget) {
            t.groupListModel = new GroupListModel();
            t.groupListWidget = new GroupListWidget({
                model: t.groupListModel,
                selector: '#group-list'
            });
            t.groupListWidget.on('change', function(groupId) {
                t.monitor.groupId = groupId;
                t.result.groupId = groupId;
                t.currentPage.update();
            });
        } else {
            t.groupListWidget.render();
        }
    },

    showDeleteBox: function() {
        var t = this;
        var listId = t.groupListWidget._groupId;
        new Box({
            id: 'deleteList' + listId,
            title: 'Удаление',
            html: 'Вы уверены, что хотите удалить список?',
            buttons: [
                {label: 'Удалить', onclick: function() {
                    this.hide();
                    t.deleteList(listId);
                }},
                {label: 'Отмена', isWhite: true}
            ]
        }).show();
    },

    deleteList: function(listId) {
        var t = this;
        Control.fire('remove_list', {
            groupId: listId
        }, function() {
            t.groupListWidget.run();
            t.groupListWidget.el().find('.item:first').click();
        });
    },

    showShareBox: function() {
        var t = this;

        if (typeof t.groupListWidget != 'object') {
            return;
        }

        var dataLists = $.extend(true, {},
            t.groupListModel.defaultLists().get(),
            t.groupListModel.userLists().get(),
            t.groupListModel.sharedLists().get()
        );
        var listId = t.groupListWidget._groupId;
        var listTitle = $('.filter > .list > .item.selected').text();
        var shareUsers = [];
        var shareLists = [];
        var isFirstShow = true;
        var shareBox = new Box({
            id: 'share' + listId,
            title: 'Поделиться',
            html: tmpl(BOX_LOADING),
            buttons: [
                {label: 'Отправить', onclick: function() {
                    t.shareList.call(this, shareUsers, shareLists)}
                },
                {label: 'Отменить', isWhite: true}
            ],
            onshow: function($box) {
                if (isFirstShow) {
                    isFirstShow = false;
                } else {
                    return;
                }

                VK.Api.call('friends.get', {fields: 'first_name, last_name, photo'}, function(dataVK) {
                    if (dataVK && dataVK.error) {
                        shareBox
                            .setTitle('Ошибка')
                            .setHTML('Вы не предоставили доступ к друзьям.')
                            .setButtons([
                                {label: 'Перелогиниться', onclick: function() {
                                    VK.Auth.logout(function() {
                                        location.replace('login/');
                                    });
                                }},
                                {label: 'Отмена', isWhite: true}
                            ]);
                    } else {
                        var dataVKfriends = dataVK.response;
                        var friends = [];
                        for (var i in dataVKfriends) {
                            var user = dataVKfriends[i];
                            friends.push({
                                id: user.uid,
                                icon: user.photo,
                                title: user.first_name + ' ' + user.last_name
                            });
                        }
                        var lists = [];
                        for (var i in dataLists) {
                            var list = dataLists[i];
                            lists.push({
                                id: list.id,
                                title: list.name
                            });
                        }

                        shareBox.setHTML(tmpl(REPORTS.BOX_SHARE));

                        var $users = $box.find('.users');
                        var $lists = $box.find('.lists');
                        $users.tags({
                            onadd: function(tag) {
                                shareUsers.push(parseInt(tag.id));
                            },
                            onremove: function(tagId) {
                                shareUsers = jQuery.grep(shareUsers, function(value) {
                                    return value != tagId;
                                });
                            }
                        }).autocomplete({
                            data: friends,
                            target: $users.closest('.ui-tags'),
                            onchange: function(item) {
                                $(this).tags('addTag', item).val('').focus();
                            }
                        }).keydown(function(e) {
                            if (e.keyCode == KEY.DEL && !$(this).val()) {
                                $(this).tags('removeLastTag');
                            }
                        });

                        $lists.tags({
                            onadd: function(tag) {
                                shareLists.push(tag.id);
                            },
                            onremove: function(tagId) {
                                shareLists = jQuery.grep(shareLists, function(value) {
                                    return value != tagId;
                                });
                            }
                        }).autocomplete({
                            data: lists,
                            target: $lists.closest('.ui-tags'),
                            onchange: function(item) {
                                $(this).tags('addTag', item).val('').focus();
                            }
                        }).keydown(function(e) {
                            if (e.keyCode == KEY.DEL && !$(this).val()) {
                                $(this).tags('removeLastTag');
                            }
                        }).tags('addTag', {
                            id: listId,
                            title: listTitle
                        });

                        $box.find('input[value=""]:first').focus();
                    }
                });
            }
        }).show();
    },

    shareList: function(shareUsers, shareLists) {
        var box = this;

        if (shareLists.length && shareUsers.length) {
            Control.fire('share_list', {
                groupIds: shareLists.join(','),
                userIds: shareUsers.join(',')
        }, function() {
                box.hide();
                new Box({
                    id: 'shareSuccess',
                    title: 'Поделиться',
                    html: 'Выбранные друзья успешно получили доступ к спискам',
                    buttons: [
                        {label: 'Закрыть'}
                    ]
                }).show();
            });
        } else {
            new Box({
                id: 'shareError',
                title: 'Ошибка',
                html: 'Не выбран пользователь или список',
                buttons: [
                    {label: 'Закрыть'}
                ]
            }).show();
        }
    }
});

$(document).ready(function() {
    $.mask.definitions['2']='[012]';
    $.mask.definitions['3']='[0123]';
    $.mask.definitions['5']='[012345]';
    $.datepicker.setDefaults({
        dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        firstDay: 1,
        showAnim: '',
        dateFormat: 'd MM'
    });

    defaultGroupCollection = new GroupCollection();
    sharedGroupCollection = new GroupCollection();
    userGroupCollection = new GroupCollection();

    VK.init({
        apiId: Configs.appId,
        nameTransportPath: '/xd_receiver.htm'
    });

    try {
        new Pages();
    } catch(e) {
        $('#global-loader').hide();
        new Box({
            title: 'Ошибка',
            html: 'Произошла ошибка JavaScript :('
        }).show();
        throw e;
    }
});
