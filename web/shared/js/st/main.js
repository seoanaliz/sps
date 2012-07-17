/**
 * Initialization
 */
var cur = {
    dataUser: {},
    dataTable: {},
    dataAllTable: {},
    wallLoaded: null, // Сколько страниц загружено
    selectedListId: null, // Выбраный список

    etc: null
};

var Configs = {
    appId: vk_appId,
    maxRows: 10000,
    tableLoadOffset: 20,
    controlsRoot: controlsRoot,
    eventsDelay: 0,

    etc: null
};

$(document).ready(function() {
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
    var pagesLoaded = 0;

    function init() {
        $container = $('#table');
        _initEvents();
        changeList();
    }
    function loadMore() {
        var $el = $("#load-more-table");
        var $tableBody = $('#table-body');
        if ($el.hasClass('loading')) return;

        $el.addClass('loading');
        Events.fire('load_table', {
                listId: currentListId,
                offset: pagesLoaded * Configs.tableLoadOffset,
                limit: Configs.tableLoadOffset
            },
            function(data) {
                pagesLoaded += 1;
                if (data.length) {
                    dataTable = $.merge(dataTable, data);
                    $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                    $el.removeClass('loading');
                } else {
                    $el.removeClass('loading');
                }
                if ($.isFunction(callback)) callback();
            }
        );
    }
    function sort(field, reverse, callback) {
        var $tableBody = $('#table-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                sortBy: field,
                sortReverse: reverse
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
            }
        );
    }
    function search(text, callback) {
        var $tableBody = $('#table-body');

        Events.fire('load_table', {
                listId: currentListId,
                limit: Configs.tableLoadOffset,
                search: text
            },
            function(data) {
                pagesLoaded = 1;
                dataTable = data;
                $tableBody.html(tmpl(TABLE_BODY, {rows: dataTable}));
                if ($.isFunction(callback)) callback(data);
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
                $('#global-loader').fadeOut(200);
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
            createDropdownContact(e, publicData)
        });
        $container.delegate('.action.add-to-list', 'click', function(e) {
            var $el = $(this);
            var $public = $el.closest('.public');
            var publicId = $public.data('id');
            var publicData;
            for (var i in dataTable) {
                if (dataTable[i].publicId == publicId) { publicData = dataTable[i]; break; }
            }
            createDropdownList(e, publicData);
        });

        $container.delegate('.followers', 'click', function(e) {
            var $target = $(this);
            $target.closest('thead').find('th').not($target).removeClass('reverse active');
            if ($target.hasClass('reverse')) {
                $target.removeClass('reverse');
                $target.removeClass('active');
            } else if ($target.hasClass('active')) {
                $target.addClass('reverse');
            } else {
                $target.addClass('active');
            }
            var isReverse = $target.hasClass('reverse');
            sort('followers', isReverse);
        });

        $container.delegate('.growth', 'click', function(e) {
            var $target = $(this);
            $target.closest('thead').find('th').not($target).removeClass('reverse active');
            if ($target.hasClass('reverse')) {
                $target.removeClass('reverse active');
            } else if ($target.hasClass('active')) {
                $target.addClass('reverse');
            } else {
                $target.addClass('active');
            }
            var isReverse = $target.hasClass('reverse');
            sort('growth', isReverse);
        });

        $container.delegate('.contacts', 'click', function(e) {
            var $target = $(this);
            $target.closest('thead').find('th').not($target).removeClass('reverse active');
            if ($target.hasClass('reverse')) {
                $target.removeClass('reverse active');
            } else if ($target.hasClass('active')) {
                $target.addClass('reverse');
            } else {
                $target.addClass('active');
            }
            var isReverse = $target.hasClass('reverse');
            sort('contacts', isReverse);
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
                        $filter.closest('thead').find('th').removeClass('reverse active');
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

    return {
        init: init,
        changeList: changeList,
        loadMore: loadMore,
        sort: sort,
        search: search
    };
})();

function createDropdownContact(e, publicData) {
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
                users.sort(function(a, b) {
                    if (a == user) return -1;
                    else return 1;
                });
                $contact.css('opacity', .5);
                Events.fire('change_user', userId, cur.selectedListId, publicId, function(data) {
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

function createDropdownList(e, publicData) {
    var $el = $(e.currentTarget);
    var offset = $el.offset();
    var $dropdown = $el.data('dropdown');
    var $public = $el.closest('.public');
    var publicId = $public.data('id');
    var selectedLists = publicData.lists;
    console.log(publicData.lists);
    var listId = null;

    e.stopPropagation();

    if (!$dropdown) {
        Events.fire('load_list', function(dataList) {
            if (!$el.hasClass('selected')) {
                var lists = dataList;
                $dropdown = $(tmpl(DROPDOWN, {items: lists})).appendTo('body');
                var $input = $dropdown.find('input');
                var $showInput = $dropdown.find('.show-input');

                $.each(selectedLists, function(i, listId) {
                    $dropdown.find('[data-id=' + listId + ']').addClass('selected');
                });

                $showInput.bind('click', function() {
                    $input.show().focus();
                });
                $dropdown.delegate('.item:not(.show-input)', 'mousedown', function(e) {
                    var $item = $(this);
                    $item.toggleClass('selected');
                    onChange($item);
                });
                $dropdown.bind('mousedown', function(e) {
                    e.stopPropagation();
                });
                $(document).mousedown(function() {
                    if ($dropdown.is(':hidden')) return;
                    $dropdown.hide();
                    $el.removeClass('selected');
                });
                $input.bind('keyup blur', function(e) {
                    var text = $.trim($input.val());
                    if (e.keyCode && e.keyCode != 13) return false;
                    if (!text) return false;
                    if (e.keyCode == 13) return $input.blur();
                    return onSave(text);
                });

                function onSave(text) {
                    Events.fire('add_list', text, function(data) {
                        Events.fire('load_list', function(dataList) {
                            $el.data('dropdown', false);
                            $(document).mousedown();
                            List.refresh();
                        });
                    });
                }
                function onChange($item) {
                    listId = $item.data('id');
                    var isSelected = $item.hasClass('selected');
                    var callback = function(data) {
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
