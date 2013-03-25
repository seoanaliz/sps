var Elements = {
    initImages: function($block) {
        $block.find(".fancybox-thumb").fancybox({
            prevEffect: 'none',
            nextEffect: 'none',
            closeBtn:false,
            fitToView: false,
            helpers: {
                title: {type : 'inside'},
                buttons: {}
            }
        });

        //логика картинок топа
        $block.find('.post-image-top img').bind('load', function () {
            var src = $(this).attr('src');
            var img = new Image();
            var $link = $(this).closest('.post').find('.ajax-loader-ext');

            img.onload = function() {
                if (this.width < 250 && this.height < 250) {
                    //small
                    Elements.initLinkLoader($link, true);
                } else {
                    //big
                    Elements.initLinkLoader($link, false);
                }
            };

            img.src = src;
        });

        $block.find('.timestamp').easydate(easydateParams);
        $block.find('.date').easydate(easydateParams);
        $block.find('.images-ready:visible').imageComposition();
        $('#right-panel').find('.post .images').imageComposition('right');
    },
    initDraggable: function($elem, islog) {
        var $block = $elem.find('.post');
        if ($block.length) {
            $block.each(function() {
                Elements.initDraggable($(this));
            });
        } else if ($elem.is('.movable:not(.blocked)')) {
            islog && console.log($elem.find('> .content'));
            $elem.find('> .content').draggable({
                revert: 'invalid',
                appendTo: 'body',
                cursor: 'move',
                cursorAt: {left: 100, top: 20},
                helper: function() {
                    return $('<div/>').html('Укажите, куда поместить пост...').addClass('moving dragged');
                },
                start: function() {
                    $(this).closest('.post').addClass('moving');
                },
                stop: function() {
                    $(this).closest('.post').removeClass('moving');
                }
            });
        }
    },
    initDroppable: function($elem) {
        var $block = $elem.find('.slot.empty:not(.locked)');
        if ($block.length) {
            $block.each(function() {
                Elements.initDroppable($(this));
            });
        } else {
            if ($elem.data('droppable_inited')) {
                return;
            }
            $elem.data('droppable_inited', true);
            $elem.droppable({
                activeClass: 'ui-state-active',
                hoverClass: 'ui-state-hover',
                drop: function(e, ui) {
                    var $slot = $(this);

                    if (!$slot.hasClass('slot')) {
                        return;
                    }

                    var $page = $slot.closest('.queue-page'),
                        $post = $(ui.draggable).closest('.post');

                    Events.fire('post_moved', $post.data('id'), $slot.data('id'), $post.data('queue-id'), function() {
                        if ($post.hasClass('relocatable')) {
                            $slot.html($post);
                        }
                        var queuePageTimestamp = app.getRightPanelWidget().getQueueWidget().getPageTimestamp($page);
                        app.updateQueue(queuePageTimestamp);
                    });
                }
            });
        }
    },
    initLinks: function($block) {
        $block.find('img.ajax-loader').each(function(){
            Elements.initLinkLoader($(this), true);
        });
    },
    leftdd: function(){
        return $("#source-select").multiselect("getChecked").map(function(){
            return this.value;
        }).get();
    },
    rightdd: function(value){
        if (typeof value == 'undefined') {
            return $("#right-drop-down").data("selected");
        } else {
            $("#right-drop-down").dropdown('getMenu').find('.ui-dropdown-menu-item[data-id="' + value + '"]').mouseup();
        }
    },
    leftType: function(){
        return $('#left-panel .type-selector a.active').data('type');
    },
    rightType: function(){
        return $('#right-panel .type-selector a.active').data('type');
    },
    calendar: function(value){
        if (typeof value == 'undefined') {
            var time = $('#calendar').datepicker('getDate').getTime();
            var timestamp = Math.round(time / 1000) - (new Date().getTimezoneOffset() * 60) + 14400;
            return timestamp;
        } else {
            $('#calendar').datepicker('setDate', value).closest('.calendar').find('.caption').html('&nbsp;');
        }
    },
    initLinkLoader: function($link, full){
        var container   = $link.parents('div.link-info-content');
        var link        = $link.attr('rel');

        $.ajax({
            url: 'http://im.' + hostname + '/int/controls/parse-url/',
            type: 'GET',
            dataType: 'jsonp',
            data: {
                url: link
            },
            success: function (data) {
                if (full) {
                    container.html(linkTplFull);
                } else {
                    container.html(linkTplShort);
                }

                if (data.img) {
                    container.find('.link-img').css('background-image', 'url(' + data.img + ')');
                } else {
                    container.find('.link-img').remove();
                }
                if (data.title) {
                    container.find('.link-description-text a').text(data.title);
                }
                if (data.description) {
                    container.find('.link-description-text p').text(data.description);
                }

                container.find('a').attr('href', link);

                var matches = link.match(pattern);

                var shortLink = link;
                if (matches[2]) {
                    shortLink = matches[2];
                }
                container.find('.link-status-content span a').text(shortLink);
            }
        });
    },
    getUserGroupId: function() {
        return $('.user-groups-tabs').find('.tab.selected').data('user-group-id') || null;
    },
    getArticleStatus: function() {
        return $('.authors-tabs').find('.tab.selected').data('article-status') || 1;
    },
    getSortType: function() {
        return $('.wall-title a').data('type');
    },
    getWallLoader: function() {
        return $('#wall-load');
    },
    getSwitcherType: function() {
        return $('#wall-switcher').find('a:visible').data('type');
    }
};
