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

var MAIN =
'<div class="left-column"></div>' +
'<div class="right-column"></div>';

var TABS =
'<div class="tab-bar">' +
    '<? each(TABS_ITEM, tabs); ?>' +
'</div>';

var TABS_ITEM =
'<div class="tab<?=(isset("isSelected") && isSelected) ? " selected" : ""?>" data-id="<?=id?>"><?=title?></div>';

var LEFT_COLUMN =
'<div class="header"></div>' +
'<div class="list"></div>';

var RIGHT_COLUMN =
'<div class="header">' +
    '<div class="tab-bar">' +
        '<div class="tab selected">Контакты</div>' +
    '</div>' +
'</div>' +
'<div class="list"></div>';

var DIALOGS =
'<? each(DIALOGS_ITEM, list); ?>';

var DIALOGS_ITEM =
'<div class="dialog clear-fix">' +
    '<div class="user">' +
        '<div class="photo">' +
            '<img src="<?=user.photo?>" alt="" />' +
        '</div>' +
        '<div class="info clear-fix">' +
            '<div class="name"><a href="http://vk.com/id<?=user.id?>" target="_blank"><?=user.name?></a></div>' +
            '<? if (user.isOnline) { ?>' +
                '<div class="status">Online</div>' +
            '<? } ?>' +
            '<div class="date"><?=lastMessage.timestamp?></div>' +
        '</div>' +
    '</div>' +
    '<div class="history"><?=lastMessage.text?></div>' +
    '<div class="actions">' +
        '<div class="action icon plus"></div>' +
    '</div>' +
'</div>';

var MESSAGES =
'<? each(MESSAGES_ITEM, list); ?>';

var MESSAGES_ITEM =
'<div class="message clear-fix">' +
    'message' +
'</div>';

var LIST =
'<? each(LIST_ITEM, list); ?>';

var LIST_ITEM =
'<div class="item" data-id="<?=id?>">' +
    '<div class="title"><?=title?></div>' +
    '<div class="list">' +
        '<? if (dialogs.length) { ?>' +
            '<? each(PUBLIC_LIST_ITEM, dialogs); ?>' +
        '<? } ?>' +
    '</div>' +
'</div>';

var PUBLIC_LIST_ITEM =
'<div class="public">' +
    '<div class="icon"><img src="<?=user.photo?>" alt="" /></div>' +
    '<div class="title"><?=user.name?></div>' +
'</div>';