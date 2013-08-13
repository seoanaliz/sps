var LIST = '<div class="tab selected" data-id="all">Популярные</div>';

var LIST_ITEM =
'<? if (isset("itemFave")) { ?>' +
    '<span class="tab" data-id="<?=itemId?>"><?=itemTitle?></span>' +
'<? } ?>';

var TABLE =
'<div class="list-head">' +
    '<?=tmpl(TABLE_HEADER)?>' +
'</div>' +
'<div class="body list-body">' +
    '<?=tmpl(TABLE_BODY, {rows: rows})?>' +
'</div>';

var TABLE_HEADER =
'<div class="row">' +
    '<div class="column <?= cur.dataUser.isEditor ? "column3" : "column4" ?> publics">' +
        '<div class="cell">' +
            '<div class="input-wrap">' +
               '<input class="filter" id="filter" type="text" placeholder="Поиск по названию" />' +
            '</div>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5 followers">' +
        '<div class="cell">' +
            'подписчики<span class="icon arrow"></span>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5 audience">' +
        '<div class="cell">' +
            'охват<span class="icon arrow">' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5 visitors">' +
        '<div class="cell">' +
            'посетители<span class="icon arrow">' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5 growth">' +
        '<div class="cell">' +
            'прирост<span class="icon arrow"></span>' +
        '</div>' +
    '</div>' +
    '<div class="column column1 in-search">' +
        '<div class="cell">' +
            'в поиске<span class="icon arrow">' +
        '</div>' +
    '</div>' +
    '<div class="column column1 cpp">' +
        '<div class="cell">' +
            '<span title="Стоимость одной публикации">Цена</span><span class="icon arrow">' +
        '</div>' +
    '</div>' +
    '<? if (cur.dataUser.isEditor) { ?>' +
        '<div class="column column1" title="Действия">' +
            '<div class="cell"></div>' +
        '</div>' +
    '<? } ?>' +
'</div>';

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<div class="public row" data-id="<?=intId?>">' +
    '<div class="column <?= cur.dataUser.isEditor ? "column3" : "column4" ?> public-info" data-id="<?=publicId?>">' +
        '<div class="cell">' +
            '<div class="photo">' +
                '<img src="<?=publicImg?>" alt="" />' +
            '</div>' +
            '<a target="_blank" href="http://vk.com/public<?=publicId?>"><?=publicName?></a>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5">' +
        '<div class="cell">' +
            '<?=publicFollowers ? numberWithSeparator(publicFollowers) : "-"?>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5">' +
        '<div class="cell">' +
            '<a class="stat-link-icon" href="http://vk.com/stats?act=reach&gid=<?=publicId?>" target="_blank">' +
                '<?=publicAudience ? numberWithSeparator(publicAudience) : "<div class=\'icon locked\'></div>"?>' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5">' +
        '<div class="cell">' +
            '<a class="stat-link-icon" href="http://vk.com/stats?gid=<?=publicId?>" target="_blank">' +
                '<?=publicVisitors ? numberWithSeparator(publicVisitors) : "<div class=\'icon locked\'></div>"?>' +
            '</a>' +
        '</div>' +
    '</div>' +
    '<div class="column column1-5">' +
        '<div class="cell">' +
            '<span class="<?=publicGrowthNum > 0 ? "plus" : "minus"?>">' +
                '<?=numberWithSeparator(publicGrowthNum)?> ' +
                '<small><?=numberWithSeparator(publicGrowthPer)?>%</small>' +
            '</span>' +
        '</div>' +
    '</div>' +
    '<div class="column column1">' +
        '<div class="cell">' +
            '<span class="<?=publicInSearch ? "true" : "false"?>">●</span>' +
        '</div>' +
    '</div>' +
    '<div class="column column1">' +
        '<div class="cell">' +
            '<span class="cpp-value" data-cpp="<?=cpp?>">' +
                '<? if (cpp === null || cpp === undefined || cpp === false || cpp === "") { ?>' +
                    '<span class="unspec"></span>' +
                '<? } else { ?>' +
                    '<?= cpp ?>&nbsp;руб' +
                '<? } ?>' +
            '</span>' +
        '</div>' +
    '</div>' +
    '<? if (cur.dataUser.isEditor) { ?>'+
        '<div class="column column1 public-actions">' +
            '<div class="cell">' +
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
        '</div>' +
    ' <? } ?> ' +
'</div>';

var DROPDOWN =
'<div class="dropdown">' +
    '<span class="icon delete clear-search"></span>' +
    '<input type="text" class="search" placeholder="Поиск" />' +
    '<? each(DROPDOWN_CATEGORY, categories); ?>' +
    '<? if (cur.dataUser.isAdmin) { ?>'+
        '<input type="text" class="add-item" placeholder="Название списка" />' +
        '<div class="show-input">Создать список</div>' +
    '<? } ?>'+
'</div>';

var DROPDOWN_CATEGORY =
'<? if (items.length) { ?>'+
    '<div class="category" data-number="<?=items.length?>">' +
        '<h4 class="title"><?=title?></h4>' +
        '<? each(DROPDOWN_ITEM, items); ?>' +
    '</div>' +
'<? } ?>';

var DROPDOWN_ITEM =
'<div data-id="<?=id?>" title="<?=name?>" class="item">' +
    '<div>' +
        '<?=name?>' +
    '</div>' +
'<div class="icon plus"></div></div>';

var FILTER_LIST =
'<? each(FILTER_LIST_ITEM, items); ?>';

var FILTER_LIST_ITEM =
'<div class="item" title="<?=name?>" data-id="<?=id?>" data-slug="<?=slug?>">' +
    '<span class="text"><?=name?></span>' +
    '<?if (cur.dataUser.isAdmin) { ?>'+
        '<span class="icon edit"></span>' +
        '<span class="icon bookmark"></span>' +
    '<? } ?>'+
'</div>';

var BOX_SHARE =
'<div class="box-share">' +
    '<div class="title">Выберите списки</div>' +
    '<input type="text" class="lists"></textarea>' +
    '<div class="title">Выберите друзей</div>' +
    '<input type="text" class="users"></textarea>' +
'</div>';
