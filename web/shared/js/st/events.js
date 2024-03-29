/**
 * Events
 */
var Events = {
    url: Configs.controlsRoot,
    delay: Configs.eventsDelay,
    isDebug: false,
    eventList: {},
    fire: function(name, args){
        var t = this;
        args = Array.prototype.slice.call(arguments, 1);
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    if (window.console && console.log && t.isDebug) {
                        console.log(name + ':');
                        console.log(args.slice(0, -1));
                        console.log('-------');
                    }
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if (window.console && console.log && t.isDebug) {
                    console.log(e);
                }
            }
        }
    }
};

var simpleAjax = function(method, data, callback) {
    $.ajax({
        url: Events.url + method + '/',
        dataType: 'json',
        data: $.extend({
            userId: cur.dataUser.uid
        }, data),
        success: function (result) {
            if (result && result.response) {
                if ($.isFunction(data)) callback = data;
                callback(result.response);
            }
        }
    });
};

var Eventlist = {
    get_user: function(userId, callback) {
        simpleAjax('addUser',{type: 'stat'}, function(dirtyData) {
            callback(dirtyData);
        });
    },
    load_list: function(callback) {
        $.ajax({
            url: controlsRoot + 'getGroupList/',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    callback(resp.data);
                }
            }
        });
    },
    load_table: function(options, callback) {
        var params = $.extend({
            listId: null,
            limit: null,
            offset: null,
            search: '',
            sortBy: '',
            sortReverse: false,
            period: ''
        }, options);

        /*
        'views',
        'overall_posts',
        'posts_days_rel',
        'sb_posts_count',
        'sb_posts_rate',
        'auth_posts',
        'auth_likes_eff',
        'auth_reposts_eff',
        'visitors',
        'avg_vis_grouth',
        'avg_vie_grouth'
        */
        var clearSortBy = {
            followers: 'quantity',
            viewers: 'viewers',
            contacts: '',
            growth: 'diff_abs',
            isActive: 'active',
            inSearch: 'in_search',
            visitors: 'visitors',
            views: 'views',
            posts: 'overall_posts',
            postsPerDay: 'posts_days_rel',
            sbPosts: 'sb_posts_count',
            sbLikes: 'sb_posts_rate',
            authorsPosts: 'auth_posts',
            authorsLikes: 'auth_likes_eff',
            authorsReposts: 'auth_reposts_eff',
            growthViews: 'avg_vie_grouth',
            growthVisitors: 'abs_vis_grow',
            cpp: 'cpp'
        };
        simpleAjax('getEntries', {
            groupId: params.listId,
            offset: params.offset,
            limit: params.limit,
            sortBy: clearSortBy[params.sortBy],
            sortReverse: params.sortReverse ? 1 : 0,
            search: params.search,
            period: params.period,
            min: params.audienceMin,
            max: params.audienceMax,
            timeFrom: params.timeFrom,
            timeTo: params.timeTo,
            type: 'groups',
            show: 1
        }, function(dirtyData) {
            var data = Table.prepareServerData(dirtyData);
            callback(data.clearList, data.clearPeriod, data.clearListType);
        });
    },
    add_list: function(groupName, callback) {
        $.ajax({
            url: controlsRoot + 'setGroup/',
            dataType: 'json',
            data: {
                groupName: groupName
            },
            success: function (resp) {
                if (resp.success) {
                    callback(resp.data);
                }
            }
        });
    },
    set_cpp: function(id, cost, callback) { // save cost per post
        $.ajax({
            url: controlsRoot + 'setCpp/',
            dataType: 'json',
            type: 'POST',
            data: {
                intId: id,
                cpp: cost
            },
            success: function (resp) {
                callback(resp);
            }
        });
    },
    update_list: function(public_id, list_id, title, callback) {
        simpleAjax('setGroup', {
            groupId: list_id,
            publId: public_id,
            groupName: title
        }, function(dirtyData) {
            callback(true);
        });
    },
    remove_list: function(list_id, callback) {
        simpleAjax('deleteGroup', {
            groupId: list_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    add_to_list: function(public_id, list_id, callback) {
        simpleAjax('implPublic', {
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    remove_from_list: function(public_id, list_id, callback) {
        simpleAjax('exPublic', {
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    change_user: function(user_id, list_id, public_id, callback) {
        simpleAjax('selectGroupAdmin', {
            adminId: user_id,
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    toggle_group_general: function(listId, callback) {
        simpleAjax('toggleGroupGeneral', {
            groupId: listId
        }, function(dirtyData) {
            callback(true);
        });
    },
    share_list: function(listId, userId, callback) {
        simpleAjax('shareGroup', {
            groupId: listId,
            recId: userId
        }, function() {
            callback(true);
        });
    },
    sort_list: function(groupId, index, callback) {
        $.ajax({
            url: controlsRoot + 'setGroupOrder/',
            data: {
                groupId: groupId,
                index: index
            },
            type: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    callback(true);
                } else {
                    callback(false);
                }
            }
        });
    },
    rename_list: function(listId, listName, callback) {
        $.ajax({
            url: controlsRoot + 'setGroup/',
            data: {
                groupId: listId,
                groupName: listName
            },
            type: 'POST',
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    callback(true, resp.data);
                } else {
                    callback(false);
                }
            }
        });
    }
};
$.extend(Events.eventList, Eventlist);
