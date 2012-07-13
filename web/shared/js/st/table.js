$(document).ready(function() {
    (function(){
        var w = $(window),
            b = $("#load-more-table");

        b.click(function() {
            cur.wallLoaded += 20;
            var newDataTable = $.merge(cur.dataTable, cur.dataAllTable.slice(cur.wallLoaded, cur.wallLoaded + 20));
            if (newDataTable.length == cur.dataAllTable.length) {
                $("#load-more-table").hide();
            } else {
                updateTable(newDataTable);
            }

            /*
             if (b.hasClass('loading')) return;
             b.addClass('loading');
             Events.fire('load_table', cur.selectedListId, cur.wallLoaded, 20, function(data) {
             cur.wallLoaded += 20;
             updateTable($.merge(cur.dataTable, data));
             b.removeClass('loading');
             });
             */
        });

        w.scroll(function() {
            if (b.is(':visible') && w.scrollTop() > (b.offset().top - w.outerHeight(true) - w.height())) {
                b.click();
            }
        });
    })();
});

function updateTable(dataDef) {
    if (!$.isArray(dataDef)) return;

    var dataTable = cur.dataTable = dataDef.slice(0);
    var $table;
    var $tableHead;
    var $tableBody;
    var $filter;

    Events.fire('load_table', cur.selectedListId, 0, 9999, function(dataAllTable) {
        cur.dataAllTable = dataAllTable;
        if (cur.dataAllTable.length != dataTable.length) {
            $("#load-more-table").show();
        } else {
            $("#load-more-table").hide();
        }
    });

    updateElements();
    renderTable({rows: dataTable});

    $table.delegate('.contact .content', 'click', function(e) {
        var $el = $(this);
        var offset = $el.offset();
        var $dropdown = $el.data('dropdown');
        var $public = $el.closest('.public');
        var publicId = $public.data('id');
        var publicData;
        for (var i in dataTable) {
            if (dataTable[i].publicId == publicId) { publicData = dataTable[i]; break; }
        }

        e.stopPropagation();

        if (!$el.hasClass('selected')) {
            $el.addClass('selected');
            if (!$dropdown) {
                var users = publicData.users;
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
    });

    $table.delegate('.action.add-to-list', 'click', function(e) {
        var $el = $(this);
        var offset = $el.offset();
        var $dropdown = $el.data('dropdown');
        var $public = $el.closest('.public');
        var publicId = $public.data('id');
        var listId = null;

        e.stopPropagation();

        Events.fire('load_list', function(dataList) {
            if (!$el.hasClass('selected')) {
                $el.addClass('selected');
                if (!$dropdown) {
                    var lists = dataList;
                    $dropdown = $(tmpl(DROPDOWN, {items: lists})).appendTo('body');
                    var $input = $dropdown.find('input');
                    var $showInput = $dropdown.find('.show-input');

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
                            $input.hide().val('').before(tmpl(DROPDOWN_ITEM, {itemId: 1, itemTitle: text}));
                            Events.fire('load_list', function(dataList) {
                                updateList(dataList);
                            });
                        });
                    }
                    function onChange($item) {
                        listId = $item.data('id');
                        Events.fire('add_to_list', publicId, listId, function(data) {
                            if ($dropdown.find('.item.selected').length) {
                                $el.find('.icon').removeClass('plus').addClass('select');
                            } else {
                                $el.find('.icon').removeClass('select').addClass('plus');
                            }
                        });
                    }
                    $el.data('dropdown', $dropdown);
                }
                $dropdown.show().css({
                    top: offset.top + $el.outerHeight(),
                    left: offset.left - $dropdown.outerWidth() + $el.outerWidth()
                });
            }
        });
    });

    $('.followers').click(function() {
        sortTable(this, 'publicFollowers');
    });

    $('.growth').click(function() {
        sortTable(this, 'publicGrowthNum');
    });

    $('.contacts').click(function() {
        sortTable(this, function(a) {
            if (a.users[0]) return a.users[0]['userName'].toLowerCase();
        });
    });

    $filter.keyup(function() {
        dataTable = $.grep(cur.dataAllTable, function(n) {
            return n.publicName.toLowerCase().indexOf($filter.val().toLowerCase()) != -1;
        });
        $tableHead.find('th.active').removeClass('active');
        renderTableBody({rows: dataTable.slice(0, 20)});
    });

    function sortTable(target, index) {
        var rows;
        var reverse = -1;
        var $target = $(target);
        var parse = $.isFunction(index) ? index : function(a) {
            return parseFloat(a[index].toString().split(' ').join(''));
        };
        var sort = function(first, second) {
            var a = parse(first);
            var b = parse(second);
            return (a > b) ? reverse : ((a < b) ? -reverse : 0);
        };

        $tableHead.find('th.active').not($target).removeClass('active reverse');
        if (!$target.hasClass('active')) {
            $target.addClass('active');
            rows = cur.dataAllTable.slice(0).sort(sort);
        } else if (!$target.hasClass('reverse')) {
            $target.addClass('reverse');
            reverse = -reverse;
            rows = cur.dataAllTable.slice(0).sort(sort);
        } else {
            $target.removeClass('active');
            $target.removeClass('reverse');
            rows = dataTable;
        }
        renderTableBody({rows: rows});
    }

    function updateElements() {
        $table = $('#table');
        $tableHead = $('#table-head');
        $tableBody = $('#table-body');
        $filter = $('#filter');
        $table.unbind();
        $filter.unbind();
    }

    function renderTable(data) {
        $table.empty().html(tmpl(TABLE, data));
        updateElements();
    }
    function renderTableBody(data) {
        $tableBody.empty().html(tmpl(TABLE_BODY, data));
    }
}

function updateList(dataDef) {
    if (!dataDef.length) return;

    var dataList = dataDef.slice(0);
    var timeout;

    $('.header').empty().html(tmpl(LIST, {items: dataList}));
    $('.header').delegate('.tab', 'click', function() {
        var $item = $(this);

        clearTimeout(timeout);
        timeout = setTimeout(function() {
            $('#global-loader').fadeIn(200);
        }, 2000);
        Events.fire('load_table', $item.data('id'), 0, 20, function(dataTable) {
            clearTimeout(timeout);
            $('#global-loader').fadeOut(200);
            cur.wallLoaded = 20;
            cur.selectedListId = $item.data('id');
            $item.closest('.header').find('.tab').removeClass('selected');
            $item.addClass('selected');
            updateTable(dataTable);
        });
    });
}

