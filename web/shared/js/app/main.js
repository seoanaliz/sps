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
        $menu.find('.item.selected').click();

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
        $wallList.find('.attachments').imageComposition();
        $wallList.find('.date').easydate({
            live: true,
            date_parse: function(date) {
                if (!date) return;
                var d = date.split('.');
                var i = d[1];
                d[1] = d[0];
                d[0] = i;
                return Date.parse(d.join('/'));
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

        $(document).bind('mousedown', function(e) {
            var $newPost = $(e.target).closest('.new-post.open');
            if (!$newPost.length) {
                $('.new-post.open').each(function() {
                    var $post = $(this);
                    var $textarea = $post.find('textarea');
                    var $photos = $post.find('.attachments > .photos img');
                    var text = $post.find('textarea').val();

                    if (!text && !$photos.length) {
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
        $newPost.delegate('.send', 'click', function() {
            _wallPost(this);
        });
        $newPost.delegate('.photo > .delete', 'click', function() {
            $(this).parent().remove();
        });
        var uploader = new qq.FileUploader({
            element: $newPost.find('.file-uploader')[0],
            action: root + 'int/controls/image-upload/',
            template: '<div class="qq-uploader">' +
                '<div class="qq-upload-drop-area">Перенесите картинки сюда</div>' +
                '<div class="qq-upload-button">Прикрепить</div>' +
                '<ul class="qq-upload-list"></ul>' +
                '</div>',
            onComplete: function(id, fileName, res) {
                var $photo = $(
                    '<div class="photo">' +
                        '<img src="' + res.image + '" data-name="' + res.filename + '" />' +
                        '<div class="delete"></div>' +
                    '</div>'
                );
                $newPost.find('.attachments > .photos').append($photo);
            }
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
                $comment.data('html', $comment.html());
                $comment.addClass('deleted').html('Комментарий удален. <a class="restore" href="javascript:;">Восстановить</a>.');
            });
        });
        $wall.delegate('.comment.deleted > .restore', 'click', function() {
            var $target = $(this);
            var $comment = $target.closest('.post');
            var commentId = $comment.data('id');
            Events.fire('comment_restore', commentId, function() {
                $comment.removeClass('deleted').html($comment.data('html'));
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
            var itemId = $item.data('id');
            var isEmpty = $item.data('empty');

            (function() {
                var dropdownItems = [
                    {title: 'мои записи', type: 'my'},
                    {title: 'лучшие записи', type: 'best'},
                    {title: 'последние записи', type: 'new'}
                ];

                if (itemId == 'my') {
                    dropdownItems = [
                        {title: 'мои записи', type: 'my'},
                        {title: 'лучшие записи', type: 'best'}
                    ];
                }
                if (isEmpty) {
                    dropdownItems = [
                        {title: 'лучшие записи', type: 'best'},
                        {title: 'последние записи', type: 'new'}
                    ];
                }

                $wall.find('> .title > .dropdown').dropdown({
                    position: 'right',
                    width: 'auto',
                    data: dropdownItems,
                    oncreate: function() {
                        var $defItem = $(this).data('dropdown').find('div:first');
                        var itemData = $defItem.data('item');
                        $(this).text(itemData.title);
                        pageLoad(itemId, itemData.type);
                    },
                    onchange: function(item) {
                        $(this).text(item.title);
                        pageLoad($menu.find('.item.selected').data('id'), item.type);
                    }
                });
            })();
        });
    }

    function _wallPost(target) {
        var $target = $(target);
        var $post = $target.closest('.new-post');
        var $button = $post.find('.send:not(.load)');
        var $textarea = $post.find('textarea');
        var $photos = $post.find('.attachments > .photos');
        var photos = [];
        var text = $textarea.val();

        $photos.find('img').each(function() {
            photos.push({'filename': $(this).data('name')});
        });

        if (!text && !photos.length) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('wall_post', {text: text, photos: photos}, function() {
                $button.removeClass('load');
                $textarea.val('').focus();
                $photos.html('');
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
    var TRIGGER_OPEN = 'open';
    var TRIGGER_CLOSE = 'close';
    var TRIGGER_CHANGE = 'change';
    var TRIGGER_CREATE = 'create';
    var ITEM_DATA_KEY = 'item';

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
                var $menu = $('<div></div>').attr({class: CLASS_MENU + ' ' + p.addClass}).appendTo('body');

                if (!p.data) return false;
                if ($el.data(p.dataKey)) {
                    $el.data(p.dataKey).remove();
                } else {
                    $('html, body').bind(p.openEvent, function(e) {
                        var $menu = $el.data(p.dataKey);
                        close($menu);
                        run(p.onclose, $el, $menu);
                    });
                    $el.bind(p.openEvent, function(e) {
                        var $menu = $el.data(p.dataKey);
                        e.stopPropagation();
                        if (!$menu.is(':visible')) {
                            $('html, body').trigger(p.openEvent);
                            open($menu);
                        } else {
                            close($menu);
                        }
                    });
                }
                $el.data(p.dataKey, $menu);

                $(p.data).each(function(i, item) {
                    var $item = $('<div/>')
                            .text(item.title)
                            .addClass(CLASS_MENU_ITEM)
                            .data('id', item.id)
                            .data(ITEM_DATA_KEY, item)
                            .appendTo($menu)
                        ;

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
                });
                $menu.delegate('.' + CLASS_MENU_ITEM, 'mouseup', function() {
                    close($menu);
                    select($(this));
                });
                $menu.bind(p.openEvent, function(e) {
                    e.stopPropagation();
                });

                function open($menu) {
                    $el.addClass(CLASS_ACTIVE);
                    $menu.css({
                        width: p.width || $el.width()
                    });

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

                    run(p.onopen, $el, $menu);
                    $el.trigger(TRIGGER_OPEN);
                }

                function close($menu) {
                    $el.removeClass(CLASS_ACTIVE);
                    $menu.hide();
                    run(p.onclose, $el, $menu);
                    $el.trigger(TRIGGER_CLOSE);
                }

                function select($item) {
                    var data = $item.data(ITEM_DATA_KEY);
                    if (p.type == 'checkbox') {
                        $menu.find('.' + CLASS_MENU_ITEM).removeClass(CLASS_MENU_ITEM_ACTIVE);
                        $item.addClass(CLASS_MENU_ITEM_ACTIVE);
                    }
                    run(p.onchange, $el, data);
                    $el.trigger(TRIGGER_CHANGE);
                }

                run(p.oncreate, $el);
                $el.trigger(TRIGGER_CREATE);
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
