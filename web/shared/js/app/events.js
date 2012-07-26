/**
 * Events
 */
var Events = {
    delay: 200,
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
        var params = $.extend({
            type: null,
            clear: null,
            filter: null
        }, options);

        callback(true);
    },
    wall_post: function(options, callback) {
        var params = $.extend({
            publicId: null,
            text: null,
            photos: null
        }, options);

        $.ajax({
            url: controlsRoot + 'article-save/',
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
        callback(true);
    },

    comment_load: function(options, callback) {
        var params = $.extend({
            postId: null,
            offset: null,
            limit: null
        }, options);

        callback(true);
    },
    comment_post: function(postId, text, callback) {
        callback(true);
    },
    comment_delete: function(commentId, callback) {
        callback(true);
    }
};
$.extend(Events.eventList, Eventlist);