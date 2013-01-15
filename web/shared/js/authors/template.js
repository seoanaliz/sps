var MAIN =
'<div id="left-column"></div>' +
'<div id="right-column"></div>';

var TABS =
'<div class="tab-bar messenger">' +
    '<? if (isset("listTab")) { ?>' +
        '<? each(TABS_ITEM_LIST, [listTab]); ?>' +
    '<? } ?>' +
    '<? if (isset("dialogTab")) { ?>' +
        '<? each(TABS_ITEM_DIALOG, [dialogTab]); ?>' +
    '<? } ?>' +
    '<div class="links">' +
        '<a class="filter" data-filtered="Показать все диалоги" data-not-filtered="Показать только непрочитанные">Показать только непрочитанные</a>' +
        '<a class="show-templates">Готовые ответы</a>' +
    '</div>' +
'</div>';

var TABS_ITEM_LIST =
'<div class="tab list<?=(isset("isSelected") && isSelected) ? " selected" : ""?>" data-id="<?=id?>">' +
    '<?=label?>' +
'</div>';

var TABS_ITEM_DIALOG =
'<div class="tab dialog<?=(isset("isSelected") && isSelected) ? " selected" : ""?>" data-id="<?=id?>">' +
    '<?=label?>' +
    '<? if (isset("isOnline") && isOnline) { ?>' +
        '<div class="icon online"></div>' +
    '<? } else { ?>' +
        '<div class="icon offline"></div>' +
    '<? } ?>' +
    '<? if (isset("isOnList") && isOnList) { ?>' +
        '<div class="icon select"></div>' +
    '<? } else { ?>' +
        '<div class="icon plus"></div>' +
    '<? } ?>' +
'</div>';

var LEFT_COLUMN =
    '<?=tmpl(TABLE_HEADER)?>' +
'<div class="list">' +
    '<div id="list-messages"></div>' +
    '<div id="list-dialogs"></div>' +
'</div>';

var DIALOGS =
'<div class="dialogs" data-id="<?=id?>">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<? each(DIALOGS_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">Список диалогов пуст</div>' +
    '<? } ?>' +
'</div>';

var AUTHORS =
'<div class="dialogs" data-id="12">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<?each(AUTHORS_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">Нету данных, сорьки :\'(</div>' +
    '<? } ?>' +
'</div>';

var AUTHORS_LOADING =
'<div class="authors">' +
    '<div class="load"></div>' +
'</div>';

var AUTHORS_ITEM =
'<div class="dialog clear-fix" data-id="0" data-title="<?=user.name?>" data-user-id="<?=user.id?>"">' +
    '<div class="user">' +
        '<div class="photo">' +
            '<a href="http://vk.com/id<?=user.id?>" target="_blank"><img src="<?=user.photo?>" alt="" /></a>' +
        '</div>' +
        '<div class="info clear-fix">' +
            '<div class="name">' +
                '<a href="http://vk.com/id<?=user.id?>" target="_blank"><?=user.name?></a>' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="history">' +
        '<? if (isset("metrick1")) { ?>' +
                '<?= tmpl(TABLE_ROW, metrick1) ?>' +
        '<? } else { ?>' +
            '...' +
        '<? } ?>' +
    '</div>' +
    '<div class="actions">' +
    '</div>' +
'</div>';

var RIGHT_COLUMN =
'<div class="header">' +
    '<div class="tab-bar">' +
        '<div class="tab selected">Контакты</div>' +
    '</div>' +
'</div>' +
'<div class="list scroll-like-mac">' +
    '<? if (isset("list") && list.length) { ?>' +
        '<? each(LIST_ITEM, list); ?>' +
    '<? } else { ?>' +
        '<div class="empty">Список пуст</div>' +
    '<? } ?>' +
'</div>';

var LIST_ITEM =
'<div class="<?=isset("isDraggable") && isDraggable ? "drag-wrap" : ""?>">' +
    '<? if (isset("title")) { ?>' +
        '<div class="item" data-id="<?=id?>" data-title="<?=title?>">' +
            '<div class="title<?=isset("isSelected") && isSelected ? " active" : ""?>">' +
                '<span class="text"><?=title?></span>' +
            '</div>' +
        '</div>' +
    '<? } ?>' +
'</div>';


var TABLE_ROW =

'<div class="column posts">' +
    '<span class="plus" >' +
        '<?=a?>'  +
    '</span>' +
'</div>' +
'<div class="column coolness">' +
        '<?=b?>' +
'</div>' +
'<div class="column coolness">' +
        '<?=c?>' +
'</div>';

var TABLE_HEADER =
    '<div class="list-head clear-fix">' +
        '<div class="item authors">' +
            'Автор</span>' +
        '</div>' +
        '<div class="item posts">' +
            'Постов отправлено(всего)' +
        '</div>' +
        '<div class="item coolness">' +
            'Крутизна лайков, %' +
        '</div>' +
        '<div class="item in-search">' +
            'Крутизна репостов, %' +
        '</div>' +
    '</div>';