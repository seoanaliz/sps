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

        List.init(function() {
            Table.init();
        });
    }
}

var List = (function() {
    var $container;

    function init(callback) {
        $container = $('.header');
        _initEvents();
        refresh(callback);
    }
    function refresh(callback) {
        Events.fire('load_list', function(data) {
            $container.html(tmpl(LIST, {items: data}));
            if ($.isFunction(callback)) callback();
        });
    }

    function _initEvents() {
        $container.delegate('.tab', 'click', function() {
            var $item = $(this);

            $container.find('.tab').removeClass('selected');
            $item.addClass('selected');

            Table.changeList($item.data('id'));
        });
    }

    return {
        init: init,
        refresh: refresh
    };
})();

var Table = (function(callback) {
    var $container;
    var dataTable = {};
    var currentListId = 0;
    var currentSearch = '';
    var currentSortBy = '';
    var currentSortReverse = '';
    var pagesLoaded = 0;

    function init() {
        $container = $('#table');
        _initEvents();
        changeList();
    }
    function loadMore() {
        var $el = $("#load-more-table");
        var $tableBody = $('.list-body');
        if ($el.hasClass('loading')) return;

        $el.addClass('loading');
        Events.fire('load_table', {
                listId: currentListId,
                search: currentSearch,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse,
                offset: pagesLoaded * Configs.tableLoadOffset,
                limit: Configs.tableLoadOffset
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
                if ($.isFunction(callback)) callback();
            }
        );
    }
    function sort(field, reverse, callback) {
        var $tableBody = $('.list-body');

        Events.fire('load_table', {
                listId: currentListId,
                search: currentSearch,
                limit: Configs.tableLoadOffset,
                sortBy: field,
                sortReverse: reverse
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
                search: text,
                limit: Configs.tableLoadOffset,
                sortBy: currentSortBy,
                sortReverse: currentSortReverse
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
    function changeList(listId) {
        Events.fire('load_table', {
                listId: listId,
                limit: Configs.tableLoadOffset
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                currentListId = listId;
                $container.html(tmpl(TABLE, {rows: data}));
                $container.find('.growth').addClass('active');
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
        $container.delegate('.contact .content', 'click', function(e) {
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

        $container.delegate('.contacts', 'click', function(e) {
            //todo: sort by contacts
            /*
            var $target = $(this);
            $target.closest('.list-head').find('.item').not($target).removeClass('reverse active');
            if ($target.hasClass('active') && !$target.hasClass('reverse')) {
                $target.addClass('reverse');
                sort('contacts', true);
            } else {
                $target.addClass('active');
                $target.removeClass('reverse');
                sort('contacts', false);
            }
            */
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
                        $filter.closest('.list-head').find('.item').removeClass('reverse active');
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
                    $dropdown.delegate('.item:not(.show-input)', 'mousedown', function(e) {
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
                                List.refresh();
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
        search: search
    };
})();

