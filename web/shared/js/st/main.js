/**
 * Initialization
 */

var Configs = {
    appId: vk_appId,
    maxRows: 10000,
    tableLoadOffset: 20,
    controlsRoot: controlsRoot,
    eventsDelay: 0,
    etc: null
};

var cur = {
    dataUser: {
        isEditor: (window.rank > 2),
        isAdmin: (window.rank > 3)
    }
};

$(document).ready(function() {
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

    Filter.init(function() {
        List.init(function() {
            Table.init();
            Counter.init();
        });
    });
    checkVkStatus();
});

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

function authInfo(response) {
    if (!response.session) {
        makeVkButton();
    } else {
        var code = 'return {' +
            'user: API.getProfiles({fields: "photo"})[0]' +
        '};';
        VK.Api.call('execute', {code: code}, function (answer) {
            if (answer && answer.response) {
                jQuery.extend(cur.dataUser, answer.response.user);
                handleUserLoggedIn(answer.response.user);
            };
        });
    }
}

function makeVkButton() {
    var $loginInfo = $('.login-info');
    if ($loginInfo.length) {
        var vkHref = 'https://oauth.vk.com/authorize?' +
                    'client_id='+ Configs.appId +
                    '&scope=stats,groups,offline' +
                    '&redirect_uri='+ encodeURIComponent(location.protocol + '//' + location.host + '/vk-login/?to=' + location.pathname) +
                    '&display=page' +
                    '&response_type=code';
        $('.login-info').html( $('<a />', {'class': 'login', href: vkHref}).text('Войти') );
    }
}

function handleUserLoggedIn(userData) {
    var $loginInfo = $('.login-info');
    $loginInfo.html('<a class="logout" href="/logout/?to='+ encodeURIComponent(location.pathname) +'">Выйти</a><a class="username"><img class="userpic" alt="" /><span></span></a>');
    var name = userData.first_name + ' ' + userData.last_name;
    $('.username', $loginInfo)
        .attr('href', 'http://vk.com/id' + userData.uid)
        .attr('title', name)
    .find('span')
        .text(name);
    $('.userpic', $loginInfo).attr('src', userData.photo);
}

