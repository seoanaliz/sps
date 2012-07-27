window.name = window.name || 'fXD';

var VK = VK || {
    init: function(callback) {return callback()},
    callMethod: function(name) {},
    addCallback: function(name, cb) {},
    removeCallback: function(name, cb) {},
    api: function(name, p, cb) {}
};

$(document).ready(function() {
    VK.init(function() {
        var apiResult = getURLParameter('api_result');
        var obj = window.JSON && JSON.parse(apiResult) || $.parseJSON(apiResult);
    });

    app.init();
});

var app = (function () {
    var isInitialized = false;
    var $leftColumn;
    var $rightColumn;
    var $wall;
    var $wallList;
    var $loadMore;
    var $menu;
    var $newPost;

    function init() {
        if (isInitialized) return;

        _updateItems();
        _initEvents();
        pageLoad($menu.find('.item.selected').data('id'));

        isInitialized = true;
    }

    function _updateItems() {
        $leftColumn = $('#left-column');
        $rightColumn = $('#right-column');
        $wall = $('#wall');
        $wallList = $('> .list', $wall);
        $loadMore = $('> .show-more', $wallList);
        $menu = $('#menu');
        $newPost = $('.new-post', $wall);

        $wall.find('.comment textarea').placeholder();
        $wall.find('.date').easydate({
            live: true,
            date_parse: function(date) {
                if (!date) return;
                var d = date.split('.');
                var i = d[1];
                d[1] = d[0];
                d[0] = i;
                return Date.parse(d.join('.'));
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
    }

    function _initEvents() {
        /*Window*/
        (function() {
            var w = $(window);

            VK.callMethod('scrollSubscribe');
            VK.addCallback('onScroll', function(scrollTop) {
                if ($loadMore.is(':visible') && scrollTop + screen.availHeight > ($loadMore.offset().top)) {
                    $loadMore.click();
                }
            });
        })();

        (function() {
            var $window = $(document);
            var lastHeight = 0;
            setInterval(function() {
                if ($window.outerHeight() != lastHeight) {
                    refreshSize();
                    lastHeight = $window.outerHeight();
                }
            }, 100);
        })();

        $(document).bind('click', function(e) {
            var $newPost = $(e.target).closest('.new-post.open');
            if (!$newPost.length) {
                $('.new-post.open').each(function() {
                    var $post = $(this);
                    var $textarea = $post.find('textarea');
                    if (!$post.find('textarea').val()) {
                        $post.removeClass('open');
                        $textarea.height('auto');
                    }
                });
            }

            var $newComment = $(e.target).closest('.new-comment.open');
            if (!$newComment.length) {
                $('.new-comment.open').each(function() {
                    var $comment = $(this);
                    var $textarea = $comment.find('textarea');
                    if (!$textarea.val()) {
                        $comment.removeClass('open');
                        $textarea.height('auto');
                    }
                });
            }
        });

        /*Left column*/
        $wall.find('> .title > .dropdown').dropdown({
            position: 'right',
            width: 'auto',
            data: [
                {title: 'мои записи', type : 'my'},
                {title: 'лучшие записи', type : 'best'},
                {title: 'последние записи', type : 'new'}
            ],
            onchange: function(node) {
                $(this).text(node.title);
                pageLoad($menu.find('.item.selected').data('id'), node.type);
            }
        });

        $newPost.find('textarea').placeholder();

        $newPost.find('textarea').bind('focus', function() {
            $(this).autoResize();
            $newPost.addClass('open');
        });
        $newPost.find('textarea').bind('keyup', function(e) {
            if (e.ctrlKey && e.keyCode == 13) {
                _wallPost(this);
            }
        });
        $newPost.find('.send').bind('click', function() {
            _wallPost(this);
        });

        $wall.delegate('.post > .delete', 'click', function() {
            var $target = $(this);
            var $post = $target.closest('.post');
            var postId = $post.data('id');
            Events.fire('wall_delete', postId, function() {
                $post.data('html', $post.html());
                $post.addClass('deleted').html('Сообщение удалено. <a class="restore" href="javascript:;">Восстановить</a>.');
            });
        });
        $wall.delegate('.post.deleted > .restore', 'click', function() {
            var $target = $(this);
            var $post = $target.closest('.post');
            var postId = $post.data('id');
            Events.fire('wall_restore', postId, function() {
                $post.removeClass('deleted').html($post.data('html'));
            });
        });
        $wall.delegate('#wall > .list > .show-more', 'click', function() {
            showMore();
        });
        $wall.delegate('.new-comment textarea', 'focus', function() {
            $(this).autoResize();
            var $newComment = $(this).closest('.new-comment');
            $newComment.addClass('open');
        });
        $wall.delegate('.new-comment textarea', 'keyup', function(e) {
            if (e.ctrlKey && e.keyCode == 13) {
                _commentPost(this);
            }
        });
        $wall.delegate('.new-comment .send', 'click', function() {
            _commentPost(this);
        });
        $wall.delegate('.comment > .delete', 'click', function() {
            var $target = $(this);
            var $comment = $target.closest('.comment');
            var commentId = $comment.data('id');
            Events.fire('comment_delete', commentId, function() {
                //todo: восстановление сообщения
                $comment.remove();
            });
        });
        $wall.delegate('.comments .show-more:not(.hide):not(.load)', 'click', function() {
            var $target = $(this);
            var $comment = $target.closest('.comment');
            var commentId = $comment.data('id');
            var tmpText = $target.text();
            $target.addClass('load').html('&nbsp;');
            Events.fire('comment_load', {}, function() {
                $target.removeClass('load').html(tmpText);
            });
        });
        $wall.delegate('.comments .show-more.hide:not(.load)', 'click', function() {
            var $target = $(this);
            var $comment = $target.closest('.comment');
            var commentId = $comment.data('id');
            var tmpText = $target.text();
            $target.addClass('load').html('&nbsp;');
            Events.fire('comment_load', {}, function() {
                $target.removeClass('load').html(tmpText);
            });
        });

        /*Right column*/
        $menu.delegate('.item', 'click', function() {
            var $item = $(this);
            pageLoad($item.data('id'));
        });
    }

    function _wallPost(target) {
        var $target = $(target);
        var $post = $target.closest('.new-post');
        var $button = $post.find('.send:not(.load)');
        var $textarea = $post.find('textarea');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('wall_post', {text: $textarea.val()}, function() {
                $button.removeClass('load');
                $textarea.val('').focus();
                pageLoad();
            });
        }
    }

    function _commentPost(target) {
        var $target = $(target);
        var $comment = $target.closest('.new-comment');
        var $textarea = $comment.find('textarea');
        var $button = $comment.find('.send:not(.load)');
        var $post = $comment.closest('.post');
        var postId = $post.data('id');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('comment_post', postId, $textarea.val(), function(data) {
                $button.removeClass('load');
                $textarea.val('').focus();
                //todo: data должна быть последними комментами
            });
        }
    }

    function showMore() {
        var tmpText = $loadMore.text();

        if ($loadMore.hasClass('load')) return;
        $loadMore.addClass('load').html('&nbsp;');
        Events.fire('wall_load', {clear: false}, function(data) {
            $loadMore.remove();
            $wallList.append(data);
            _updateItems();
        });
    }

    function pageLoad(id, filter) {
        Events.fire('wall_load', {clear: true, type: id, filter: filter}, function(data) {
            if (id) {
                var $targetItem = $menu.find('.item[data-id="' + id + '"]');
                var $targetList = $targetItem.next('.list');
                var $selectedItem = $menu.find('.item.selected').not($targetItem);
                var $selectedList = $menu.find('.list.selected');

                $targetItem.addClass('selected');
                $targetList.addClass('selected').slideDown(100);

                $selectedItem.removeClass('selected');
                if ($selectedList[0] && $selectedList[0] != $targetItem.closest('.list')[0]) {
                    $selectedList.removeClass('selected').slideUp(100);
                }
            }

            $wallList.html(data);
            _updateItems();
        });
    }

    function refreshSize() {
        VK.callMethod('resizeWindow', false, $('body').outerHeight());
    }

    return {
        init: init,
        showMore: showMore,
        refreshSize: refreshSize
    };
})();

// Парсинг URL
function getURLParameter(name) {
    return decodeURIComponent((new RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
}

// Автовысота у textarea
(function($) {
    $.fn.autoResize = function() {
        return this.each(function() {
            var $input = $(this);
            var $autoResize = $('<div/>').appendTo('body');
            if (!$input.data('autoResize')) {
                $input.data('autoResize', $autoResize);
                $autoResize
                    .css({
                        width: $input.width(),
                        minHeight: $input.height(),
                        padding: $input.css('padding'),
                        lineHeight: $input.css('line-height'),
                        font: $input.css('font'),
                        fontSize: $input.css('font-size'),
                        position: 'absolute',
                        wordWrap: 'break-word',
                        top: -100000
                    })
                ;
                $input.bind('keyup focus blur', function(e) {
                    $autoResize.html($input.val().split('\n').join('<br/>.') + '<br/>.');
                    $input.css('height', $autoResize.height());
                });
            }
        });
    };
})(jQuery);

// Кроссбраузерные плейсхолдеры
(function($) {
    $.fn.placeholder = function(para) {
        return this.each(function(parameters) {
            var defaults = {
                el: this,
                color: '#CCC',
                text: false,
                helperClass: 'placeholder'
            };
            var t = this;
            var p = t.p = $.extend(defaults, parameters);
            var $input = $(p.el);
            var placeholderText = p.text || $input.attr('placeholder');
            var $wrapper = $('<div/>');
            var $placeholder = $('<div/>').addClass(p.helperClass).text(placeholderText).css({
                padding: $input.css('padding')
            });

            $input
                .wrap($wrapper)
                .data('placeholder', $placeholder)
                .removeAttr('placeholder')
                .parent().prepend($placeholder)
            ;

            $placeholder.bind('mousedown', function() {
                $placeholder.hide();
                $input.focus();
            });
            $placeholder.bind('mouseup', function() {
                $input.focus();
            });
            $input.bind('blur', function() {
                if (!$input.val()) {
                    $placeholder.fadeIn(100);
                }
            });
            $input.bind('focus', function() {
                $placeholder.hide();
            });
        });
    };
})(jQuery);

// Дропдауны
(function($) {
    var CLASS_ACTIVE = 'active';
    var CLASS_MENU = 'ui-dropdown-menu';
    var CLASS_MENU_ITEM = 'ui-dropdown-menu-item';
    var CLASS_MENU_ITEM_ACTIVE = 'active';
    var CLASS_MENU_ICON = 'icon';
    var CLASS_MENU_ICON_LEFT = 'icon-left';
    var CLASS_MENU_ICON_RIGHT = 'icon-right';
    var CLASS_MENU_ITEM_WITH_ICON_LEFT = 'icon-left';
    var CLASS_MENU_ITEM_WITH_ICON_RIGHT = 'icon-right';

    var methods = {
        init: function(parameters) {
            return this.each(function() {
                var defaults = {
                    el: $(this),
                    type: 'normal',
                    width: '',
                    addClass: '',
                    position: 'left',
                    iconPosition: 'left',
                    openEvent: 'mousedown',
                    dataKey: 'dropdown',
                    data: [{}],
                    onchange: function() {},
                    oncreate: function() {},
                    onopen: function() {},
                    onclose: function() {}
                };
                var t = this;
                var p = t.p = $.extend(defaults, parameters);
                var $el = p.el;

                if ($el.data(p.dataKey) || !p.data) return false;

                $el.data(p.dataKey, $('<div></div>').attr({class: CLASS_MENU + ' ' + p.addClass}).appendTo('body'));
                $(p.data).each(function(i, item) {
                    var $item = $('<div>' + item.title + '</div>').attr({class: CLASS_MENU_ITEM});
                    if (item.id) {
                        $item.data('id', item.id);
                    }
                    if (item.icon) {
                        var $icon = $('<div><img src="' + item.icon + '" /></div>');
                        $item.append($icon);
                        if (p.iconPosition == 'left') {
                            $icon.attr({class: CLASS_MENU_ICON + ' ' + CLASS_MENU_ICON_LEFT});
                            $item.addClass(CLASS_MENU_ITEM_WITH_ICON_LEFT);
                        } else {
                            $icon.attr({class: CLASS_MENU_ICON + ' ' + CLASS_MENU_ICON_RIGHT});
                            $item.addClass(CLASS_MENU_ITEM_WITH_ICON_RIGHT);
                        }
                    }
                    if (item.isActive) {
                        $item.addClass(CLASS_MENU_ITEM_ACTIVE);
                    }
                    $item.mouseup(function() {
                        if (p.type == 'checkbox') {
                            $el.data(p.dataKey).find('.' + CLASS_MENU_ITEM).removeClass(CLASS_MENU_ITEM_ACTIVE);
                            $item.addClass(CLASS_MENU_ITEM_ACTIVE);
                        }
                        close();
                        run(p.onchange, $el, item);
                        $el.trigger('change');
                    });
                    $el.data(p.dataKey).append($item);
                });

                $el.bind(p.openEvent, function(e) {
                    e.stopPropagation();
                    if (!$el.data(p.dataKey).is(':visible')) {
                        $('html, body').trigger(p.openEvent);
                        open();
                    } else {
                        close();
                    }
                });

                $el.data(p.dataKey).bind(p.openEvent, function(e) {
                    e.stopPropagation();
                });

                $('html, body').bind(p.openEvent, function(e) {
                    close();
                    run(p.onclose, $el, $el.data(p.dataKey));
                });

                function open() {
                    $el.addClass(CLASS_ACTIVE);
                    $el.data(p.dataKey).css({
                        width: p.width || $el.width()
                    });

                    var $menu = $el.data(p.dataKey);
                    var isFixed = !!($menu.css('position') == 'fixed');
                    var offset = $el.offset();
                    var offsetTop = offset.top;
                    var offsetLeft = offset.left
                        + parseFloat($menu.css('margin-left'))
                        - parseFloat($menu.css('margin-right'));
                    if (p.position == 'right') {
                        offsetLeft += ($el.width() - $menu.width())
                    }
                    if (isFixed) {
                        offsetTop -= $(document).scrollTop();
                        offsetLeft -= $(document).scrollLeft();
                    }

                    $menu.css({
                        top: offsetTop + $el.outerHeight(),
                        left: offsetLeft
                    }).show();
                    run(p.onopen, $el, $el.data(p.dataKey));
                    $el.trigger('open');
                }

                function close() {
                    $el.removeClass(CLASS_ACTIVE);
                    $el.data(p.dataKey).hide();
                    run(p.onclose, $el, $el.data(p.dataKey));
                    $el.trigger('close');
                }

                run(p.oncreate, $el);
                $el.trigger('create');
            });

            function run(f, context, argument) {
                if ($.isFunction(f)) {
                    f.call(context, argument);
                }
            }
        }
    };

    $.fn.dropdown = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.dropdown');
        }
    };
})(jQuery);