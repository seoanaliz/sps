var Configs = {
    limit: 100
};

//        {"response":[
//            {
//                "published_at":null,
//                "ad_public":{
//                    "id":123,
//                    "ava":"https://vk.com/images/community_50.gif",
//                    "name":"ЭРОТИКА НА ГРАНИ ПОРНО. СЕКС ЗНАКОМСТВА.",
//                    "link":"http://vk.com/public123"
//                },
//                "posted_at":null,
//                "deleted_at":null,
//                "overlaps":null,
//                "subscribers":0,
//                "visitors":0
//            }
//        ]}
var REPORTS_ITEM =
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
    '<div class="column time" title="Время постинга"><?=isset("posted_at") ? posted_at : "Неизветсно" ?></div>' +
    '<div class="column time" title="Время удаления"><?=isset("deleted_at") ? deleted_at : "Неизветсно" ?></div>' +
    '<div class="column visitors" title="Уникальных посетителей"><?=isset("visitors") ? visitors : "0" ?></div>' +
    '<div class="column subscribers" title="Подписалось"><?=isset("subscribers") ? subscribers : "0" ?></div>' +
'</div>';

$.mask.definitions['2']='[012]';
$.mask.definitions['3']='[0123]';
$.mask.definitions['5']='[012345]';

$(document).ready(function() {
    init();
    bindEvents();
    $('#time').mask('29:59');
});

function init() {
    updateList();
}

function updateList() {
//        {"response":[
//            {
//                "published_at":null,
//                "ad_public":{
//                    "id":123,
//                    "ava":"https://vk.com/images/community_50.gif",
//                    "name":"ЭРОТИКА НА ГРАНИ ПОРНО. СЕКС ЗНАКОМСТВА.",
//                    "link":"http://vk.com/public123"
//                },
//                "posted_at":null,
//                "deleted_at":null,
//                "overlaps":null,
//                "subscribers":0,
//                "visitors":0
//            }
//        ]}
    Events.fire('get_report_list', Configs.limit, 0, function(data) {
        var $results = $('#results');
        var html = '';
        if (data && data.length) {
            for (var i = 0; i < data.length; i++) {
                if (!data.hasOwnProperty(i)) continue;
                var item = data[i];
                html += tmpl(REPORTS_ITEM, item);
            }
        }
        $results.html(html);
        $results.find('.time').each(function() {
            var $date = $(this);
            var timestamp = $date.text();
            if (!intval(timestamp)) return;
            var currentDate = new Date();
            var date = new Date(timestamp * 1000);
            var m = date.getMonth() + 1;
            var y = date.getFullYear() + '';
            var d = date.getDate() + '';
            var h = date.getHours() + '';
            var min = date.getMinutes() + '';
            var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
            if (currentDate.getDate() != d) {
                text += ', ' + d + '.' + m + '.' + (y.substr(-2));
            }
            $date.html(text);
        });
    });
}

function bindEvents() {
    $('#addReport').click(function() {
//            {"response":[
//                {"published_at":{
//                    "id":12,
//                    "ava":"http://cs301412.userapi.com/g00012/e_a326b897.jpg",
//                    "name":"СЗИП каждый день",
//                    "link":"http://vk.com/public12"},
//                    "ad_public":{
//                        "id":123,
//                        "ava":"https://vk.com/images/community_50.gif",
//                        "name":"ЭРОТИКА НА ГРАНИ ПОРНО. СЕКС ЗНАКОМСТВА.",
//                        "link":"http://vk.com/public123"},
//                    "posted_at":null,
//                    "deleted_at":null,
//                    "overlaps":null,
//                    "subscribers":0,
//                    "visitors":0
//                }
//            ]};
        var ourPublicId = $.trim($('#our-public-id').val());
        var publicId = $.trim($('#public-id').val());
        var dirtyTime = ($('#time').val() || '__:__').split('_').join('0').split(':');
        var hours = dirtyTime[0];
        var minutes = dirtyTime[1];
        var date = new Date();
        date.setHours(hours);
        date.setMinutes(minutes);
        var timestamp = Math.round(date.getTime() / 1000);
        Events.fire('add_report', ourPublicId, publicId, timestamp, function() {
            updateList();
        });
    });
}
