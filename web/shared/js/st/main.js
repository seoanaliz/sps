/**
 * Events
 */
var Events = {
    url: controlsRoot,
    delay: 0,
    eventList: {},
    fire: function(name, args){
        var t = this;
        args = Array.prototype.slice.call(arguments, 1);
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    console.log(name + ':');
                    console.log(args.slice(0, -1));
                    console.log('-------');
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if(console && $.isFunction(console.log)) {
                    console.log(e);
                }
            }
        }
    }
};

/**
 * Test data
 */
var DataUser = {
    uid: 4718705,
    first_name: 'Artyom',
    last_name: 'Kohver'
};

var DataUsers = [ {
        userId: 1,
        userName: 'Asadasdfша Радославов',
        userPhoto: 'http://cs4852.userapi.com/u78467867/d_689784aa.jpg',
        userDescription: 'Сотрудничество'
    }, {
        userId: 2,
        userName: 'Artyom Kohver',
        userPhoto: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        userDescription: 'Сотрудничество'
    }, {
        userId: 3,
        userName: 'Коля',
        userPhoto: 'http://cs11368.userapi.com/u4373383/d_79f9e41f.jpg',
        userDescription: 'Сотрудничество'
    }, {
        userId: 4,
        userName: '123',
        userPhoto: 'http://app.uxpin.com/u/a/0/9/a09e2c748e10258cd892f11307202c77/e_d7f264dc.jpg',
        userDescription: 'Сотрудничество'
    }
];

var DataTable = [ {
        publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        publicName: 'Путешествия',
        publicFollowers: '516',
        publicGrowthNum: '1231',
        publicGrowthPer: '0.53%',
        users: DataUsers,

        etc: false
    }, {
        publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        publicName: 'Креативные идеи',
        publicFollowers: '123 456',
        publicGrowthNum: '231',
        publicGrowthPer: '0.53%',
        users: DataUsers,

        etc: false
    }, {
        publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        publicName: 'Тысяча чертей, какая',
        publicFollowers: '55 111',
        publicGrowthNum: '1',
        publicGrowthPer: '0.53%',
        users: DataUsers,

        etc: false
    }, {
        publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        publicName: 'Эротика',
        publicFollowers: '2 375',
        publicGrowthNum: '2231',
        publicGrowthPer: '0.53%',
        users: DataUsers,

        etc: false
    }, {
        publicImg: 'http://cs407727.userapi.com/v407727814/2b33/ui72j20Zn9w.jpg',
        publicName: 'Самые красивые девушк',
        publicFollowers: '71 375',
        publicGrowthNum: '1131',
        publicGrowthPer: '0.53%',
        users: DataUsers,

        etc: false
    }, {
        publicImg: 'http://app.uxpin.com/u/5/4/a/54a4c4259d2169e5bf80dfb906c03cf4/e_68ce31a0.jpg',
        publicName: 'Интересные факты',
        publicFollowers: '221 123',
        publicGrowthNum: '0',
        publicGrowthPer: '0%',
        users: DataUsers,

        etc: false
    }
];

var DataList = [
    {itemId: 1, itemTitle: 'Все', itemSelected: true},
    {itemId: 2, itemTitle: 'Популярные'},
    {itemId: 3, itemTitle: 'Прикольные'},
    {itemId: 4, itemTitle: 'Смелые'},
    {itemId: 5, itemTitle: 'Красивые'},
    {itemId: 6, itemTitle: 'Ужасные'}
];

/**
 * Initialization
 */
var Eventlist = {
    load_list: function(viewer_id, callback) {
//        $.ajax({
//            url: Events.url + 'arcticle-item/',
//            data: {
//                userId: viewer_id
//            },
//            success: function (data) {
//                callback(false);
//            }
//        });
        callback(false);
    },
    load_table: function(viewer_id, list_id, offset, limit, callback) {
        callback(false);
    },
    add_list: function(viewer_id, title, callback) {
        callback(false);
    },
    remove_list: function(viewer_id, list_id, callback) {
        callback(false);
    },
    change_user: function(viewer_id, user_id, public_id, callback) {
        callback(false);
    }
};
$.extend(Events.eventList, Eventlist);

