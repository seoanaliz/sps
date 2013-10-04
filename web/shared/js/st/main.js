/**
 * Initialization
 */

var Configs = {
    appId: vk_appId,
    loginBlockReady: false,
    shareButtonReady: false,
    tableLoadOffset: 25,
    controlsRoot: controlsRoot,
    activeElement: document.body
};

var cur = {
    dataUser: {
        isAuthorized: (window.rank > 1),
        isEditor: (window.rank > 2),
        isAdmin: (window.rank > 3)
    }
};

currentListId = 'all';
$(document).ready(function() {
    currentListId = entriesPrecache.groupId;

    (function(w) {
        var $elem = $('#go-to-top');
        $elem.click(function() {
            $(w).scrollTop(0);
        });
        $(w).bind('scroll', function(e) {
            if (w.scrollY <= 0) {
                $elem.hide();
            } else if (!$elem.is(':visible')) {
                $elem.show();
            }
        });
    })(window);

    document.body.addEventListener && document.body.addEventListener('focus', function() {
        Configs.activeElement = document.activeElement;
    }, true);

    Filter.init(function() {
        List.init(function() {
            initHistory();
            Table.init();
        });
    });

    checkVkStatus();
});

function initHistory() {
    window.onpopstate = function(e) {
        if (typeof e.state === 'object' && e.state) {
            if ('listId' in e.state) {
                Filter.selectList(e.state.listId);
            } else {
                Filter.selectList('all');
            }
        }
    };

    // в переменную entriesPrecache передавались данные с сервера
    if (entriesPrecache &&
            typeof history === 'object' && ('replaceState' in history)) {
        // запишем в history состояние, взятое из прекеша
        history.replaceState({listId: entriesPrecache.groupId || 'all', slug: ''}, '');
    }
}

function checkVkStatus() {
    if (typeof VK !== 'undefined' && VK.Api) {
        VK.init({
            apiId: Configs.appId,
            nameTransportPath: '/xd-receiver.htm'
        });

        VK.Auth.getLoginStatus(authInfo);
    } else {
        makeVkButton();
    }
}

function makeVkButton() {
    var redirectAfterLogin = (currentListId === 'my') ? '/stat/' : location.pathname;
    $('.login-info').html('<a href='+ makeVkLoginLink(redirectAfterLogin) +' class="login">Войти</a>');
    revealLoginBlock();
}

function authInfo(response) {
    if (!response.session) {
        makeVkButton();
    } else {
        var code = 'return {' +
            'user: API.getProfiles({fields: "photo"})[0]' +
        '};';
        VK.Api.call('execute', {code: code}, function(answer) {
            var userData = null;
            if (answer && answer.response) {
                $.extend(cur.dataUser, answer.response.user);
                userData = answer.response.user;
            }
            handleUserLoggedIn(userData);
        });
    }
}

function makeVkLoginLink(redirectTo) {
    return vkLoginUrlTpl.replace('{{redirect}}', encodeURIComponent(redirectTo || ''));
}

function handleUserLoggedIn(userData) {
    var $loginInfo = $('.login-info');
    var buttonCode = '<a class="logout" href="/logout/?to=' + encodeURIComponent(location.pathname) + '">Выйти</a>';
    if (userData) {
        $loginInfo.html(buttonCode + '<a class="username"><img class="userpic" alt="" /><span></span></a>');
        var name = userData.first_name + ' ' + userData.last_name;
        $('.username', $loginInfo)
            .attr('href', 'http://vk.com/id' + userData.uid)
            .attr('title', name)
            .find('span')
            .text(name);

        function show() {
            $('.userpic', $loginInfo).attr('src', userData.photo);
            revealLoginBlock();
        }

        var img = new Image();
        img.onload = show;
        img.onerror = show;
        img.src = userData.photo;
    } else {
        $loginInfo.html(buttonCode);
        revealLoginBlock();
    }
}

function revealLoginBlock() {
    Configs.loginBlockReady = true;
    $('.login-info').css({opacity: 1});
    if (Configs.shareButtonReady) {
        document.getElementById('button-wrap').style.opacity = 1;
    }
}

