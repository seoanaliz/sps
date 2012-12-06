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
var REPORTS_MONITOR_ITEM =
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
    '<div class="column time" title="Время постинга"><?=isset("posted_at") ? posted_at : "-" ?></div>' +
    '<div class="column time" title="Время удаления"><?=isset("deleted_at") ? deleted_at : "-" ?></div>' +
    '<div class="column visitors" title="Уникальных посетителей"><?=isset("visitors") ? visitors : "0" ?></div>' +
    '<div class="column subscribers" title="Подписалось"><?=isset("subscribers") ? subscribers : "0" ?></div>' +
'</div>';

var REPORTS_MONITOR_HEADER =
'<div class="form">' +
    '<input id="our-public-id" type="text" placeholder="Кого рекламируем" />' +
    '<input id="public-id" type="text" placeholder="Где размещаем" />' +
    '<input id="time" type="text" placeholder="Время начала наблюдения" style="width: 200px" />' +
    '<button id="addReport" class="button">+</button>' +
'</div>';
