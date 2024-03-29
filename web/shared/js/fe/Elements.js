var Elements = {
    initImages: function($block) {
        $block.find(".fancybox-thumb").fancybox({
            prevEffect: 'none',
            nextEffect: 'none',
            closeBtn:false,
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
        $block.find('.images').imageComposition();
        setTimeout(function () {
            $block.find('.expanded-post .images-ready').imageComposition();
        }, 60);
    },
    initDraggable: function($elem, islog) {
        var $block = $elem.find('.post');
        if ($block.length) {
            $block.each(function() {
                Elements.initDraggable($(this));
            });
        } else if ($elem.is('.movable:not(.blocked)')) {
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
    initDroppable: function() {
        $('.queue-page').find('.slot.empty:not(.locked)').each(function() {
            Elements.attachDroppable($(this));
        });
    },
    attachDroppable: function($elem) {
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

                var $post = $(ui.draggable).closest('.post');
                var postId = $post.data('id');
                if ($post.hasClass("external")) {
                    var externalId = postId;
                }
                Events.fire('post_moved', postId, $slot.data('id'), $post.data('queue-id'), externalId, function(isSuccess, data) {
                    if (isSuccess && data.success && data.html) {
                        var maybeSlot = ui.draggable.closest('.slot'); // кодга перетаскиваем из одной ячейки в другую, очистим ячейку-источник
                        if (maybeSlot.length) {
                            app.getRightPanelWidget().getQueueWidget().deleteArticleInSlot(maybeSlot, /* isEmpty */ true);
                        }
  
                        app.getRightPanelWidget().getQueueWidget().setSlotArticleHtml($slot, data.html);
                        if ($post.hasClass('relocatable')) { // скроем в "источниках"
                            $post.addClass('hidden_' + data.id).hide();
                        }
                    } else if (data.message) {
                        popupError(data.message, { timeout: 1500 });
                    }
                });
            }
        });
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
        if (typeof value === 'undefined') {
            return $("#right-drop-down").data("selected");
        } else {
            $("#right-drop-down").dropdown('getMenu').find('.ui-dropdown-menu-item[data-id="' + value + '"]').mouseup();
        }
    },
    // вконтактовский id текущего выбранного справа паблика
    currentExternalId: function() {
        return app.getRightPanelWidget().currentExternalId;
    },
    leftType: function(){
        return $('#left-panel .type-selector a.active').data('type');
    },
    rightType: function(){
        return $('#right-panel .type-selector a.active').data('type');
    },
    calendar: function(value){
        if (typeof value === 'undefined') {
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