function changeState(listId, slug, doReplace) {
    var pushedURI = '/stat/' + (slug || '');

    if ('pushState' in history) {
        // ничего не делаем, всё ок
    } else if (location.pathname !== pushedURI) { // для старых браузеров — перезагружаем страницу, если URI должен поменяться
        location.href = pushedURI; // REDIRECT, перезагрузка страницы
        return;
    }

    if (doReplace) {
        history.replaceState({listId: listId, slug: slug}, '', pushedURI);
    } else {
        Filter.selectList(listId).success(function() {
            history.pushState({listId: listId, slug: slug}, '', pushedURI);
        });
    }
}

var List = (function() {
    var $container;
    var $actions;

    function init(callback) {
        $container = $('.header');
        $container.find('.tab-bar').html(tmpl(LIST, {items: []}));
        $actions = $('.actions', $container);
        if (entriesPrecache && entriesPrecache.groupId) {
            var listId = entriesPrecache.groupId;
        } else {
            listId = 'all';
        }
        select(listId, callback);
        _initEvents();
    }
    function _initEvents() {
        cur.dataUser.isAdmin && $container.delegate('.actions .share', 'click', function() {
            var $itemSelected = $('.filter .item.selected');
            var listId = $itemSelected.data('id');
            var listTitle = $itemSelected.text();
            var isFirstShow = true;
            var shareUsers = [];
            var shareLists = [];
            var box = new Box({
                id: 'share' + listId,
                title: 'Поделиться',
                html: tmpl(BOX_LOADING),
                buttons: [
                    {label: 'Отправить', onclick: share},
                    {label: 'Отменить', isWhite: true}
                ],
                onshow: function($box) {
                    var box = this;
                    if (isFirstShow) {
                        isFirstShow = false;
                        VK.Api.call('friends.get', {fields: 'first_name, last_name, photo'}, function(dataVK) {
                            Events.fire('load_list', function(dataLists) {
                                if (dataVK && dataVK.error) {
                                    box.setTitle('Ошибка')
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
                                    for (i in dataLists) {
                                        var list = dataLists[i];
                                        lists.push({
                                            id: list.itemId,
                                            title: list.itemTitle
                                        });
                                    }

                                    box.setHTML(tmpl(BOX_SHARE));

                                    var $users = $box.find('.users');
                                    var $lists = $box.find('.lists');
                                    $users.tags({
                                        onadd: function(tag) {
                                            shareUsers.push(parseInt(tag.id));
                                        },
                                        onremove: function(tagId) {
                                            shareUsers = $.grep(shareUsers, function(value) {
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
                                            shareLists = $.grep(shareLists, function(value) {
                                                return value != tagId;
                                            });
                                        }
                                    })
                                    .autocomplete({
                                        data: lists,
                                        target: $lists.closest('.ui-tags'),
                                        onchange: function(item) {
                                            $(this).tags('addTag', item).val('').focus();
                                        }
                                    })
                                    .keydown(function(e) {
                                        if (e.keyCode == KEY.DEL && !$(this).val()) {
                                            $(this).tags('removeLastTag');
                                        }
                                    })
                                    .tags('addTag', {id: listId, title: listTitle});

                                    $box.find('input[value=""]:first').focus();
                                }
                            });
                        });
                    } else {
                        $box.find('input[value=""]:first').focus();
                    }
                }
            }).show();

            function share($button, $box) {
                var box = this;

                if (shareLists.length && shareUsers.length) {
                    Events.fire('share_list', shareLists.join(','), shareUsers.join(','), function() {
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
        cur.dataUser.isAdmin && $container.delegate('.actions .delete', 'click', function() {
            var listId = $('.filter > .list > .item.selected').data('id');
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
                Events.fire('remove_list', listId, function() {
                    Filter.refreshList(function() {
                        $('.filter > .list > .item[data-id="all"]').click();
                    });
                });
            }
        });
        $container.delegate('.tab', 'click', function() {
            Filter.selectList($(this).data('id'));
        });
    }

    function refresh(callback) {
        var $selectedItem = $container.find('.tab.selected');
        var id = $selectedItem.data('id');
        $container.find('.tab-bar').html(tmpl(LIST, {items: []}));
        $actions = $('.actions', $container);
        select(id, function() {
            if ($.isFunction(callback))
                callback();
        });
    }

    function select(id, callback) {
        if (id === 'all' || id === 'not_listed' || id === 'my') {
            $actions.fadeOut(140);
        } else {
            cur.dataUser.isAdmin && $actions.fadeIn(300);
        }
        var $item = $container.find('.tab[data-id=' + id + ']');
        $container.find('.tab.selected').removeClass('selected');
        $item.addClass('selected');

        if ($.isFunction(callback))
            callback();
        else {
            Filter.selectList(id);
        }
    }

    return {
        init: init,
        refresh: refresh,
        select: select
    };
})();

var Filter = (function() {
    var $container;
    var $audienceWrapper;
    var $audience;
    var $periodWrapper;
    var $period;
    var $list;

    function init(callback) {
        $container = $('td > .filter');
        $list = $('> .list', $container);
        $audienceWrapper = $('> .audience-wrapper', $container);
        $audience = $('> .audience', $audienceWrapper);
        $periodWrapper = $('> .period-wrapper', $container);
        $period = $('> .period', $periodWrapper);

        if (entriesPrecache && entriesPrecache.groupId) {
            var listId = entriesPrecache.groupId;
        } else {
            listId = 'all';
        }

        _initEvents();
        refreshList(callback, listId);
    }

    function _initEvents() {
        (function() {
            var $slider = $audience.find('> .slider-wrap');
            var $sliderRange = $audience.find('> .slider-range');
            var $valMin = $sliderRange.find('> .value-min');
            var $valMax = $sliderRange.find('> .value-max');
            var notRender = false;
            $sliderRange.find('.value-min, .value-max').click(function() {
                var $val = $(this);
                $val.attr('contenteditable', true).focus();
            });
            $sliderRange.find('.value-min, .value-max').blur(function() {
                var $val = $(this);
                $val.removeAttr('contenteditable');
            });
            $sliderRange.find('.value-min, .value-max').bind('keyup keydown', function(e) {
                switch (e.keyCode) {
                    case KEY.ENTER:
                    case KEY.ESC:
                        $(this).blur();
                        notRender = false;
                        changeRange({originalEvent: true});
                        return false;
                }
                var intText = intval($(this).html());
                if ($(this).html() != intText) {
                    $(this).text(intText);
                }
                notRender = true;
                if ($(this).hasClass('value-min')) {
                    $slider.slider('values', 0, intText);
                } else {
                    $slider.slider('values', 1, intText);
                }
            });
            $slider.slider({
                range: true,
                min: 0,
                max: 10000000,
                animate: 100,
                values: [0, 10000000],
                create: function(event, ui) {
                    renderRange();
                },
                slide: function(event, ui) {
                    renderRange();
                },
                change: function(event, ui) {
                    if (!notRender) {
                        renderRange();
                    }
                    notRender = false;
                    changeRange(event);
                }
            });
            function renderRange() {
                var audience = [
                    $slider.slider('values', 0),
                    $slider.slider('values', 1)
                ];
                $valMin.html(audience[0]);
                $valMax.html(audience[1]);
            }
            function changeRange(e) {
                var audience = [
                    $slider.slider('values', 0),
                    $slider.slider('values', 1)
                ];
                if (e.originalEvent) {
                    Table.setAudience(audience);
                }
            }
        })();
        $period.delegate('input', 'change', function() {
            var $input = $(this);
            var period;
            switch ($input.val()) {
                case 'day':
                    period = 1;
                    break;
                case 'week':
                    period = 7;
                    break;
                case 'month':
                    period = 30;
                    break;
                default:
                    period = 1;
            }
            Table.setPeriod(period);
        });
        $list.delegate('.item', 'click', function() {
            changeState(this.getAttribute('data-id'), this.getAttribute('data-slug'));
        });
        $list.delegate('.bookmark', 'click', function(e) {
            e.stopPropagation();
            var listId = $(this).closest('.item').data('id');

            var box = new Box({
                title: 'Перемещение списка',
                html: 'Вы уверены, что хотите перенести список?',
                buttons: [
                    {label: 'Переместить', onclick: moveList},
                    {label: 'Отмена', isWhite: true}
                ]
            }).show();

            function moveList() {
                this.hide();
                Events.fire('toggle_group_general', listId, function() {
                    Filter.refreshList();
                });
            }
        });
        $list.delegate('.edit', 'click', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.item');
            var $editField = $('<textarea class="edit-field"></textarea>');
            var $saver = $('<span class="saver">Save</span>');
            $item.append($editField);
            $editField.focus();
            $editField.val($item.attr('title'));
            var saverAppendTimeout = setTimeout(function() {
                $item.append($saver);
                $saver.animate({'margin-right': 0});
            }, 150);
            $saver.click(function(e) {
                e.stopPropagation();
                saveEditor();
            });
            $editField
                    .on('click', function(e) {
                e.stopPropagation();
            })
                    .on('keyup', function(e) {
                if (e.keyCode === KEY.ESC) {
                    destroyEditor();
                }
            })
                    .on('blur', destroyEditor);

            function saveEditor() {
                var id = $item.data('id');
                Events.fire('rename_list', id, $editField.val(), function(success, data) {
                    if (success) {
                        $item.attr('title', data.groupName);
                        $item.attr('data-slug', data.slug);
                        $item.find('.text').text(data.groupName);
                        changeState(id, data.slug, true/*doReplace*/);
                    } else {
                        Filter.refreshList();
                    }
                    destroyEditor();
                });
            }
            function destroyEditor() {
                clearTimeout(saverAppendTimeout);
                $editField.remove();
                $saver.remove();
            }
        });
        // сортировка списков
        if (cur.dataUser.isAdmin) {
            var preventEvent = function() {
                return false;
            };
            $list.filter('.private, .global').sortable({
                axis: 'y',
                tolerance: 'pointer',
                update: function(_, ui) {
                    var listId = ui.item.data('id');
                    var index = $(this).find('.item').index(ui.item);
                    Events.fire('sort_list', listId, index, function(success) {
                        if (!success) {
                            Filter.refreshList();
                        }
                    });
                },
                start: function(_, ui) { // нужно в FireFox (версия 22) для превента выбора списка при окончании драг-н-дропа
                    ui.item.on('click', preventEvent);
                },
                stop: function(_, ui) {
                    setInterval(function() { // отменяет хак для FF
                        ui.item.off('click', preventEvent);
                    }, 10);
                }
            });
        }
    }

    function getList(callback) {
        if (groupsPrecache && groupsPrecache.success) {
            callback(groupsPrecache.data);
            groupsPrecache = false;
        } else {
            Events.fire('load_list', callback);
        }
    }

    function refreshList(callback, maybeListId) {
        var $selectedItem = $list.find('.item.selected');
        var id = maybeListId || $selectedItem.data('id');
        var $list_global = $('> .list.global', $container);
        var $list_private = $('> .list.private', $container);
        var $list_shared = $('> .list.shared', $container);
        getList(function(data) {
            $list_global.html(tmpl(FILTER_LIST, {items: data.global}));
            $list_private.html(tmpl(FILTER_LIST, {items: data.private}));
            $list_shared.html(tmpl(FILTER_LIST, {items: data.shared}));
            if (id) {
                selectList(id, function() {
                    if ($.isFunction(callback))
                        callback();
                });
            } else if ($.isFunction(callback))
                callback();
        });
    }
    function selectList(id, callback) {
        var Def = new Deferred();
        var $item = $list.find('.item[data-id=' + id + ']');
        $list.find('.item.selected').removeClass('selected');
        $item.addClass('selected');

        if ($.isFunction(callback)) {
            callback();
        } else {
            List.select(id, function() {
                Table.changeList(id, $item.data('slug')).success(function() {
                    Def.fireSuccess();
                });
            });
        }
        return Def;
    }
    function setSliderMin(min) {
        var $slider = $audience.find('> .slider-wrap');
        $slider.slider('option', 'min', parseInt(min) - 1);
        $slider.slider('value', $slider.slider('value'));
    }

    function setSliderMax(max) {
        var $slider = $audience.find('> .slider-wrap');
        $slider.slider('option', 'max', parseInt(max) + 1);
        $slider.slider('value', $slider.slider('value'));
    }

    return {
        init: init,
        refreshList: refreshList,
        selectList: selectList,
        setSliderMin: setSliderMin,
        setSliderMax: setSliderMax
    };
})();

var Table = (function() {
    var $container;
    var dataTable = {};
    var pagesLoaded = 0;
    var currentSearch = '';
    var currentSortBy = '';
    var currentSortReverse = false;
    var currentPeriod = 1;
    var currentAudience = [];
    var currentInterval = [];
    var currentListType = 0;

    function init(callback) {
        $container = $('#table');
        _initEvents();
        changeList();
        if ($.isFunction(callback))
            callback();
    }
    function prepareServerData(dirtyData) {
        var clearList = [];
        if (dirtyData.hasMore) {
            $('#load-more-table').show();
        } else {
            $('#load-more-table').hide();
        }

        if ($.isArray(dirtyData.list)) {
            $.each(dirtyData.list, function(i, publicItem) {
                clearList.push({
                    intId: publicItem.id,
                    publicId: publicItem.vk_id,
                    publicImg: publicItem.ava,
                    publicName: publicItem.name,
                    publicFollowers: publicItem.quantity,
                    publicGrowthNum: publicItem.diff_abs,
                    publicGrowthPer: publicItem.diff_rel,
                    publicIsActive: !!publicItem.active,
                    publicInSearch: !!publicItem.in_search,
                    publicVisitors: publicItem.visitors,
                    publicAudience: publicItem.viewers,
                    cpp: publicItem.cpp,
                    lists: ($.isArray(publicItem.group_id) && publicItem.group_id.length) ? publicItem.group_id : [],
                    users: []
                });
            });
        }

        var data = {
            clearList: clearList,
            clearPeriod: [dirtyData.min_max.min, dirtyData.min_max.max],
            clearListType: 0
        };

        return data;
    }
    function loadMore() {
        var $el = $("#load-more-table");
        if ($el.hasClass('loading'))
            return;
        $el.addClass('loading');

        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            offset: pagesLoaded * Configs.tableLoadOffset,
            search: currentSearch,
            sortBy: currentSortBy,
            sortReverse: currentSortReverse,
            period: currentPeriod,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: currentInterval[0],
            timeTo: currentInterval[1]
        };
        if (currentListType) {
            $el.removeClass('loading');
            return;
        }

        Events.fire('load_table', params,
                function(data, maxPeriod, listType) {
                    pagesLoaded += 1;
                    if (!listType) {
                        if (data.length) {
                            dataTable = $.merge(dataTable, data);
                            $tableBody.append(tmpl(TABLE_BODY, {rows: data}));
                        }
                    } else {
                        if (data.length) {
                            dataTable = $.merge(dataTable, data);
                            $tableBody.append(tmpl(OUR_TABLE_BODY, {rows: data}));
                        }
                    }
                    $el.removeClass('loading');
                    initUserPublics();
                }
        );
    }
    function sort(field, reverse, callback) {
        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            search: currentSearch,
            sortBy: field,
            sortReverse: reverse,
            period: currentPeriod,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: currentInterval[0],
            timeTo: currentInterval[1]
        };

        Events.fire('load_table', params,
                function(data, maxPeriod, listType) {
                    pagesLoaded = 1;
                    currentListType = listType;
                    currentSortBy = field;
                    currentSortReverse = reverse;
                    dataTable = data;
                    if (!listType) {
                        $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                    } else {
                        $tableBody.html(tmpl(OUR_TABLE_BODY, {rows: dataTable}));
                    }
                    if ($.isFunction(callback))
                        callback(data);
                    initUserPublics();
                }
        );
    }
    function search(text, callback) {
        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            search: text,
            sortBy: currentSortBy,
            sortReverse: currentSortReverse,
            period: currentPeriod,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: currentInterval[0],
            timeTo: currentInterval[1]
        };

        Events.fire('load_table', params,
                function(data, maxPeriod, listType) {
                    pagesLoaded = 1;
                    currentListType = listType;
                    currentSearch = text;
                    dataTable = data;
                    $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                    if ($.isFunction(callback))
                        callback(data);
                    
                    initUserPublics();
                }
        );
    }
    function setPeriod(period, callback) {
        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            search: currentSearch,
            sortBy: currentSortBy,
            sortReverse: currentSortReverse,
            period: period,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: currentInterval[0],
            timeTo: currentInterval[1]
        };

        Events.fire('load_table', params,
            function(data, maxPeriod, listType) {
                pagesLoaded = 1;
                currentListType = listType;
                currentPeriod = period;
                dataTable = data;
                if (!listType) {
                    $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                } else {
                    $tableBody.html(tmpl(OUR_TABLE_BODY, {rows: dataTable}));
                }
                if ($.isFunction(callback))
                    callback(data);
                
                initUserPublics();
            }
        );
    }
    function setAudience(audience, callback) {
        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            search: currentSearch,
            sortBy: currentSortBy,
            sortReverse: currentSortReverse,
            period: currentPeriod,
            audienceMin: audience[0],
            audienceMax: audience[1]
        };
        if (currentInterval[0] && currentInterval[1]) {
            params.timeFrom = currentInterval[0];
            params.timeTo = currentInterval[1];
        }

        Events.fire('load_table', params,
                function(data, maxPeriod, listType) {
                    pagesLoaded = 1;
                    currentListType = listType;
                    currentAudience = audience;
                    dataTable = data;
                    if (!listType) {
                        $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                    } else {
                        $tableBody.html(tmpl(OUR_TABLE_BODY, {rows: dataTable}));
                    }
                    if ($.isFunction(callback))
                        callback(data);
                    
                    initUserPublics();
                }
        );
    }
    function setInterval(interval, callback) {
        var $tableBody = $('.list-body');
        var params = {
            listId: currentListId,
            limit: Configs.tableLoadOffset,
            search: currentSearch,
            sortBy: currentSortBy,
            sortReverse: currentSortReverse,
            period: currentPeriod,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: interval[0],
            timeTo: interval[1]
        };

        Events.fire('load_table', params,
                function(data, maxPeriod, listType) {
                    pagesLoaded = 1;
                    currentListType = listType;
                    currentInterval = interval;
                    dataTable = data;
                    if (!listType) {
                        $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                    } else {
                        $tableBody.html(tmpl(OUR_TABLE_BODY, {rows: dataTable}));
                    }
                    if ($.isFunction(callback))
                        callback(data);
                    
                    initUserPublics();
                }
        );
    }
    function changeList(listId, slug) {
        var Def = new Deferred();

        var defSearch = '';
        var defSortBy = 'growth';
        var defSortReverse = false;
        var params = {
            listId: listId,
            limit: Configs.tableLoadOffset,
            search: defSearch,
            sortBy: defSortBy,
            sortReverse: defSortReverse,
            period: currentPeriod,
            audienceMin: currentAudience[0],
            audienceMax: currentAudience[1],
            timeFrom: currentInterval[0],
            timeTo: currentInterval[1],
            slug: slug
        };

        function getTable(params, callback) {
            if (typeof entriesPrecache === 'object') { // в переменную entriesPrecache передавались данные с сервера
                var data = prepareServerData(entriesPrecache);
                listId = entriesPrecache.groupId;
                callback(data.clearList, data.clearPeriod, data.clearListType, entriesPrecache.groupId);
                entriesPrecache = false;
            } else {
                Events.fire('load_table', params, callback);
            }
        }

        getTable(params, loadTableCallback);

        function loadTableCallback(data, maxPeriod, listType) {
            pagesLoaded = 1;
            currentListType = listType;
            currentListId = listId;
            currentSearch = defSearch;
            currentSortBy = defSortBy;
            currentSortReverse = defSortReverse;
            dataTable = data;
            if (!listType) {
                $container.html(tmpl(TABLE, {rows: data}));
            } else {
                $container.html(tmpl(OUR_TABLE, {rows: data}));
            }
            $container.find('.' + currentSortBy).addClass('active');
            if (!currentListId) {
                $container.removeClass('no-list-id');
            } else {
                $container.addClass('no-list-id');
            }
            if (dataTable.length < Configs.tableLoadOffset) {
                $('#load-more-table').hide();
            } else {
                $('#load-more-table').show();
            }
            Filter.setSliderMin(maxPeriod[0]);
            Filter.setSliderMax(maxPeriod[1]);
            Def.fireSuccess();
            initUserPublics();
        }

        return Def;
    }

    /**
     * Переназначает сама себя
     * @lazy
     */
    var initUserPublicsEvents = function () {
        $container.delegate('.cpp-value.editable', 'click', function () {
            var $container = $(this);
            $container.removeClass('editable');
            $container.data('htmlBefore', $container.html());
            $container.html('<span class="edit-wrap"><input class="input" type="text" />&nbsp;руб</span>');
            $container.find('.input').bind('blur',
                function () {
                    cancelEditor($(this).closest('.cpp-value'));
                }).val($container.data('cpp')).focus();
        })
        .delegate('.cpp-value .input', 'keyup', function (e) {
            var $input = $(this);
            $input.removeClass('invalid');
            if (e.keyCode === KEY.ENTER) {
                var $container = $input.closest('.cpp-value');
                saveEditor($container.closest('.public').find('.public-info').data('id'), $input.val()).success(function(result) {
                    if (result) {
                        if (result.success) {
                            if (result.cpp) {
                                $container.text(result.cpp + ' руб');
                            } else {
                                $container.html('<span class="unspec">Не указано</span>');
                            }
                            $container.data('cpp', result.cpp);
                            $container.addClass('editable');
                        } else if (result.validation) {
                            $input.addClass('invalid');
                        }
                    }
                });
            } else if (e.keyCode === KEY.ESC) {
                cancelEditor($(this).closest('.cpp-value'));
            }
        });

        function saveEditor(id, rawVal) {
            var Def = new Deferred();
            var val = $.trim(rawVal);
            if (val) {
                Events.fire('set_cpp', id, val, function (result) {
                    Def.fireSuccess(result);
                });
            } else {
                Def.fireSuccess(false);
            }
            return Def;
        }

        function cancelEditor($container) {
            $container.html($container.data('htmlBefore'));
            $container.addClass('editable');
        }

        initUserPublicsEvents = function() {};
    }

    /**
     * @private
     */
    function initUserPublics() {
        if (currentListId === 'my') {
            $container.find('.cpp-value')
                        .addClass('editable')
                    .find('.unspec')
                        .html('Не указано');

            initUserPublicsEvents();
        }
    }

    function _initEvents() {
        $container.delegate('.action.add-to-list', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            var publicData;

            for (var i in dataTable) {
                if (dataTable[i].intId === publicId) {
                    publicData = dataTable[i];
                    break; // ------------------- BREAK
                }
            }
            if (publicData) {
                e.stopPropagation();
                _createDropdown($(e.currentTarget), publicData);
            }
        });

        $container.delegate('.action.delete-public', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            Events.fire('remove_from_list', publicId, currentListId, function() {
                $public.addClass('not-editable');
            });
        });

        $container.delegate('.action.restore-public', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            Events.fire('add_to_list', publicId, currentListId, function() {
                $public.removeClass('not-editable');
            });
        });

        var sortFields = {
            followers: '.followers',
            viewers: '.audience',
            growth: '.growth',
            isActive: '.is-active',
            inSearch: '.in-search',
            visitors: '.visitors',
            views: '.views',
            cpp: '.cpp'
        };

        $.each(sortFields, function(sortFieldKey, sortFieldSelector) {
            $container.delegate(sortFieldSelector, 'click', function() {
                var $target = $(this);
                $target.closest('.list-head').find('.column').not($target).removeClass('reverse active');
                if ($target.hasClass('active') && !$target.hasClass('reverse')) {
                    $target.addClass('reverse')
                        .find('.arrow')
                            .addClass('top');
                    sort(sortFieldKey, true);
                } else {
                    $target.addClass('active')
                           .removeClass('reverse')
                           .find('.arrow')
                               .removeClass('top');
                    sort(sortFieldKey, false);
                }
            });
        });

        (function() {
            var timeout;
            $container.delegate('#filter', 'keyup', function(e) {
                var $filter = $(this);
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    $filter.addClass('loading');
                    search($.trim($filter.val()), function() {
                        $filter.removeClass('loading');
                    });
                }, 500);
            });
        })();

        (function() {
            var b = $('#load-more-table');
            var w = $(window);

            b.click(function() {
                loadMore();
            });
            w.scroll(function() {
                if (b.is(':visible') && w.scrollTop() > (b.offset().top - w.outerHeight(true) - w.height())) {
                    loadMore();
                }
            });
        })();
    }

    function _createDropdown($el, publicData) {
        var offset = $el.offset();
        var $dropdown;
        var $public = $el.closest('.public');
        var publicId = $public.data('id');
        var selectedLists = publicData.lists;
        var listId = null;

        Events.fire('load_list', function(dataList) {
            if (!$el.hasClass('selected')) {
                var categories = [{
                        title: 'Личные',
                        items: dataList.private
                    }, {
                        title: 'Категории',
                        items: dataList.global
                    }];

                $dropdown = $(tmpl(DROPDOWN, {categories: categories})).appendTo('body');

                // поиск по категориям
                initListSearch();

                var $input = $dropdown.find('.add-item');
                $.each(selectedLists, function(i, listId) {
                    $dropdown.find('[data-id=' + listId + ']').addClass('selected');
                });

                $dropdown.delegate('.show-input', 'click', function() {
                    $input.show().focus();
                })
                .delegate('.item:not(.show-input)', 'mousedown', function(e) {
                    var $item = $(this);
                    onChange($item);
                })
                .delegate('.add-item', 'keyup', function(e) {
                    if (e.keyCode === KEY.ENTER) {
                        onSave($.trim($input.val()));
                    }
                })
                .delegate('.add-item', 'blur', function () {
                    onSave($.trim($input.val()));
                })
                $dropdown.bind('mousedown', function(e) {
                    e.stopPropagation();
                });
                $(document).mousedown(function() {
                    if ($dropdown.is(':hidden')) {
                        return;
                    }
                    $dropdown.hide();
                    $el.removeClass('selected');
                    setTimeout(function() {
                        if (document.activeElement === document.body) {
                            $(Configs.activeBeforeDropdown).focus();
                        }
                    }, 50);
                });

                function onSave(groupName) {
                    Events.fire('add_list', groupName, function() {
                        Events.fire('load_list', function(dataList) {
                            var all_lists = dataList.private;
                            all_lists.push.apply(all_lists, dataList.global);

                            var $tmpDropdown = $(tmpl(DROPDOWN, {items: all_lists}));
                            $dropdown.html($tmpDropdown.html());
                            $input = $dropdown.find('.add-item');
                            $dropdown.find('.search').focus();
                            Filter.refreshList();
                        });
                    });
                }
                function onChange($item) {
                    listId = $item.data('id');
                    var isSelected = !$item.hasClass('selected');
                    var callback = function(data) {
                        if (!data)
                            return;
                        $item.toggleClass('selected');
                        if ($dropdown.find('.item.selected').length) {
                            $el.find('.icon').removeClass('plus').addClass('select');
                        } else {
                            $el.find('.icon').removeClass('select').addClass('plus');
                        }
                    };
                    if (isSelected) {
                        Events.fire('add_to_list', publicId, listId, callback);
                    } else {
                        Events.fire('remove_from_list', publicId, listId, callback);
                    }
                }

                showDropdown();
                $dropdown.find('.search').focus();
            }
        });

        function initListSearch() {
            var previousDisplay = $dropdown.find('.item')[0].style.display;
            var previousCategoryDisplay = $dropdown.find('.category')[0].style.display;
            function clearSearch() {
                var $search = $dropdown.find('.search');
                $search.attr('value', '');
                $search.trigger('change');
                $search.focus();
            }
            var previousValue = '';
            var clearSearchNode = $dropdown.find('.clear-search')[0];
            $dropdown.delegate('.search', 'keyup drop paste change', function(e) {
                var val = $(this).val();
                if (('keyCode' in e) && e.keyCode === KEY.ESC) {
                    return clearSearch(); // ---- RETURN
                }
                if (val !== previousValue) {
                    clearSearchNode.style.display = val ? 'block' : 'none';
                    var regexp = new RegExp(val, 'gim');

                    $dropdown.find('.category').each(function() {
                        var $category = $(this);
                        var i = Number(this.getAttribute('data-number'));
                        $category.find('.item').each(function() {
                            var text = this.getAttribute('title');
                            if (regexp.test(text)) {
                                var div = this.childNodes[0];
                                div.innerHTML = val ? text.replace(regexp, "<span class=\"highlight\">$&</span>") : text;
                                this.style.display = previousDisplay;
                            } else {
                                i--;
                                this.style.display = 'none';
                            }
                        });
                        if (i === 0) {
                            this.style.display = 'none';
                        } else {
                            this.style.display = previousCategoryDisplay;
                        }
                    });
                    previousValue = val;
                }
            })
            .delegate('.clear-search', 'click', clearSearch);
        }

        function showDropdown() {
            Configs.activeBeforeDropdown = Configs.activeElement;
            $el.addClass('selected');
            $dropdown.show().css({
                top: offset.top + 29,
                left: offset.left - $dropdown.outerWidth() + 40
            });
        }
    }

    function setCurrentInterval(interval) {
        currentInterval = interval;
    }

    return {
        init: init,
        changeList: changeList,
        loadMore: loadMore,
        sort: sort,
        search: search,
        setPeriod: setPeriod,
        setAudience: setAudience,
        setInterval: setInterval,
        setCurrentInterval: setCurrentInterval,
        prepareServerData: prepareServerData
    };
})();
