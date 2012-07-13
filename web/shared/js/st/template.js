/**
 * Templating
 */
var tmpl = (function() {
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

    return tmpl;
})();

var LIST =
'<div class="tab-bar">' +
    '<span data-id="null" class="tab selected">Все записи</span>' +
    '<? each(LIST_ITEM, items); ?>' +
'</div>';

var LIST_ITEM =
'<span data-id="<?=itemId?>" class="tab">' +
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
        '<th class="contacts" width="30%">' +
            'контакты<span class="icon arrow"></span>' +
        '</th>' +
    '</tr>' +
'</thead>' +
'<tbody id="table-body">' +
    '<?=tmpl(TABLE_BODY, {rows: rows})?>' +
'</tbody>';

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<tr class="public" data-id="<?=publicId?>">' +
    '<td>' +
        '<span class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</span>' +
        '<?=publicName?>' +
    '</td>' +
    '<td><?=publicFollowers?></td>' +
    '<td>' +
        '<span class="<?=publicGrowthNum > 0 ? "plus" : "minus"?>">' +
            '<?=publicGrowthNum?> <small><?=publicGrowthPer?>%</small>' +
        '</span>' +
    '</td>' +
    '<td>' +
        '<? if (isset("users") && users.length) { ?>' +
            '<?=tmpl(CONTACT, users[0])?>' +
        '<? } ?>' +
        '<div class="actions">' +
            '<span class="action add-to-list">' +
                '<span class="icon <?=lists ? "select" : "plus"?>"></span>' +
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
    '<div class="item show-input">Создать список</div>' +
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