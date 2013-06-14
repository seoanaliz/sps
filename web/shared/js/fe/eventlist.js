Control = $.extend(Control, {
    root: controlsRoot,
    type: 'GET',
    dataType: 'html',

    controlMap: {
        get_articles: {
            name: 'articles-list'
        },
        authors_get: {
            name: 'authors-list'
        },
        author_remove: {
            name: 'author-delete',
            params: {
                authorId: 'vkId'
            }
        },
        author_add: {
            name: 'author-add',
            params: {
                authorId: 'vkId'
            }
        },
        add_list: {
            name: 'add-user-group',
            dataType: 'json'
        },
        add_to_list: {
            name: 'add-user-to-group',
            params: {
                userId: 'vkId',
                listId: 'userGroupId'
            }
        },
        remove_from_list: {
            name: 'remove-user-from-group',
            params: {
                userId: 'vkId',
                listId: 'userGroupId'
            }
        },
        get_source_list: {
            name: 'source-feeds-list',
            dataType: 'json'
        },
        accept_article: {
            name: 'article-approved',
            params: {
                articleId: 'id'
            }
        },
        decline_article: {
            name: 'article-reject',
            params: {
                articleId: 'id'
            }
        },
        get_queue: {
            name: 'articles-queue-timeline'
        },
        post: {
            name: 'article-save',
            dataType: 'json',
            type: 'POST'
        }
    }
});

var Eventlist = {
    leftcolumn_deletepost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'article-delete/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_clear_post_text: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'article-clear-text/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    leftcolumn_recoverpost: function(post_id, callback){
        $.ajax({
            url: controlsRoot + 'article-restore/',
            data: {
                id: post_id
            },
            success: function (data) {
                callback(1);
            }
        });
    },
    rightcolumn_deletepost: function(post_id, gridId, timestamp, callback){
        $.ajax({
            url: controlsRoot + 'article-queue-delete/',
            dataType : "json",
            data: {
                id: post_id,
                gridId: gridId,
                timestamp: timestamp,
                targetFeedId: Elements.rightdd(),
                type: Elements.rightType(),
            },
            success: function(data) {
                if (typeof callback === 'function') {
                    callback(true, data);
                }
            }
        });
    },
    rightcolumn_save_slot: function(gridLineId, time, startDate, endDate, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-save/',
            dataType : "json",
            data: {
                gridLineId : gridLineId,
                startDate : startDate,
                endDate : endDate,
                time: time,
                type: Elements.rightType(),
                targetFeedId: Elements.rightdd()
            },
            success: function (data) {
                if(data.success) {
                    callback(true);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    rightcolumn_time_edit: function(gridLineId, gridLineItemId, time, timestamp, qid, callback) {
        $.ajax({
            url: controlsRoot + 'grid-line-item-save/',
            dataType : "json",
            data: {
                gridLineId: gridLineId,
                gridLineItemId: gridLineItemId,
                time: time,
                timestamp: timestamp,
                queueId: qid
            },
            success: function(data) {
                if (data.success) {
                    callback(true);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(false);
                }
            }
        });
    },
    rightcolumn_removal_time_edit: function(gridLineId, gridLineItemId, time, qid, callback) {
        $.ajax({
            url: controlsRoot + 'plan-post-delete/',
            dataType : "json",
            data: {
                time: time,
                queueId: qid
            },
            success: function(data) {
                if (data.success) {
                    callback(true);
                } else {
                    callback(false);
                }
            }
        });
    },
    rightcolumn_post_edit: function(text, photos, link, dataQueueId, callback){
        var $sourceFeedIds = Elements.leftdd();
        var $sourceFeedId;
        if ($sourceFeedIds.length != 1) {
            $sourceFeedId = null;
        } else {
            $sourceFeedId = $sourceFeedIds[0];
        }

        $.ajax({
            url: controlsRoot + 'article-queue-item-save/',
            type: 'POST',
            dataType : "json",
            data: {
                articleQueueId: dataQueueId,
                text: text,
                photos: photos,
                link: link,
                sourceFeedId: $sourceFeedId,
                targetFeedId: Elements.rightdd(),
                userGroupId: Elements.getUserGroupId()
            },
            success: function(data) {
                callback(data);
            }
        });
    },

    post_moved: function(article_id, slot_id, queueId, callback){
        $.ajax({
            url: controlsRoot + 'article-add-to-queue/',
            dataType : "json",
            data: {
                articleId: article_id,
                timestamp: slot_id,
                targetFeedId: Elements.rightdd(),
                queueId: queueId,
                type: Elements.rightType()
            },
            success: function (data) {
                if(data.success) {
                    callback(1, data);
                } else {
                    if (data.message) {
                        popupError(Lang[data.message]);
                    }
                    callback(0, data);
                }
            }
        });
    },

    load_post_edit: function(id, callback){
        $.ajax({
            url: controlsRoot + 'article-get/',
            dataType : "json",
            data: {
                articleId: id
            },
            success: function (data) {
                if(data && data.id) {
                    callback(true, data);
                } else {
                    callback(false, null);
                }
            }
        });
    },

    post_describe_link: function(link, callback) {
        $.ajax({
            url: controlsRoot + 'parse-url/',
            type: 'GET',
            dataType : "json",
            data: {
                url: link
            },
            success: function (data) {
                callback(data);
            }
        });
    },

    post_link_data: function(data, callback) {
        $('div.link-description').html('<img src="' + root + 'shared/images/fe/ajax-loader.gif">');
        $.ajax({
            url: controlsRoot + 'link-info-upload/',
            type: 'GET',
            dataType : "json",
            data: {
                data: data
            },
            success: function (data) {
                if (data) {
                    $('.reload-link').click();
                } else {
                    popupError('Ошибка сохренения информации о ссылке');
                }
                if (typeof callback == 'function') {
                    callback(data);
                }
            }
        });
    },

    leftcolumn_sort_type_change: function() {
        app.loadArticles(true);
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

    eof: null
};

/**
 * @deprecated
 */
var Events = {
    delay: 0,
    isDebug: false,
    eventList: Eventlist,
    fire: function(name){
        var t = this;
        var args;
        if (arguments.length == 2 && (typeof arguments[1] == 'object') && arguments[1].length) {
            args = arguments[1];
        } else {
            args = Array.prototype.slice.call(arguments, 1);
        }
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    if (window.console && console.log && t.isDebug) {
                        console.groupCollapsed(name);
                        console.log('args: ' + args.slice(0, -1));
                        console.groupEnd(name);
                    }
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if (window.console && console.log && t.isDebug) {
                    console.groupCollapsed('Error');
                    console.log(e);
                    console.groupEnd('Error');
                }
            }
        }
    }
};
