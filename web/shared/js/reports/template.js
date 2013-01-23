var REPORTS = {
    MAIN:
    '<div id="header" class="header"></div>' +
    '<table><tbody>' +
        '<tr>' +
            '<td id="left-column" class="left-column">' +
                '<div class="content">' +
                    '<div id="list-header" class="list-head clear-fix"></div>' +
                    '<div id="results"></div>' +
                '</div>' +
            '</td>' +
            '<td id="right-column" class="right-column">' +
                '<div id="group-list" class="filter"></div>' +
            '</td>' +
        '</tr>' +
    '</tbody></table>',

    BOX_SHARE:
    '<div class="box-share">' +
        '<div class="title">Выберите списки</div>' +
        '<input type="text" class="lists"></textarea>' +
        '<div class="title">Выберите друзей</div>' +
        '<input type="text" class="users"></textarea>' +
    '</div>',

    GROUP_LIST_ITEM:
    '<div data-id="<?=id?>" class="item"><?=name?></div>',

    GROUP_LIST:
    '<? if (!count(userLists) || count(userLists) > 1) { ?>' +
        '<div class="list">' +
            '<? each(REPORTS.GROUP_LIST_ITEM, defaultLists); ?>' +
        '</div>' +
        '<div class="title">Мои списки</div>' +
    '<? } ?>' +
    '<div class="list">' +
        '<? each(REPORTS.GROUP_LIST_ITEM, userLists); ?>' +
        '<input type="text" placeholder="Введите название списка..." /> ' +
        '<div class="item">Создать список</div>' +
    '</div>' +
    '<? if (count(sharedLists)) { ?>' +
        '<div class="title">Общие списки</div>' +
        '<div class="list">' +
            '<? each(REPORTS.GROUP_LIST_ITEM, sharedLists); ?>' +
        '</div>' +
    '<? } ?>',

    HEADER:
    '<div class="tab-bar">' +
        '<div id="tab-monitors" class="tab selected">Мониторы</div>' +
        '<div id="tab-results" class="tab">Результаты</div>' +
        '<div class="actions">' +
            '<a id="share-link" class="share">Поделиться списком</a>' +
        '</div>' +
    '</div>' +
    '<div id="list-add-monitor" class="list-add-monitor"></div>',

    MONITOR: {
        LIST_ADD_MONITOR:
        '<div class="form">' +
            '<input id="our-public-id" data-required="true" type="text" placeholder="Кого рекламируем" />' +
            '<input id="public-id" data-required="true" type="text" placeholder="Где размещаем" />' +
            '<input id="time-start" data-required="true" type="text" placeholder="Начало" style="width: 70px" />' +
            '<input id="time-end" type="text" placeholder="Конец" style="width: 70px" />' +
            '<input id="datepicker" data-required="true" type="text" placeholder="Дата" style="width: 70px" />' +
            '<button id="addReport" class="button">Добавить</button>' +
        '</div>',

        LIST_HEADER:
        '<div class="item public our-public">Кого рекламируем<span class="icon arrow"></div>' +
        '<div class="item public partner">Где размещаем<span class="icon arrow"></div>' +
        '<div class="item time date">Дата<span class="icon arrow"></div>' +
        '<div class="item time time-start">Начало<span class="icon arrow"></div>' +
        '<div class="item time time-stop">Конец<span class="icon arrow"></div>' +
        '<div class="item time">Активность<span class="icon arrow"></div>',

        LIST:
        '<? if (isset("items") && items.length) { ?>' +
            '<? each(REPORTS.MONITOR.ITEM, items); ?>' +
        '<? } else { ?>' +
            'Empty' +
        '<? } ?>',

        ITEM:
        '<div class="row" data-our-public-id="<?=ad_public.id?>" data-public-id="<?=published_at.id?>" data-report-id="<?=report_id?>">' +
            '<? if (isset("ad_public") && ad_public) { ?>' +
                '<div class="column public" title="Наш паблик">' +
                    '<div class="photo">' +
                        '<img src="<?=ad_public.ava?>" alt="" />' +
                    '</div>' +
                    '<div class="name">' +
                        '<a target="_blank" href="http://vk.com/public<?=ad_public.id?>"><?=ad_public.name?></a>' +
                    '</div>' +
                '</div>' +
            '<? } ?>' +
            '<? if (isset("published_at") && published_at) { ?>' +
                '<div class="column public" title="Партнер">' +
                    '<div class="photo">' +
                        '<img src="<?=published_at.ava?>" alt="" />' +
                    '</div>' +
                    '<div class="name">' +
                        '<a target="_blank" href="http://vk.com/public<?=published_at.id?>"><?=published_at.name?></a>' +
                    '</div>' +
                '</div>' +
            '<? } ?>' +
            '<div class="column date" title="Дата"><?=isset("start_search_at") ? start_search_at : "-" ?></div>' +
            '<div class="column time" title="Начало"><?=isset("start_search_at") ? start_search_at : "-" ?></div>' +
            '<div class="column time" title="Конец"><?=isset("stop_search_at") ? stop_search_at : "-" ?></div>' +
            '<div class="column time" title="Активность"><span class="<?=(isset("active") &&  active) ? "true" : "false"?>">●</span></div>' +
            '<div class="column action" title="Удалить"><div class="icon delete"></div></div>' +
        '</div>'
    },

    RESULT: {
        LIST_HEADER:
        '<div class="item public our-public">Кого рекламируем<span class="icon arrow"></div>' +
        '<div class="item public partner">Где размещаем<span class="icon arrow"></div>' +
        '<div class="item time post-time">Время поста<span class="icon arrow"></div>' +
        '<div class="item time delete-time">Удалён<span class="icon arrow"></div>' +
        '<div class="item time overlap-time">Перекрыт<span class="icon arrow"></div>' +
        '<div class="item visitors">Посетителей<span class="icon arrow"></div>' +
        '<div class="item subscribers">Подписчиков<span class="icon arrow"></div>',

        LIST:
        '<? if (isset("items") && items.length) { ?>' +
            '<? each(REPORTS.RESULT.ITEM, items); ?>' +
        '<? } else { ?>' +
            'Empty' +
        '<? } ?>',

        ITEM:
        '<div class="row" data-our-public-id="<?=ad_public.id?>" data-public-id="<?=published_at.id?>">' +
            '<? if (isset("ad_public") && ad_public) { ?>' +
                '<div class="column public" title="Наш паблик">' +
                    '<div class="photo">' +
                        '<img src="<?=ad_public.ava?>" alt="" />' +
                    '</div>' +
                    '<div class="name">' +
                        '<a target="_blank" href="http://vk.com/public<?=ad_public.id?>"><?=ad_public.name?></a>' +
                    '</div>' +
                '</div>' +
            '<? } ?>' +
            '<? if (isset("published_at") && published_at) { ?>' +
                '<div class="column public" title="Партнер">' +
                    '<div class="photo">' +
                        '<img src="<?=published_at.ava?>" alt="" />' +
                    '</div>' +
                    '<div class="name">' +
                        '<a target="_blank" href="http://vk.com/public<?=published_at.id?>"><?=published_at.name?></a>' +
                    '</div>' +
                '</div>' +
            '<? } ?>' +
            '<div class="column time" title="Время поста"><?=isset("posted_at") ? posted_at : "-" ?></div>' +
            '<div class="column diff-time" title="Удалён через"><?=isset("deleted_at") ? deleted_at : "-" ?></div>' +
            '<div class="column diff-time" title="Перекрыт через"><?=(isset("overlaps") && overlaps.length) ? overlaps[0] : "-" ?></div>' +
            '<div class="column visitors<?=(isset("visitors") && visitors > 0) ? " plus" : " minus"?>" title="Уникальных посетителей"><?=isset("visitors") ? visitors : "0" ?></div>' +
            '<div class="column subscribers<?=(isset("subscribers") && subscribers > 0) ? " plus" : " minus"?>" title="Подписалось"><?=isset("subscribers") ? subscribers : "0" ?></div>' +
        '</div>'
    }
};
