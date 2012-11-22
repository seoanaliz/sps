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
'<?=tmpl(TABLE_HEADER)?>' +
'<div class="list-body">' +
    '<?=tmpl(TABLE_BODY, {rows: rows})?>' +
'</div>';

var TABLE_HEADER =
'<div class="list-head clear-fix">' +
    '<div class="item publics">' +
        '<input class="filter" id="filter" type="text" placeholder="Поиск по названию" />' +
    '</div>' +
    '<div class="item followers">' +
        'подписчики<span class="icon arrow"></span>' +
    '</div>' +
    '<div class="item is-active">' +
        'бан<span class="icon arrow">' +
    '</div>' +
    '<div class="item in-search">' +
        'в поиске<span class="icon arrow">' +
    '</div>' +
    '<div class="item visitors">' +
        'посетители<span class="icon arrow">' +
    '</div>' +
    '<div class="item growth">' +
        'прирост<span class="icon arrow"></span>' +
    '</div>' +
    '<div class="item contacts">' +
        'контакты' +
    '</div>' +
'</div>';

var TABLE_BODY =
'<? each(TABLE_ROW, rows); ?>';

var TABLE_ROW =
'<div class="public clear-fix" data-id="<?=publicId?>">' +
    '<div class="column public-info clear-fix" data-id="<?=publicId?>">' +
        '<div class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</div>' +
        '<a target="_blank" href="http://vk.com/public<?=publicId?>"><?=publicName?></a>' +
    '</div>' +
    '<div class="column public-followers"><?=publicFollowers?></div>' +
    '<div class="column public-is-active"><span class="<?=publicIsActive ? "true" : "false"?>">●</span></div>' +
    '<div class="column public-in-search"><span class="<?=publicInSearch ? "true" : "false"?>">●</span></div>' +
    '<div class="column public-visitors">' +
        '<a href="http://vk.com/stats?gid=<?=publicId?>" target="_blank">' +
            '<?=publicVisitors ? publicVisitors : "-"?>' +
        '</a>' +
    '</div>' +
    '<div class="column public-growth">' +
        '<span class="<?=publicGrowthNum > 0 ? "plus" : "minus"?>">' +
            '<?=publicGrowthNum?> <small><?=publicGrowthPer?>%</small>' +
        '</span>' +
    '</div>' +
    '<div class="column public-contacts">' +
        '<? if (isset("users") && users.length) { ?>' +
            '<?=tmpl(CONTACT, users[0])?>' +
        '<? } ?>' +
    '</div>' +
    '<div class="column public-actions">' +
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

var OUR_TABLE =
'<?=tmpl(OUR_TABLE_HEADER)?>' +
'<div class="list-body">' +
    '<?=tmpl(OUR_TABLE_BODY, {rows: rows})?>' +
'</div>';

var OUR_TABLE_HEADER =
'<div class="list-head clear-fix">' +
    '<div class="item publics">' +
        '<input class="filter" id="filter" type="text" placeholder="Поиск по названию" disabled="true" />' +
    '</div>' +
    '<div class="item posts" title="всего постов">посты<span class="icon arrow"></div>' +
    '<div class="item posts-per-day" title="постов в день">постов в день<span class="icon arrow"></div>' +
    '<div class="item authors-posts" title="авторские посты">авт. посты<span class="icon arrow"></div>' +
    '<div class="item authors-likes" title="авторские лайки">авт. лайки<span class="icon arrow"></div>' +
    '<div class="item authors-reposts" title="авторские репосты">авт. репосты<span class="icon arrow"></div>' +
    '<div class="item growth-visitors" title="прирост посетителей">рост посетит.<span class="icon arrow"></div>' +
//    '<div class="item sb-posts" title="SB посты">SB посты<span class="icon arrow"></div>' +
    '<div class="item sb-likes" title="SB лайки">SB лайки<span class="icon arrow"></div>' +
'</div>';

var OUR_TABLE_BODY =
'<? each(OUR_TABLE_ROW, rows); ?>';

var OUR_TABLE_ROW =
'<div class="public clear-fix" data-id="<?=publicId?>">' +
    '<div class="column public-info clear-fix" data-id="<?=publicId?>">' +
        '<div class="photo">' +
            '<img src="<?=publicImg?>" alt="" />' +
        '</div>' +
        '<a target="_blank" href="http://vk.com/public<?=publicId?>"><?=publicName?></a>' +
    '</div>' +
    '<div class="column public-posts"><?=publicPosts ? publicPosts : "-"?></div>' +
    '<div class="column public-posts-per-day"><?=publicPostsPerDay ? publicPostsPerDay : "-"?></div>' +
    '<div class="column public-authors-posts"><?=publicAuthorsPosts ? publicAuthorsPosts + "%" : "-"?></div>' +
    '<div class="column public-authors-likes"><?=publicAuthorsLikes ? publicAuthorsLikes + "%" : "-"?></div>' +
    '<div class="column public-authors-reposts"><?=publicAuthorsReposts ? publicAuthorsReposts + "%" : "-"?></div>' +
    '<div class="column public-growth-visitors"><?=publicGrowthVisitors ? publicGrowthVisitors : "-"?></div>' +
//    '<div class="column public-sb-posts"><?=publicSbPosts ? publicSbPosts : "-"?></div>' +
    '<div class="column public-sb-likes"><?=publicSbLikes ? publicSbLikes : "-"?></div>' +
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

var BOX_SHARE =
'<div class="box-share">' +
//    '<div class="title">Поделитесь с друзьями</div>' +
//    '<input type="text" value="http://socialboard.ru/stat" />' +
    '<div class="title">Выберите списки</div>' +
    '<input type="text" class="lists"></textarea>' +
    '<div class="title">Выберите друзей</div>' +
    '<input type="text" class="users"></textarea>' +
//    '<div class="title">Ваш комментарий</div>' +
//    '<textarea rows="2" cols="" class="comment"></textarea>' +
'</div>';