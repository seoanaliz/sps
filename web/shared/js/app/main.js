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
    var easydateParams = {
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
    };

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

        $wallList.find('textarea').placeholder();
        $wallList.find('.attachments').imageComposition();
        $wallList.find('.date').easydate(easydateParams);
        $wallList.find('.comment.new:first').closest('.comments').find('textarea').focus();
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
            if (!$(this).data('autoResize')) $(this).autoResize();
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

        $wall.delegate('.post .hight-light.new', 'hover', function() {
            var $hightLight = $(this);
            var $post = $hightLight.closest('.post');
            Events.fire('wall_mark_as_read', $post.data('id'), function() {
                $hightLight.removeClass('new');
            });
        });
        $wall.delegate('.comment.new', 'hover', function() {
            var $comment = $(this);
            var $post = $comment.closest('.post');
            Events.fire('comment_mark_as_read', $post.data('id'), $comment.data('id'), function() {
                $comment.removeClass('new');
            });
        });
        $wall.delegate('.show-new-comment', 'click', function() {
            var $post = $(this).closest('.post');
            var $newComment = $post.find('.new-comment');
            $post.toggleClass('no-comments');
            if (!$post.hasClass('no-comments')) {
                $newComment.find('textarea').focus();
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
            if (!$(this).data('autoResize')) $(this).autoResize();
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
            var $comment = $target.closest('.comment');
            var commentId = $comment.data('id');
            Events.fire('comment_restore', commentId, function() {
                $comment.removeClass('deleted').html($comment.data('html'));
            });
        });
        $wall.delegate('.comments .show-more:not(.hide):not(.load)', 'click', function() {
            var $target = $(this);
            var $post = $target.closest('.post');
            var $commentsList = $('.comments > .list', $post);
            var postId = $post.data('id');
            var tmpText = $target.text();
            $target.addClass('load').html('&nbsp;');
            Events.fire('comment_load', {postId: postId, all: true}, function(html) {
                $target.removeClass('load').html(tmpText);
                $commentsList.html(html).find('.date').easydate(easydateParams);
            });
        });
        $wall.delegate('.comments .show-more.hide:not(.load)', 'click', function() {
            var $target = $(this);
            var $post = $target.closest('.post');
            var $commentsList = $('.comments > .list', $post);
            var postId = $post.data('id');
            var tmpText = $target.text();
            $target.addClass('load').html('&nbsp;');
            Events.fire('comment_load', {postId: postId, all: false}, function(html) {
                $target.removeClass('load').html(tmpText);
                $commentsList.html(html).find('.date').easydate(easydateParams);
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
                    addClass: 'wall-dropdown-menu',
                    data: dropdownItems,
                    oncreate: filterWall,
                    onupdate: filterWall,
                    onchange: function(item) {
                        $(this).text(item.title);
                        pageLoad($menu.find('.item.selected').data('id'), item.type);
                    }
                });
                function filterWall() {
                    var $defItem = $(this).dropdown('getMenu').find('div:first');
                    var itemData = $defItem.data('item');
                    $(this).text(itemData.title);
                    pageLoad(itemId, itemData.type);
                }
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
        var $commentsList = $('.comments > .list', $post);
        var postId = $post.data('id');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('comment_post', postId, $textarea.val(), function(html) {
                $button.removeClass('load');
                $textarea.val('').focus();
                $commentsList.append(html).find('.date').easydate(easydateParams);
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

(function($) {
    var PLUGIN_NAME = 'counter';
    var DATA_KEY = PLUGIN_NAME;

    var methods = {
        init: function(params) {
            return this.each(function() {
                var defaults = {
                    prefix: ''
                };
                var options = $.extend(defaults, params);
                var $el = $(this);

                $el.data(DATA_KEY, {
                    $el: $el,
                    options: options
                });
            });
        },
        increment: function(num) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                num = num || 1;

                $el.html(options.prefix + intval($el.html()) + num);
            });
        },
        decrement: function(num) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                num = num || 1;

                $el.html(options.prefix + intval($el.html()) - num);
            });
        }
    };

    $.fn[PLUGIN_NAME] = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.' + PLUGIN_NAME);
        }
    };
})(jQuery);