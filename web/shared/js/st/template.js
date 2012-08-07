/**
 * Templating
 */
var tmpl = (function($) {
    var cache = {};
    var format = function(str) {
        return str
            .replace(/[\r\t\n]/g, ' ')
            .split('<?').join('\t')
            .split("'").join("\\'")
            .replace(/\t=(.*?)\?>/g, "',$1,'")
            .split('?>').join("p.push('")
            .split('\t').join("');")
            .split('\r').join("\\'");
    };
    var tmpl = function(str, data) {
        try {
            var fn = (/^#[A-Za-z0-9_-]*$/.test(str))
                ? function() {
                    return cache[str] || ($(str).length ? tmpl($(str).html()) : str)
                }
                : (new Function('obj',
                    'var p=[],' +
                    'print=function(){p.push.apply(p,arguments)},' +
                    'isset=function(v){return !!obj[v]},' +
                    'each=function(ui,obj){for(var i=0; i<obj.length; i++) { print(tmpl(ui, $.extend(obj[i],{i:i}))) }};' +
                    "with(obj){p.push('" + format(str) + "');} return p.join('');"
                ));
            return (cache[str] = fn(data || {}));
        }
        catch(e) {
            if (window.console && console.log) console.log(format(str));
            throw e;
        }
    };

    return tmpl;
})(jQuery);

var LIST =
'<div class="tab-bar clear-fix">' +
    '<div class="tab selected" data-id="null">Популярные</div>' +
    '<? each(LIST_ITEM, items); ?>' +
    '<div class="actions">' +
        '<a href="javascript:;" class="share">Поделиться</a> |' +
        '<a href="javascript:;" class="edit">Редактировать</a> |' +
        '<a href="javascript:;" class="delete">Удалить</a>' +
    '</div> ' +
'</div>';

var LIST_ITEM =
'<? if (isset("itemFave")) { ?>' +
    '<span class="tab"data-id="<?=itemId?>"><?=itemTitle?></span>' +
'<? } ?>';

var TABLE =
'<div class="list-head clear-fix">' +
    '<div class="item publics">' +
        '<input class="filter" id="filter" type="text" placeholder="Поиск по названию" />' +
    '</div>' +
    '<div class="item followers">' +
        'подписчики<span class="icon arrow"></span>' +
    '</div>' +
    '<div class="item growth">' +
        'прирост<span class="icon arrow"></span>' +
    '</div>' +
    '<div class="item contacts">' +
        'контакты' +
    '</div>' +
'</div>' +
'<div class="list-body">' +
    '<?=tmpl(TABLE_BODY, {rows: rows})?>' +
'</div>';

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<div class="public clear-fix" data-id="<?=publicId?>">' +
    '<div class="public-info clear-fix" data-id="<?=publicId?>">' +
        '<div class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</div>' +
        '<a target="_blank" href="http://vk.com/public<?=publicId?>"><?=publicName?></a>' +
    '</div>' +
    '<div class="public-followers"><?=publicFollowers?></div>' +
    '<div class="public-growth">' +
        '<span class="<?=publicGrowthNum > 0 ? "plus" : "minus"?>">' +
            '<?=publicGrowthNum?> <small><?=publicGrowthPer?>%</small>' +
        '</span>' +
    '</div>' +
    '<div class="public-contacts">' +
        '<? if (isset("users") && users.length) { ?>' +
            '<?=tmpl(CONTACT, users[0])?>' +
        '<? } ?>' +
    '</div>' +
    '<div class="public-actions">' +
        '<span class="action add-to-list">' +
            '<span class="icon <?=lists.length ? "select" : "plus"?>"></span>' +
        '</span>' +
        '<span class="action delete-public">' +
            '<span class="icon delete"></span>' +
        '</span>' +
        '<span class="action restore-public">' +
            '<span class="icon plus"></span>' +
        '</span>' +
    '</div>' +
'</div>';

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
    '<div class="item show-input">Создать список</div>' +
    '<div class="item hide-public">Скрыть паблик</div>' +
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

var FILTER_LIST =
'<div class="item selected" data-id="null">Популярные</div>' +
'<? each(FILTER_LIST_ITEM, items); ?>';

var FILTER_LIST_ITEM =
'<div class="item" data-id="<?=itemId?>">' +
    '<span class="text"><?=itemTitle?></span>' +
    '<div class="icon bookmark<?=(isset("itemFave")) ? " selected" : ""?>"></div>' +
'</div>';

var BOX_LAYOUT =
'<div  class="box-layout"></div>';

var BOX_WRAP =
'<div class="box-wrap">' +
    '<? if (isset("title")) { ?>' +
        '<div class="title">' +
            '<span class="text"><?=title?></span>' +
            '<? if (isset("closeBtn")) { ?>' +
                '<div class="close"></div>' +
            '<? } ?>' +
        '</div>' +
    '<? } ?>' +
    '<div class="body"><?=body?></div>' +
    '<? if (isset("buttons") && buttons.length) { ?>' +
        '<div class="actions-wrap">' +
            '<div class="actions"></div>' +
        '</div>' +
    '<? } ?>' +
'</div>';

var BOX_ACTION =
'<button class="action button<?=isset("isWhite") ? " white" : ""?>"><?=label?></button>';

var BOX_LOADING =
'<div class="box-loading"></div>';

var BOX_SHARE =
'<div class="box-share">' +
    '<div class="title">Поделитесь с друзьями</div>' +
    '<input type="text" value="http://socialboard.ru/stat" />' +
    '<div class="title">Выберите друзей</div>' +
    '<input type="text" class="users"></textarea>' +
//    '<div class="title">Ваш комментарий</div>' +
//    '<textarea rows="2" cols="" class="comment"></textarea>' +
    '<div class="title">Выберите списки</div>' +
    '<input type="text" class="lists"></textarea>' +
'</div>';