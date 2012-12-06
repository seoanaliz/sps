/*
{"response":[
    {
        "published_at":null,
        "ad_public":{
            "id":123,
            "ava":"https://vk.com/images/community_50.gif",
            "name":"ЭРОТИКА НА ГРАНИ ПОРНО. СЕКС ЗНАКОМСТВА.",
            "link":"http://vk.com/public123"
        },
        "posted_at":null,
        "deleted_at":null,
        "overlaps":null,
        "subscribers":0,
        "visitors":0
    }
]}
*/
var REPORTS = {
    MAIN:
    '<div id="header" class="header"></div>' +
    '<div id="list-header" class="list-head clear-fix"></div>' +
    '<div id="results"></div>',

    HEADER:
    '<div class="tab-bar">' +
        '<div id="tab-monitors" class="tab selected">Мониторы</div>' +
        '<div id="tab-results" class="tab">Результаты</div>' +
    '</div>' +
    '<div id="list-add-monitor" class="list-add-monitor"></div>',

    RESULT: {
        LIST_HEADER:
        '<div class="item public our-public">Кого рекламируем<span class="icon arrow"></div>' +
        '<div class="item public partner">Где размещаем<span class="icon arrow"></div>' +
        '<div class="item time post-time">Во сколько<span class="icon arrow"></div>' +
        '<div class="item time date">Дата<span class="icon arrow"></div>' +
        '<div class="item time">На сколько<span class="icon arrow"></div>' +
        '<div class="item time">Активность<span class="icon arrow"></div>',

        LIST:
        '<? if (isset("items") && items.length) { ?>' +
            '<? each(REPORTS.RESULT.ITEM, items); ?>' +
        '<? } else { ?>' +
            'Empty' +
        '<? } ?>',

        ITEM:
        '<div class="row">' +
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
        '</div>'
    },
    MONITOR: {
        LIST_ADD_MONITOR:
        '<div class="form">' +
            '<input id="our-public-id" type="text" placeholder="Кого рекламируем" />' +
            '<input id="public-id" type="text" placeholder="Где размещаем" />' +
            '<input id="time" type="text" placeholder="Время начала наблюдения" style="width: 200px" />' +
            '<button id="addReport" class="button">Добавить монитор</button>' +
        '</div>',

        LIST_HEADER:
        '<div class="item public our-public">Кого рекламируем<span class="icon arrow"></div>' +
        '<div class="item public partner">Где размещаем<span class="icon arrow"></div>' +
        '<div class="item time post-time">Время поста<span class="icon arrow"></div>' +
        '<div class="item time delete-time">Удалён через<span class="icon arrow"></div>' +
        '<div class="item time overlap-time">Перекрыт через<span class="icon arrow"></div>' +
        '<div class="item visitors">Посетителей<span class="icon arrow"></div>' +
        '<div class="item subscribers">Подписчиков<span class="icon arrow"></div>',

        LIST:
        '<? if (isset("items") && items.length) { ?>' +
            '<? each(REPORTS.MONITOR.ITEM, items); ?>' +
        '<? } else { ?>' +
            'Empty' +
        '<? } ?>',

        ITEM:
        '<div class="row">' +
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
            '<div class="column time" title="Удалён через"><?=isset("deleted_at") ? deleted_at : "-" ?></div>' +
            '<div class="column time" title="Перекрыт через"><?=isset("deleted_at") ? deleted_at : "-" ?></div>' +
            '<div class="column visitors" title="Уникальных посетителей"><?=isset("visitors") ? visitors : "0" ?></div>' +
            '<div class="column subscribers" title="Подписалось"><?=isset("subscribers") ? subscribers : "0" ?></div>' +
        '</div>'
    }
};