var List = (function() {
    var $container;
    var $actions;

    function init(callback) {
        $container = $('.header');

        Events.fire('load_bookmarks', function(data) {
            $container.find('.tab-bar').html(tmpl(LIST, {items: data}));
            $actions = $('.actions', $container);
            select('all');
            callback();
            _initEvents();
        });
    }
    function _initEvents() {
        $container.delegate('.tab', 'click', function() {
            var $item = $(this);
            select($item.data('id'));
        });
        $container.delegate('.actions .share', 'click', function() {
            var listId = $('.filter > .list > .item.selected').data('id');
            var listTitle = $('.filter > .list > .item.selected').text();
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
                                    box
                                        .setTitle('Ошибка')
                                        .setHTML('Вы не предоставили доступ к друзьям.')
                                        .setButtons([
                                            {label: 'Перелогиниться', onclick: function() {
                                                VK.Auth.logout(function() {
                                                    location.replace('login/');
                                                });
                                            }},
                                            {label: 'Отмена', isWhite: true}
                                        ])
                                    ;
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
                                            id: list.itemId,
                                            title: list.itemTitle
                                        });
                                    }

                                    box.setHTML(tmpl(BOX_SHARE));

                                    var $users = $box.find('.users');
                                    var $lists = $box.find('.lists');
                                    $users
                                        .tags({
                                            onadd: function(tag) {
                                                shareUsers.push(parseInt(tag.id));
                                            },
                                            onremove: function(tagId) {
                                                shareUsers = jQuery.grep(shareUsers, function(value) {
                                                    return value != tagId;
                                                });
                                            }
                                        })
                                        .autocomplete({
                                            data: friends,
                                            target: $users.closest('.ui-tags'),
                                            onchange: function(item) {
                                                $(this).tags('addTag', item).val('').focus();
                                            }
                                        })
                                        .keydown(function(e) {
                                            if (e.keyCode == KEY.DEL && !$(this).val()) {
                                                $(this).tags('removeLastTag');
                                            }
                                        })
                                    ;
                                    $lists
                                        .tags({
                                            onadd: function(tag) {
                                                shareLists.push(tag.id);
                                            },
                                            onremove: function(tagId) {
                                                shareLists = jQuery.grep(shareLists, function(value) {
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
                                        .tags('addTag', {id: listId, title: listTitle})
                                    ;
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
        $container.delegate('.actions .edit', 'click', function() {
            Table.toggleEditMode();
        });
        $container.delegate('.actions .delete', 'click', function() {
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
                box.hide();
                Events.fire('remove_list', listId, function() {
                    List.refresh(function() {
                        Filter.listRefresh(function() {
                            $('.filter .list .item[data-id="all"]').click();
                        });
                    });
                });
            }
        });
    }

    function refresh(callback) {
        var $selectedItem = $container.find('.tab.selected');
        var id = $selectedItem.data('id');
        Events.fire('load_bookmarks', function(data) {
            $container.find('.tab-bar').html(tmpl(LIST, {items: data}));
            $actions = $('.actions', $container);
            select(id, function() {
                if ($.isFunction(callback)) callback();
            });
        });
    }

    function select(id, callback) {
        if (id === 'all' || id === 'all_not_listed') {
            id = null;
            $actions.hide();
        } else {
            $actions.show();
        }
        var $item = $container.find('.tab[data-id=' + id + ']');
        $container.find('.tab.selected').removeClass('selected');
        $item.addClass('selected');

        if ($.isFunction(callback)) {
            callback();
        } else {
            Filter.listSelect($item.data('id'));
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
    var $intervalWrapper;
    var $interval;

    function init(callback) {
        $container = $('td > .filter');
        $list = $('> .list', $container);
        $audienceWrapper = $('> .audience-wrapper', $container);
        $audience = $('> .audience', $audienceWrapper);
        $periodWrapper = $('> .period-wrapper', $container);
        $period = $('> .period', $periodWrapper);
        $intervalWrapper = $('> .interval-wrapper', $container);
        $interval = $('> .interval', $intervalWrapper);

        _initEvents();
        listRefresh(callback);
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
                switch(e.keyCode) {
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
        (function() {
            var $timeFrom = $interval.find('.timeFrom');
            var $timeTo = $interval.find('.timeTo');
            $($timeFrom).add($timeTo).datepicker({
                dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
                monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                firstDay: 1,
                showAnim: '',
                dateFormat: 'd MM yy'
            }).change(function(e) {
                var $timeFrom = $interval.find('.timeFrom');
                var $timeTo = $interval.find('.timeTo');
                var dateFrom = $timeFrom.datepicker('getDate');
                var dateTo = $timeTo.datepicker('getDate');
                $timeTo.datepicker('option', 'minDate', dateFrom);
                $timeFrom.datepicker('option', 'maxDate', dateTo);
                Table.setInterval([
                    Math.round(dateFrom ? (dateFrom.getTime() / 1000) : null),
                    Math.round(dateTo ? (dateTo.getTime() / 1000) : null)
                ]);
            });
            $timeTo.datepicker('setDate', new Date((new Date).getTime() - TIME.DAY));
            $timeFrom.datepicker('setDate', new Date($timeTo.datepicker('getDate').getTime() - TIME.DAY));
            var dateFrom = $timeFrom.datepicker('getDate');
            var dateTo = $timeTo.datepicker('getDate');
            $timeTo.datepicker('option', 'minDate', dateFrom);
            $timeFrom.datepicker('option', 'maxDate', dateTo);
            Table.setCurrentInterval([
                intval($timeFrom.datepicker('getDate').getTime() / 1000),
                intval($timeTo.datepicker('getDate') / 1000)
            ]);
        })();
        $period.delegate('input', 'change', function() {
            var $input = $(this);
            var period;
            switch($input.val()) {
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
            var $item = $(this);
            listSelect($item.data('id'));
        });
        $list.delegate('.item > .bookmark', 'click', function(e) {
            e.stopPropagation();
            var $icon = $(this);
            var $item = $icon.closest('.item');
            var listId = $item.data('id');
            if (!$icon.hasClass('selected')) {
                Events.fire('add_to_general', listId, function() {
                    $icon.addClass('selected');
                    List.refresh(function() {
                        List.select($list.find('.item.selected').data('id'));
                    });
                });
            } else {
                $icon.removeClass('selected');
                Events.fire('remove_from_general', listId, function() {
                    $icon.removeClass('selected');
                    List.refresh(function() {
                        List.select($list.find('.item.selected').data('id'));
                    });
                });
            }
        });
    }
    function listRefresh(callback) {
        var $selectedItem = $list.find('.item.selected');
        var id = $selectedItem.data('id');
        var $list_global  =  $('> .list.global', $container);
        var $list_private =  $('> .list.private', $container);
        var $list_shared  =  $('> .list.shared', $container);
        Events.fire('load_list', function(data) {
            $list_global.html(tmpl(FILTER_LIST, {items: data.global_list}));
            $list_private.html(tmpl(FILTER_LIST, {items: data.private_list}));
            $list_shared.html(tmpl(FILTER_LIST, {items: data.shared_list}));
            if (id) {
                listSelect(id, function() {
                    if ($.isFunction(callback)) callback();
                });
            } else if ($.isFunction(callback)) callback();
        });
    }
    function listSelect(id, callback) {
        var $item = $list.find('.item[data-id=' + id + ']');
        $list.find('.item.selected').removeClass('selected');
        $item.addClass('selected');

        if ($.isFunction(callback)) callback();
        else {
            List.select($item.data('id'));
            Table.changeList($item.data('id'));
        }
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

    function showInterval() {
        $audienceWrapper.slideUp(400);
        $periodWrapper.slideUp(400);
        $intervalWrapper.slideDown(200);
    }
    function hideInterval() {
        $audienceWrapper.slideDown(200);
        $periodWrapper.slideDown(200);
        $intervalWrapper.slideUp(400);
    }

    return {
        init: init,
        listRefresh: listRefresh,
        listSelect: listSelect,
        setSliderMin: setSliderMin,
        setSliderMax: setSliderMax,
        showInterval: showInterval,
        hideInterval: hideInterval
    };
})();

var Table = (function() {
    var $container;
    var idEditMode = false;
    var dataTable = {};
    var pagesLoaded = 0;
    var currentListId = 0;
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
        if ($.isFunction(callback)) callback();
    }
    function loadMore() {
        var $el = $("#load-more-table");
        if ($el.hasClass('loading')) return;
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
                if ($.isFunction(callback)) callback(data);
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
                if (!listType) {
                    $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                } else {
                    $tableBody.html(tmpl(OUR_TABLE_BODY, {rows: dataTable}));
                }
                if (dataTable.length < Configs.tableLoadOffset) {
                    $('#load-more-table').hide();
                } else {
                    $('#load-more-table').show();
                }
                if ($.isFunction(callback)) callback(data);
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
                if ($.isFunction(callback)) callback(data);
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
                if ($.isFunction(callback)) callback(data);
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
                if ($.isFunction(callback)) callback(data);
            }
        );
    }
    function changeList(listId) {
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
            timeTo: currentInterval[1]
        };

        Events.fire('load_table', params,
            function(data, maxPeriod, listType) {
                pagesLoaded = 1;
                currentListType = listType;
                currentListId = listId;
                currentSearch = defSearch;
                currentSortBy = defSortBy;
                currentSortReverse = defSortReverse;
                dataTable = data;
                if (!listType) {
                    $container.html(tmpl(TABLE, {rows: data}));
                    Filter.hideInterval();
                } else {
                    $container.html(tmpl(OUR_TABLE, {rows: data}));
                    Filter.showInterval();
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
                $('#global-loader').fadeOut(200);
            }
        );
    }

    function _initEvents() {
        $container.delegate('.action.add-to-list', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            var publicData;

            for (var i in dataTable) {
                if (dataTable[i].intId == publicId) {
                    publicData = dataTable[i];
                    break; // ------------------- BREAK
                }
            }
            if (publicData) {
                _createDropdownList(e, publicData);
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
            posts: '.posts',
            postsPerDay: '.posts-per-day',
            authorsPosts: '.authors-posts',
            authorsLikes: '.authors-likes',
            authorsReposts: '.authors-reposts',
            sbPosts: '.sb-posts',
            sbLikes: '.sb-likes',
            growthVisitors: '.growth-visitors'
        };

        $.each(sortFields, function(sortFieldKey, sortFieldSelector) {
            $container.delegate(sortFieldSelector, 'click', function() {
                var $target = $(this);
                $target.closest('.list-head').find('.column').not($target).removeClass('reverse active');
                if ($target.hasClass('active') && !$target.hasClass('reverse')) {
                    $target.addClass('reverse');
                    sort(sortFieldKey, true);
                } else {
                    $target.addClass('active');
                    $target.removeClass('reverse');
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
            var b = $("#load-more-table");
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

    function _createDropdownList(e, publicData) {
        var $el = $(e.currentTarget);
        var offset = $el.offset();
        var $dropdown = $el.data('dropdown');
        var $public = $el.closest('.public');
        var publicId = $public.data('id');
        var selectedLists = publicData.lists;
        var listId = null;

        e.stopPropagation();

        if (!$dropdown) {
            Events.fire('load_list', function(dataList) {
                if (!$el.hasClass('selected')) {
                    var all_lists = dataList.private_list;
                    all_lists.push.apply(all_lists,dataList.global_list);

                    $dropdown = $(tmpl(DROPDOWN, {items: all_lists})).appendTo('body');
                    var $input = $dropdown.find('input');

                    $.each(selectedLists, function(i, listId) {
                        $dropdown.find('[data-id=' + listId + ']').addClass('selected');
                    });

                    $dropdown.delegate('.show-input', 'click', function() {
                        $input.show().focus();
                    });
                    $dropdown.delegate('.item:not(.show-input)', 'mousedown', function(e) {
                        var $item = $(this);
                        onChange($item);
                    });
                    $dropdown.delegate('input', 'keyup blur', function(e) {
                        var text = $.trim($input.val());
                        if (e.keyCode && e.keyCode != KEY.ENTER) return false;
                        if (!text) return false;
                        if (e.keyCode == KEY.ENTER) return $input.blur();
                        return onSave(text);
                    });
                    $dropdown.bind('mousedown', function(e) {
                        e.stopPropagation();
                    });
                    $(document).mousedown(function() {
                        if ($dropdown.is(':hidden')) return;
                        $dropdown.hide();
                        $el.removeClass('selected');
                    });

                    function onSave(text) {
                        Events.fire('add_list', text, function() {
                            Events.fire('load_list', function(dataList) {
                                $el.data('dropdown', false);
                                var all_lists = dataList.private_list;
                                all_lists.push.apply(all_lists,dataList.global_list);

                                var $tmpDropdown = $(tmpl(DROPDOWN, {items: all_lists}));
                                $dropdown.html($tmpDropdown.html());
                                $input = $dropdown.find('input');
                                Filter.listRefresh();
                            });
                        });
                    }
                    function onChange($item) {
                        listId = $item.data('id');
                        var isSelected = !$item.hasClass('selected');
                        var callback = function(data) {
                            if (!data) return;
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
                    $el.addClass('selected');
                    $el.data('dropdown', $dropdown);
                    $dropdown.show().css({
                        top: offset.top + $el.outerHeight(),
                        left: offset.left - $dropdown.outerWidth() + $el.outerWidth()
                    });
                }
            });
        } else {
            $el.addClass('selected');
            $dropdown.show().css({
                top: offset.top + $el.outerHeight(),
                left: offset.left - $dropdown.outerWidth() + $el.outerWidth()
            });
        }
    }

    function toggleEditMode() {
        editMode(!idEditMode);
    }

    function editMode(on) {
        idEditMode = on;
        var $list = $container.find('.list-body');
        if (on) {
            $list.addClass('edit-mode');
        } else {
            $list.removeClass('edit-mode');
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
        editMode: editMode,
        toggleEditMode: toggleEditMode,
        setInterval: setInterval,
        setCurrentInterval: setCurrentInterval
    };
})();

var Counter = (function(){
    var $container;

    function init( callback ){
        $container = $('#listed-counter');
        if( cur.dataUser.isEditor ) {
            $container.show();
        }
        refresh();
    }

    function refresh(){
       $container.find('span').text( cur.dataUser.listed );
    }

    return {
        init: init,
        refresh: refresh
    };
})();