$(document).ready(function() {
    $.ajax({
        url: Events.url + 'getGroupList/',
        data: {
            userId: 1
        },
        success: function (data) {
            callback(false);
        }
    });
    Events.fire('load_list', DataUser.uid, function(dataList) {
        Events.fire('load_table', DataUser.uid, 123, 0, 100, function(dataTable) {
            updateList(dataList || DataList);
            updateTable(dataTable || DataTable);
            $('#global-loader').fadeOut(200);
        });
    });
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
                    Events.fire('change_user', DataUser.uid, $item.data('user-id'), 123, function(data) {
                        $el.closest('.contact').html('');
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
                    var dataItems = dataList || DataList;
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
                    $input.bind('keyup', function(e) {
                        var text = $.trim($input.val());
                        if (e.keyCode && e.keyCode != 13) return false;
                        if (!text) return false;
                        if (e.keyCode == 13) return onSave(text);
                    });

                    function onSave(text) {
                        Events.fire('add_list', DataUser.uid, text, function(data) {
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
            updateTable(dataTable || DataTable);
        });
    });
}

/**
 * Templating
 */
(function() {
    var t = this;
    var cache = {};
    var format = function(str) {
        return str
            .replace(/[\r\t\n]/g, ' ')
            .split("<?").join("\t")
            .split("'").join("\\'")
            .replace(/\t=(.*?)\?>/g, "',$1,'")
            .split("\t").join("');")
            .split("?>").join("p.push('")
            .split("\r").join("\\'");
    };
    var tmpl = function(str, data) {
        try {
            var fn = (!/[^\w-]/.test(str))
                ? (cache[str] = cache[str] || tmpl($.trim($('#' + str).html() || t[str])))
                : (new Function('obj',
                'var p=[],' +
                    'print=function(){p.push.apply(p,arguments)},' +
                    'isset=function(v){return !!obj[v]},' +
                    'each=function(ui,obj){for(var i=0; i<obj.length; i++) { print(tmpl(ui, $.extend(obj[i],{i:i}))) }};' +
                    "with(obj){p.push('" + format(str) + "');} return p.join('');"
            ));
            return data ? fn(data) : fn;
        }
        catch(e) {
            if (console && console.log) console.log(format(str));
            throw e;
        }
    };

    return t.tmpl = tmpl;
})();

var LIST =
'<div class="tab-bar">' +
    '<? each(LIST_ITEM, items); ?>';
'</div>';

var LIST_ITEM =
'<span data-id="<?=itemId?>" class="tab<?=isset("itemSelected") ? " selected" : "" ?>">' +
    '<?=itemTitle?>' +
'</span>';

var TABLE =
'<thead id="table-head">' +
    '<tr>' +
        '<th class="public" width="30%">' +
            '<input class="filter" id="filter" type="text" placeholder="Поиск по названию" />' +
        '</th>' +
        '<th class="followers">' +
            'подписчики<span class="icon arrow"></span>' +
        '</th>' +
        '<th class="growth">' +
            'прирост<span class="icon arrow"></span>' +
        '</th>' +
        '<th class="contacts" width="31%">' +
            'контакты<span class="icon arrow"></span>' +
        '</th>' +
    '</tr>' +
'</thead>' +
'<tbody id="table-body">' +
    '<?=tmpl(TABLE_BODY, {rows: rows})?>'
'</tbody>';

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<tr>' +
    '<td>' +
        '<span class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</span>' +
        '<?=publicName?>' +
    '</td>' +
    '<td><?=publicFollowers?></td>' +
    '<td>' +
        '<span class="<? print(publicGrowthNum > 0 ? "plus" : "minus"); ?>">' +
            '<?=publicGrowthNum?> <small><?=publicGrowthPer?></small>' +
        '</span>' +
    '</td>' +
    '<td>' +
        '<?=tmpl(CONTACT, users[0])?>' +
        '<div class="actions">' +
            '<span class="action add-to-list">' +
                '<span class="icon plus"></span>' +
            '</span>' +
        '</div>' +
    '</td>' +
'</tr>';

var CONTACT =
'<div class="contact">' +
    '<div class="photo">' +
        '<img src="<?=userPhoto?>" alt="" />' +
    '</div>' +
    '<div class="content">' +
        '<div class="name">' +
            '<a target="_blank" href="http://vk.com/im?sel=<?=userId?>"><?=userName?></a>' +
        '</div>' +
        '<div class="description">' +
            '<?=userDescription?>' +
        '</div>' +
        '<div class="icon arrow"></div>' +
    '</div>' +
'</div>';

var DROPDOWN =
'<div class="dropdown">' +
    '<? each(DROPDOWN_ITEM, items); ?>' +
    '<input type="text" class="add-item" placeholder="Название списка" />' +
    '<div class="item show-input">Создать список</div>'
'</div>';

var DROPDOWN_ITEM =
'<div data-id="<?=itemId?>" class="item"><?=itemTitle?><div class="icon plus"></div></div>';

var CONTACT_DROPDOWN =
'<div class="contact-dropdown">' +
    '<? each(CONTACT_DROPDOWN_ITEM, users); ?>' +
'</div>';

var CONTACT_DROPDOWN_ITEM =
'<div class="item" data-user-id="<?=userId?>">' +
    '<div class="photo">' +
        '<img src="<?=userPhoto?>" alt="" />' +
    '</div>' +
    '<div class="content">' +
        '<div class="name">' +
            '<a target="_blank" href="http://vk.com/im?sel=<?=userId?>"><?=userName?></a>' +
        '</div>' +
        '<div class="description">' +
            '<?=userDescription?>' +
        '</div>' +
    '</div>' +
'</div>';
