/**
 * Initialization
 */
$(document).ready(function() {
    VK.init({
        apiId: vk_appId,
        nameTransportPath: '/xd_receiver.htm'
    });
    getInitData();

    function getInitData() {
        var code;
        code = 'return {';
        code += 'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]';
        code += '};';
        VK.Api.call('execute', {'code': code}, onGetInitData);
    }

    function onGetInitData(data) {
        var r;
        if (data.response) {
            r = data.response;
            DataUser = r.me;
            Events.fire('load_list', DataUser.uid, function(dataList) {
                var selectedList;
                for (var i in dataList) {
                    if (dataList[i].itemSelected) {
                        selectedList = dataList[i].itemId; break;
                    }
                }
                Events.fire('load_table', DataUser.uid, selectedList, 0, 100, function(dataTable) {
                    try {
                        updateList(dataList);
                        updateTable(dataTable);
                        $('#global-loader').fadeOut(200);
                    } catch(e) {
                        $('#global-loader')
                            .css('background-image', 'none')
                            .html('Error: "' + e.arguments[0] + '" ' + e.type);
                        throw e;
                    }
                });
            });
        }
    }
});

function updateTable(dataDef) {
    if (!dataDef.length) return;

    var dataTable = dataDef.slice(0);
    var $table;
    var $tableHead;
    var $tableBody;
    var $filter;

    updateElements();
    renderTable({rows: dataTable});

    $table.delegate('.contact .content', 'click', function(e) {
        var $el = $(this);
        var offset = $el.offset();
        var $dropdown = $el.data('dropdown');
        e.stopPropagation();

        if (!$el.hasClass('selected')) {
            $el.addClass('selected');
            if (!$dropdown) {
                var dataItems = DataUsers;
                $dropdown = $(tmpl(CONTACT_DROPDOWN, {users: dataItems})).appendTo('body');

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
                    var $contact = $el.closest('.contact');
                    $contact.css('opacity', .5);
                    Events.fire('change_user', DataUser.uid, $item.data('user-id'), 123, 123, function(data) {
                        $contact
                            .html(tmpl(CONTACT, DataUsers[$item.data('user-id')-1]))
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
        e.stopPropagation();

        Events.fire('load_list', DataUser.uid, function(dataList) {
            if (!$el.hasClass('selected')) {
                $el.addClass('selected');
                if (!$dropdown) {
                    var dataItems = dataList;
                    $dropdown = $(tmpl(DROPDOWN, {items: dataItems})).appendTo('body');
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
                        Events.fire('add_list', DataUser.uid, 123, text, function(data) {
                            $input.hide();
                            $input.val('').before(tmpl(DROPDOWN_ITEM, {itemId: 1, itemTitle: text}));
                        });
                    }
                    function onChange($item) {
                        $el.find('.icon').addClass('select');
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
            return a.user['userName'].toLowerCase();
        });
    });

    $filter.keyup(function() {
        dataTable = $.grep(dataDef, function(n) {
            return n.publicName.toLowerCase().indexOf($filter.val().toLowerCase()) != -1;
        });
        $tableHead.find('th.active').removeClass('active');
        renderTableBody({rows: dataTable});
    });

    function sortTable(target, index) {
        var rows;
        var reverse = -1;
        var $target = $(target);
        var parse = $.isFunction(index) ? index : function(a) {
            return parseInt(a[index].split(' ').join(''));
        };
        var sort = function(first, second) {
            var a = parse(first);
            var b = parse(second);
            return (a > b) ? reverse : ((a < b) ? -reverse : 0);
        };

        $tableHead.find('th.active').not($target).removeClass('active');
        if (!$target.hasClass('active')) {
            $target.addClass('active');
            rows = dataTable.slice(0).sort(sort);
        } else if (!$target.hasClass('reverse')) {
            $target.addClass('reverse');
            reverse = -reverse;
            rows = dataTable.slice(0).sort(sort);
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

    $('.header').empty().html(tmpl(LIST, {items: dataList}));
    $('.header').delegate('.tab', 'click', function() {
        $('.header .tab').removeClass('selected');
        var $item = $(this);
        $item.addClass('selected');

        Events.fire('load_table', DataUser.uid, $item.data('id'), 0, 100, function(dataTable) {
            updateTable(dataTable);
        });
    });
}
