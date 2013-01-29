/**
 * Events
 * @var string appControlsRoot
 */
var Events = {
    delay: 0,
    eventList: {},
    fire: function(name, args){
        var t = this;
        args = Array.prototype.slice.call(arguments, 1);
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    if(window.console && console.log) {
                        console.log(name + ':');
                        console.log(args.slice(0, -1));
                        console.log('-------');
                    }
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if (window.console && console.log) {
                    console.log(e);
                }
            }
        }
    }
};

var Eventlist = {
    wall_load: function(options, callback) {
        var defaultOptions = {
            // my, ID ленты
            type: null,
            // all, queued, sent - статус записи
            tabType: null,
            // ID группы автора
            userGroupId: null,
            // Загрузить только записи, без поля ввода и т.д.
            articlesOnly: false,
            // best, my, ...
            filter: null,
            // Смещение записей
            page: -1,
            // my, deferred
            mode: null
        };
        options = $.extend(defaultOptions, options);

        $.ajax({
            url: appControlsRoot + 'articles-list/',
            dataType : "html",
            data: options,
            success: function (data) {
                callback(data);
            }
        });
    },
    wall_post: function(options, callback) {
        var params = $.extend({
            publicId: null,
            text: null,
            photos: null
        }, options);

        $.ajax({
            url: appControlsRoot + 'article-save/',
            type: 'POST',
            dataType : "json",
            data: params,
            success: function (data) {
                if(data.success) {
                    callback(true);
                } else {
                    callback(false);
                }
            }
        });
    },
    wall_delete: function(postId, callback) {
        $.ajax({
            url: appControlsRoot + 'article-delete/',
            data: {
                id: postId
            },
            success: function (data) {
                callback(true);
            }
        });
    },
    wall_restore: function(postId, callback) {
        $.ajax({
            url: appControlsRoot + 'article-restore/',
            data: {
                id: postId
            },
            success: function (data) {
                callback(true);
            }
        });
    },
    wall_mark_as_read: function(postId, callback) {
        $.ajax({
            url: appControlsRoot + 'article-mark/',
            data: {
                id: postId
            },
            success: function (data) {
                callback(true);
            }
        });
    },

    comment_load: function(options, callback) {
        var params = $.extend({
            postId: null,
            all: true
        }, options);

        $.ajax({
            url: appControlsRoot + 'comments-load/',
            data: params,
            success: function (data) {
                callback(data);
            }
        });
    },
    comment_post: function(postId, text, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-save/',
            type: 'POST',
            data: {
                id: postId,
                text: text
            },
            success: function (data) {
                callback(data);
            }
        });
    },
    comment_delete: function(commentId, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-delete/',
            data: {
                id: commentId
            },
            success: function (data) {
                callback(true);
            }
        });
    },
    comment_restore: function(commentId, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-restore/',
            data: {
                id: commentId
            },
            success: function (data) {
                callback(true);
            }
        });
    },
    comment_mark_as_read: function(postId, commentId, callback) {
        $.ajax({
            url: appControlsRoot + 'comment-mark/',
            data: {
                articleId: postId,
                commentId: commentId
            },
            success: function (data) {
                callback(true);
            }
        });
    }
};
$.extend(Events.eventList, Eventlist);
