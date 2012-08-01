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
    dataUser: {}
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

    VK.init({
        apiId: Configs.appId,
        nameTransportPath: '/xd_receiver.htm'
    });
    getInitData();

    function getInitData() {
        var code =
            'return {' +
                'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]' +
            '};';
        VK.Api.call('execute', {code: code}, initVK);
    }
});

function initVK(data) {
    if (data.response) {
        var r = data.response;
        cur.dataUser = r.me;

        Filter.init(function() {
            List.init(function() {
                Table.init();
            });
        });
    }
}

var List = (function() {
    var $container;
    var $actions;

    function init(callback) {
        $container = $('.header');

        refresh(function() {
            $actions = $('.actions', $container);
            _initEvents();

            if ($.isFunction(callback)) callback();
        });
    }
    function _initEvents() {
        $container.delegate('.tab', 'click', function() {
            var $item = $(this);
            select($item.data('id'));
        });

        $actions.delegate('.share', 'click', function() {
            var listId = $('.filter > .list > .item.selected').data('id');
            var isFirstShow = true;
            var box = new Box({
                id: 'share' + listId,
                title: 'Поделиться',
                html: tmpl(BOX_SHARE),
                buttons: [
                    {label: 'Отправить', onclick: share},
                    {label: 'Отменить', isWhite: true}
                ],
                onshow: function($box) {
                    var box = this;
                    var $users = $box.find('.users');
                    var $lists = $box.find('.lists');
                    var $comment = $box.find('.comment');
                    if (isFirstShow) {
                        VK.Api.call('friends.get', {fields: 'first_name, last_name, photo'}, function(data) {
                            if (data.error) {
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
                                var res = data.response;
                                var html = '';
                                for (var i in res) {
                                    var user = res[i];
                                    html += user.first_name + ' ' + user.last_name + ', ';
                                }
                                //box.setHTML(html).setButtons();
                            }
                        });
                        isFirstShow = false;
                        $comment.autoResize();
                        $users
                            .textext({
                                plugins : 'tags autocomplete filter',
                                ext: {
                                    itemManager: {
                                        stringToItem: function(str) { return {name: str}; },
                                        itemToString: function(item) { return item.name; },
                                        compareItems: function(item1, item2) { return item1.name == item2.name; }
                                    }
                                }
                            })
                            .bind('getSuggestions', function(e, data) {
                                var list = [{id: 1, name: 'Артем'}, {id: 2, name: 'Вова'}],
                                    textext = $(e.target).textext()[0],
                                    query = (data ? data.query : '') || '';
                                $(this).trigger('setSuggestions', {result: textext.itemManager().filter(list, query)});
                            })
                            .bind('setFormData', function(e, data) {
                                $(e.target).data('tags', data);
                            })
                        ;
                        $users.textext()[0].getFormData();
                        $lists
                            .textext({
                                plugins : 'tags autocomplete filter',
                                tagsItems: [{name: 'asadfdsgdfhretq'}],
                                ext: {
                                    itemManager: {
                                        stringToItem: function(str) { return {name: str}; },
                                        itemToString: function(item) { return item.name; },
                                        compareItems: function(item1, item2) { return item1.name == item2.name; }
                                    }
                                }
                            })
                            .bind('getSuggestions', function(e, data) {
                                var list = [{name: 'asadfdsgdfhretq'}, {name: 'asadfdsgdfhretq'}, {name: 'asadfdsgdfhretq'}],
                                    textext = $(e.target).textext()[0],
                                    query = (data ? data.query : '') || '';
                                $(this).trigger('setSuggestions', {result: textext.itemManager().filter(list, query)});
                            })
                            .bind('setFormData', function(e, data) {
                                $(e.target).data('tags', data);
                            })
                        ;
                        $lists.textext()[0].getFormData();
                    }
                    $box.find('textarea[value=""]:first').focus();
                }
            }).show();

            function share($button, $box) {
                console.log($box.find('.users').data('tags'));
                console.log($box.find('.lists').data('tags'));
            }
        });
        $actions.delegate('.edit', 'click', function() {
            var listId = $('.filter > .list > .item.selected').data('id');
            //Table.editMode(true);
            $('.table').toggleClass('edit-mode');
        });
        $actions.delegate('.delete', 'click', function() {
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
                    List.refresh(function() {
                        Filter.listRefresh(function() {
                            $('.filter > .list > .item[data-id="null"]').click();
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
            $container.html(tmpl(LIST, {items: data}));
            if (id) {
                select(id, function() {
                    if ($.isFunction(callback)) callback();
                });
            } else if ($.isFunction(callback)) callback();
        });
    }

    function select(id, callback) {
        var $item = $container.find('.tab[data-id=' + id + ']');
        $container.find('.tab.selected').removeClass('selected');
        $item.addClass('selected');
        if ($.isFunction(callback)) callback();
        else {
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
    var $audience;
    var $period;
    var $list;

    function init(callback) {
        $container = $('td.filter');
        $audience = $('> .audience', $container);
        $period = $('> .period', $container);
        $list = $('> .list', $container);

        _initEvents();
        listRefresh(callback);
    }

    function _initEvents() {
        (function() {
            var $slider = $audience.find('> .slider-wrap');
            var $sliderRange = $audience.find('> .slider-range');
            $slider.slider({
                range: true,
                min: 0,
                max: 100,
                animate: 100,
                values: [0, 100],
                create: function(event, ui) {
                    renderRange();
                },
                slide: function(event, ui) {
                    renderRange();
                },
                change: function(event, ui) {
                    renderRange();
                    changeRange();
                }
            });
            function renderRange() {
                var audience = [
                    $slider.slider('values', 0),
                    $slider.slider('values', 1)
                ];
                $sliderRange.html(audience[0] + ' - ' + audience[1]);
            }
            function changeRange() {
                var audience = [
                    $slider.slider('values', 0),
                    $slider.slider('values', 1)
                ];
                Table.setAudience(audience);
            }
        })();
        $period.delegate('input', 'change', function() {
            var $input = $(this);
            Table.setPeriod($input.val());
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
                Events.fire('add_to_bookmark', listId, function() {
                    $icon.addClass('selected');
                    List.refresh();
                });
            } else {
                $icon.removeClass('selected');
                Events.fire('remove_from_bookmark', listId, function() {
                    $icon.removeClass('selected');
                    List.refresh();
                });
            }
        });
    }
    function listRefresh(callback) {
        var $selectedItem = $list.find('.item.selected');
        var id = $selectedItem.data('id');
        Events.fire('load_list', function(data) {
            $list.html(tmpl(FILTER_LIST, {items: data}));
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
            List.select($item.data('id'), function() {
                Table.changeList($item.data('id'));
            });
        }
    }

    return {
        init: init,
        listRefresh: listRefresh,
        listSelect: listSelect
    };
})();

var Table = (function() {
    var $container;
    var dataTable = {};
    var pagesLoaded = 0;
    var currentListId = 0;
    var currentSearch = '';
    var currentSortBy = '';
    var currentSortReverse = false;
    var currentPeriod = 'day';
    var currentAudience = [];

    function init(callback) {
        $container = $('#table');
        _initEvents();
        changeList();
        if ($.isFunction(callback)) callback();
    }
    function loadMore() {
        var $el = $("#load-more-table");
        var $tableBody = $('.list-body');
        if ($el.hasClass('loading')) return;

        $el.addClass('loading');
        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                offset: pagesLoaded * Configs.tableLoadOffset,
                search: currentSearch,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse,
                period: currentPeriod,
                audienceMin: currentAudience[0],
                audienceMax: currentAudience[1]
            },
            function(data) {
                pagesLoaded += 1;
                if (data.length) {
                    dataTable = $.merge(dataTable, data);
                    $tableBody.append(tmpl(TABLE_BODY, {rows: data}));
                    $el.removeClass('loading');
                } else {
                    $el.removeClass('loading');
                }
            }
        );
    }
    function sort(field, reverse, callback) {
        var $tableBody = $('.list-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                search: currentSearch,
                sortBy: field,
                sortReverse: reverse,
                period: currentPeriod,
                audienceMin: currentAudience[0],
                audienceMax: currentAudience[1]
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentSortBy = field;
                currentSortReverse = reverse;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
            }
        );
    }
    function search(text, callback) {
        var $tableBody = $('.list-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                search: text,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse,
                period: currentPeriod,
                audienceMin: currentAudience[0],
                audienceMax: currentAudience[1]
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentSearch = text;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
                if (dataTable.length < Configs.tableLoadOffset) {
                    $('#load-more-table').hide();
                } else {
                    $('#load-more-table').show();
                }
            }
        );
    }
    function setPeriod(period, callback) {
        var $tableBody = $('.list-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                search: currentSearch,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse,
                period: period,
                audienceMin: currentAudience[0],
                audienceMax: currentAudience[1]
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentPeriod = period;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
            }
        );
    }
    function setAudience(audience, callback) {
        var $tableBody = $('.list-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                search: currentSearch,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse,
                period: currentPeriod,
                audienceMin: audience[0],
                audienceMax: audience[1]
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentAudience = audience;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
            }
        );
    }
    function changeList(listId) {
        var newSearch = '';
        var newSortBy = 'growth';
        var newSortReverse = false;

        Events.fire('load_table', {
                listId: listId,
                limit: Configs.tableLoadOffset,
                search: newSearch,
                sortBy: newSortBy,
                sortReverse: newSortReverse,
                period: currentPeriod,
                audienceMin: currentAudience[0],
                audienceMax: currentAudience[1]
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentListId = listId;
                currentSearch = newSearch;
                currentSortBy = newSortBy;
                currentSortReverse = newSortReverse;
                $container.html(tmpl(TABLE, {rows: data}));
                $container.find('.' + currentSortBy).addClass('active');
                $('#global-loader').fadeOut(200);
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
            }
        );
    }

    function _initEvents() {
        $container.delegate('.contact', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            var publicData;
            for (var i in dataTable) {
                if (dataTable[i].publicId == publicId) { publicData = dataTable[i]; break; }
            }
            _createDropdownContact(e, publicData);
        });
        $container.delegate('.action.add-to-list', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            var publicData;
            for (var i in dataTable) {
                if (dataTable[i].publicId == publicId) { publicData = dataTable[i]; break; }
            }
            _createDropdownList(e, publicData);
        });

        $container.delegate('.followers', 'click', function(e) {
            var $target = $(this);
            $target.closest('.list-head').find('.item').not($target).removeClass('reverse active');
            if ($target.hasClass('active') && !$target.hasClass('reverse')) {
                $target.addClass('reverse');
                sort('followers', true);
            } else {
                $target.addClass('active');
                $target.removeClass('reverse');
                sort('followers', false);
            }
        });

        $container.delegate('.growth', 'click', function(e) {
            var $target = $(this);
            $target.closest('.list-head').find('.item').not($target).removeClass('reverse active');
            if ($target.hasClass('active') && !$target.hasClass('reverse')) {
                $target.addClass('reverse');
                sort('growth', true);
            } else {
                $target.addClass('active');
                $target.removeClass('reverse');
                sort('growth', false);
            }
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

    function _createDropdownContact(e, publicData) {
        var $el = $(e.currentTarget);
        var offset = $el.offset();
        var $dropdown = $el.data('dropdown');
        var $public = $el.closest('.public');
        var publicId = $public.data('id');
        var users = publicData.users;

        e.stopPropagation();

        if (!$el.hasClass('selected')) {
            $el.addClass('selected');
            if (!$dropdown) {
                $dropdown = $(tmpl(CONTACT_DROPDOWN, {users: users})).appendTo('body');

                $dropdown.delegate('.item', 'mousedown', function(e) {
                    if ($(e.target).is('a')) return false;
                    var $item = $(this);
                    $dropdown.find('.item.selected').removeClass('selected');
                    $item.addClass('selected');
                    onChange($item);
                });
                $(document).mousedown(function() {
                    if ($dropdown.is(':hidden')) return;
                    $dropdown.hide();
                    $el.removeClass('selected');
                });

                function onChange($item) {
                    var userId = $item.data('user-id');
                    var $contact = $el.closest('.contact');
                    var user;
                    for (var i in users) {
                        if (users[i].userId == userId) { user = users[i]; break; }
                    }
                    $contact.css('opacity', .5);
                    Events.fire('change_user', userId, currentListId, publicId, function(data) {
                        if (!data) return;
                        $contact
                            .html(tmpl(CONTACT, user))
                            .animate({opacity: 1}, 100)
                        ;
                    });
                }
                $el.data('dropdown', $dropdown);
            }
            $dropdown.show().css({
                top: offset.top + $el.outerHeight(),
                left: offset.left - 1,
                width: $el.outerWidth()
            });
        }
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
                    var lists = dataList;
                    $dropdown = $(tmpl(DROPDOWN, {items: lists})).appendTo('body');
                    var $input = $dropdown.find('input');

                    $.each(selectedLists, function(i, listId) {
                        $dropdown.find('[data-id=' + listId + ']').addClass('selected');
                    });

                    $dropdown.delegate('.show-input', 'click', function() {
                        $input.show().focus();
                    });
                    $dropdown.delegate('.hide-public', 'click', function() {
                        var $item = $(this);
                        if ($item.hasClass('selected')) {
                            Events.fire('hide_public', publicId, function() {
                                $item.removeClass('selected');
                                $public.css('opacity', 1);
                            });
                        } else {
                            Events.fire('hide_public', publicId, function() {
                                $item.addClass('selected');
                                $public.css('opacity',.5);
                            });
                        }
                    });
                    $dropdown.delegate('.item:not(.show-input):not(.hide-public)', 'mousedown', function(e) {
                        var $item = $(this);
                        onChange($item);
                    });
                    $dropdown.delegate('input', 'keyup blur', function(e) {
                        var text = $.trim($input.val());
                        if (e.keyCode && e.keyCode != 13) return false;
                        if (!text) return false;
                        if (e.keyCode == 13) return $input.blur();
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
                        Events.fire('add_list', text, function(data) {
                            Events.fire('load_list', function(dataList) {
                                $el.data('dropdown', false);
                                var $tmpDropdown = $(tmpl(DROPDOWN, {items: dataList}));
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

    return {
        init: init,
        changeList: changeList,
        loadMore: loadMore,
        sort: sort,
        search: search,
        setPeriod: setPeriod,
        setAudience: setAudience
    };
})();