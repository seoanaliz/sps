window.name = window.name || 'fXD';

var VK = VK || {
    init: function(callback) {return callback()},
    callMethod: function(name) {},
    addCallback: function(name, cb) {},
    removeCallback: function(name, cb) {},
    api: function(name, p, cb) {}
};

var app = (function () {
    var isInitialized = false;
    var $wall;
    var $newPost;

    function init(apiResult) {
        if (isInitialized) return;

        $wall = $('#wall');
        $newPost = $('.new-post', $wall);
        //$wall.find('.photo img').attr('src', apiResult.response.viewer[0].photo);

        _initEvents();

        isInitialized = true;
    }

    function _initEvents() {
        VK.callMethod('scrollSubscribe');
        VK.addCallback('onScroll', function(scrollTop) {});

        $wall.find('.date').easydate({
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

        $wall.delegate('.show-more', 'click', function() {});
    }

    function _wallPost(target) {
        var $target = $(target);
        var $post = $target.closest('.new-post');
        var $button = $post.find('.send');
        var $textarea = $post.find('textarea');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
        }
    }

    function _commentPost(target) {
        var $target = $(target);
        var $comment = $target.closest('.new-comment');
        var $textarea = $comment.find('textarea');
        var $button = $comment.find('.send');
        var $post = $comment.closest('.comment');
        var postId = $post.data('id');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('comment_post', postId, $textarea.val(), function() {
                $button.removeClass('load');
            });
        }
    }

    function refreshSize() {
        VK.callMethod('resizeWindow', false, $('body').outerHeight());
    }

    return {
        init: init,
        refreshSize: refreshSize
    };
})();

$(document).ready(function() {
    VK.init(function() {
        var apiResult = getURLParameter('api_result');
        var obj = window.JSON && JSON.parse(apiResult) || $.parseJSON(apiResult);

        app.init(obj);
    });
});

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
                $input.bind('keyup', function(e) {
                    $autoResize.html($input.val().split('\n').join('<br/>.') + '<br/>.');
                    $input.css('height', $autoResize.height());
                });
            }
        });
    };
})(jQuery);