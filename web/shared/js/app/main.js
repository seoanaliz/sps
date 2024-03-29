window.name = window.name || 'fXD';

var VK = VK || {
    init: function(callback) {return callback()},
    callMethod: function(name) {},
    addCallback: function(name, cb) {},
    removeCallback: function(name, cb) {},
    api: function(name, p, cb) {}
};

$(document).ready(function() {
    new App();
});

var App = (function() {
    var easydateParams = {
        live: true,
        date_parse: function(date) {
            if (!date) {
                return '';
            }
            var d = date.split('.');
            return Date.parse([d[1], d[0], d[2]].join('/'));
        },
        uneasy_format: function(date) {
            return date.toLocaleDateString();
        }
    };

    var App = Class.extend({
        wallPage: 0,

        init: function() {
            var t = this;
            VK.init(function() {
                t._updateItems();
                t._bindEvents();
                t.$menu.find('.item.selected').click();
            });
        },

        _updateItems: function() {
            var t = this;
            t.$leftColumn = $('#left-column');
            t.$rightColumn = $('#right-column');
            t.$wall = $('#wall');
            t.$wallList = $('> .list', t.$wall);
            t.$loadMore = $('> .show-more', t.$wallList);
            t.$menu = $('#menu');
            t.$newPost = $('.new-post', t.$wall);
            t.$wallTabs = $('.tabs', t.$wall);
            t.$wallTitle = $('> .title', t.$wall);
            t.$wallList.find('textarea').placeholder();
            t.$wallList.find('.attachments').imageComposition();
            t.$wallList.find('.date').easydate(easydateParams);
            t.$wallList.find('.comment.new:first').closest('.comments').find('textarea').focus();
        },

        _bindEvents: function() {
            var t = this;
            var $menu = t.$menu;

            (function() {
                VK.callMethod('scrollSubscribe');
                VK.addCallback('onScroll', function(scrollTop) {
                    var $loadMore = t.$loadMore;
                    if ($loadMore.is(':visible') && scrollTop + screen.availHeight > ($loadMore.offset().top)) {
                        t.showMore();
                    }
                });
            })();

            (function() {
                var $window = $('#main');
                var lastHeight = 0;
                setInterval(function() {
                    if ($window.height() != lastHeight) {
                        t.refreshSize();
                        lastHeight = $window.height();
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

            $menu.delegate('.item', 'click', function() {
                var $selectedItem = $(this);
                var itemId = $selectedItem.data('id');
                $menu.find('.item.selected').removeClass('selected');
                $selectedItem.addClass('selected');
                if (itemId != 'my') {
                    $selectedItem.find('.counter').fadeOut(200);
                }
                t.appendPageData({
                    userGroupId: null
                });
            });
        },

        _bindLeftColumnEvents: function() {
            var t = this;
            var $wallTabs = t.$wallTabs;
            var $wallTitle = t.$wallTitle;
            var $leftColumn = t.$leftColumn;
            var $wallGroups = $leftColumn.find('#groups');

            $wallGroups.delegate('.tab', 'click', function() {
                var $tab = $(this);
                $wallGroups.find('.tab.selected').removeClass('selected');
                $tab.addClass('selected');
                t.appendPageData();
            });

            $wallTabs.delegate('.tab', 'click', function() {
                var $tab = $(this);
                $wallTabs.find('.tab.selected').removeClass('selected');
                $tab.addClass('selected');
                t.appendPageData({
                    articlesOnly: true
                }, function(data) {
                    t.$wallList.html(data);
                    t._updateItems();
                });
            });

            $wallTitle.find('#wall-switcher a').click(function() {
                var $target = $(this);
                $target.parent().find('a[data-switch-to="' + $target.data('mode') + '"]').show();
                $target.hide();
                t.appendPageData();
            });

            t._bindNewPostEvents();

            t._bindWallEvents();
        },

        _bindWallEvents: function() {
            var t = this;
            var $wall = t.$wall;

            $wall.delegate('.post .hight-light.new', 'hover', function(e) {
                if (e.type != 'mouseenter') return;
                var $hightLight = $(this);
                var $post = $hightLight.closest('.post');
                Events.fire('wall_mark_as_read', $post.data('id'), function() {
                    $hightLight.removeClass('new');
                    function decCounter($counter) {
                        if (!$counter.data('counter')) {
                            $counter.counter({prefix: '+'});
                        }
                        $counter.counter('decrement');
                    }
                    decCounter(t.$menu.find('.item.selected .counter'));
                    decCounter($wall.find('.tabs .tab.selected .counter'));
                });
            });
            $wall.delegate('.comment.new', 'hover', function(e) {
                if (e.type != 'mouseenter') return;
                var $comment = $(this);
                var $post = $comment.closest('.post');
                Events.fire('comment_mark_as_read', $post.data('id'), $comment.data('id'), function() {
                    $comment.removeClass('new');
                    var $counter = t.$menu.find('.item.selected .counter');
                    if (!$counter.data('counter')) {
                        $counter.counter({prefix: '+'});
                    }
                    $counter.counter('decrement');
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
            $wall.delegate('.show-cut', 'click', function() {
                var $text = $(this).closest('.text');
                var $shortcut = $text.find('.shortcut');
                var $cut = $text.find('.cut');

                $shortcut.hide();
                $cut.show();
            });
            $wall.delegate('.post > .delete', 'click', function() {
                var $target = $(this);
                var $post = $target.closest('.post');
                var postId = $post.data('id');
                Events.fire('wall_delete', postId, function() {
                    $post.data('html', $post.html());
                    $post.addClass('deleted').html('Сообщение удалено. <a class="restore">Восстановить</a>.');
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
            $wall.delegate('#wall > .list > .show-more', 'click', t.showMore);
            $wall.delegate('.new-comment textarea', 'focus', function() {
                if (!$(this).data('autoResize')) $(this).autoResize();
                var $newComment = $(this).closest('.new-comment');
                $newComment.addClass('open');
            });
            $wall.delegate('.new-comment textarea', 'keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.keyCode == KEY.ENTER) {
                    t._commentPost(this);
                }
            });
            $wall.delegate('.new-comment .send', 'click', function() {
                t._commentPost(this);
            });
            $wall.delegate('.comment > .delete', 'click', function() {
                var $target = $(this);
                var $comment = $target.closest('.comment');
                var commentId = $comment.data('id');
                Events.fire('comment_delete', commentId, function() {
                    $comment.data('html', $comment.html());
                    $comment.addClass('deleted').html('Комментарий удален. <a class="restore">Восстановить</a>.');
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
                Events.fire('comment_load', {
                    postId: postId,
                    all: true
                }, function(html) {
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
                Events.fire('comment_load', {
                    postId: postId,
                    all: false
                }, function(html) {
                    $target.removeClass('load').html(tmpText);
                    $commentsList.html(html).find('.date').easydate(easydateParams);
                });
            });
            $wall.delegate('.show-all-postponed', 'click', function() {
                var $target = $(this);
                if ($target.data('posts')) {
                    var $posts = $target.data('posts');
                    if ($posts.first().is(':visible')) {
                        $posts.hide();
                        $target.html($target.data('def-html'));
                    } else {
                        $posts.show();
                        $target.html('Скрыть записи в очереди');
                    }
                } else {
                    t.appendPageData({
                        mode: 'deferred',
                        articlesOnly: true
                    }, function(html) {
                        if (html) {
                            var $posts = $(html);
                            $target.after($posts);
                            $target.data('def-html', $target.html());
                            $target.html('Скрыть записи в очереди');
                            $target.data('posts', $posts);
                            t._updateItems();
                        } else {
                            $target.remove();
                        }
                    });
                }
            });
        },

        _bindNewPostEvents: function() {
            var t = this;
            var $newPost = t.$newPost;

            $newPost.find('textarea').placeholder();
            $newPost.find('textarea').bind('focus', function() {
                if (!$(this).data('autoResize')) $(this).autoResize();
                $newPost.addClass('open');
            });
            $newPost.find('textarea').bind('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.keyCode == KEY.ENTER) {
                    t._wallPost(this);
                }
            });
            $newPost.delegate('.send', 'click', function() {
                t._wallPost(this);
            });
            $newPost.delegate('.photo > .delete', 'click', function() {
                $(this).parent().remove();
            });
            if ($newPost.find('.file-uploader').length) {
                var uploader = new qq.FileUploader({
                    element: $newPost.find('.file-uploader')[0],
                    action: window.root + 'int/controls/image-upload/',
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
            }
        },

        _wallPost: function(target) {
            var t = this;
            var $target = $(target);
            var $post = $target.closest('.new-post');
            var $button = $post.find('.send:not(.load)');
            var $textarea = $post.find('textarea');
            var $photos = $post.find('.attachments > .photos');
            var photos = [];
            var text = $textarea.val();

            $photos.find('img').each(function() {
                photos.push({filename: $(this).data('name')});
            });

            if (!text && !photos.length) {
                $textarea.focus();
            } else {
                $button.addClass('load');
                var publicId = t.$menu.find('.item.selected').data('id');
                var groupId = $('#groups').find('.tab.selected').data('id');
                Events.fire('wall_post', {
                    text: text,
                    publicId: publicId,
                    photos: photos,
                    userGroupId: groupId
                }, function() {
                    $button.removeClass('load');
                    $textarea.val('').focus();
                    $photos.html('');
                    t.appendPageData();
                });
            }
        },

        _commentPost: function(target) {
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
        },

        showMore: function() {
            var t = this;
            if (t.$loadMore.hasClass('load')) {
                return;
            }
            t.$loadMore.addClass('load').html('&nbsp;');
            t.appendPageData({
                articlesOnly: true,
                page: ++t.wallPage
            }, function(data) {
                t.$loadMore.remove();
                t.$wallList.append(data);
                t._updateItems();
            });
        },

        appendPageData: function(options, callback) {
            var t = this;
            var $selectedTab = $('#groups').find('.tab.selected');
            var $selectedItem = t.$menu.find('.item.selected');
            var $selectedStatus = $('#statuses').find('.tab.selected');
            var $selectedMode = $('#wall-switcher').find('a:visible');
            var params = $.extend({
                type: $selectedItem.data('id'),
                userGroupId: $selectedTab.data('id'),
                articleStatus: $selectedItem.data('id') == 'my' ? $selectedStatus.data('article-status') : null,
                mode: $selectedMode.data('mode'),
                articlesOnly: false,
                page: 0
            }, options);
            t.wallPage = params.page;
            if (typeof callback != 'function') {
                callback = function(data) {
                    if (params.articlesOnly) {
                        t.$wallList.html(data);
                        t._updateItems();
                    } else {
                        t.$leftColumn.html(data);
                        t._updateItems();
                        t._bindLeftColumnEvents();
                    }
                }
            }
            Events.fire('wall_load', params, callback);
        },

        refreshSize: function() {
            VK.callMethod('resizeWindow', false, $('#main').outerHeight());
        }
    });

    App.ARTICLE_STATUS_REVIEWING = 1;
    App.ARTICLE_STATUS_APPROVED = 2;
    App.ARTICLE_STATUS_REJECTED = 3;

    return App;
})();
