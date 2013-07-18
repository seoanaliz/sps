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
            growthVisitors: 'abs_vis_grow'
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
            show: 1
        }, function(dirtyData) {
            var clearList = [];
            var clearPeriod = [];
            var clearListType = 0;

            if (dirtyData.min_max) {
                clearPeriod = [
                    dirtyData.min_max.min,
                    dirtyData.min_max.max
                ];
            }
            if (dirtyData.group_type == 2) {
                clearListType = 1;
            }
            if (!clearListType) {
                if ($.isArray(dirtyData.list)) {
                    $.each(dirtyData.list, function(i, publicItem) {
                        var users = [];
                        $.each(publicItem.admins, function(i, data) {
                            users.push({
                                userId: data.vk_id,
                                userName: data.name,
                                userPhoto: data.ava == 'standard' ? 'http://vk.com/images/camera_c.gif' : data.ava,
                                userDescription: data.role || '&nbsp;'
                            });
                        });
                        clearList.push({
                            intId: publicItem.id,
                            publicId: publicItem.vk_id,
                            publicImg: publicItem.ava,
                            publicName: publicItem.name,
                            publicFollowers: publicItem.quantity,
                            publicGrowthNum: publicItem.diff_abs,
                            publicGrowthPer: publicItem.diff_rel,
                            publicIsActive: !!publicItem.active,
                            publicInSearch: !!publicItem.in_search,
                            publicVisitors: publicItem.visitors,
                            publicAudience: publicItem.viewers,
                            lists: ($.isArray(publicItem.group_id) && publicItem.group_id.length) ? publicItem.group_id : [],
                            users: users
                        });
                    });
                }
            } else {
                /*
                id - id
                name - name
                ava: "http://cs302214.userapi.com/g37140977/e_9e81c016.jpg
                auth_likes_eff: 0 - Авторское/спарсенное: лайки
                auth_posts: 0 - авторских постов
                auth_reposts_eff: 0 - Авторское/спарсенное: репосты
                avg_vie_grouth: null - средний суточный прирост просмотров
                avg_vis_grouth: null - средний суточный прирост уников
                overall_posts: 68 - общее количество постов за период
                posts_days_rel: 0 - в среднем постов за сутки
                sb_posts_count: 56 - постов из источников
                sb_posts_rate: 0 - средний рейтинг постов из источников
                views: null - просмотры
                visitors: null - посетители
                */
                if ($.isArray(dirtyData.list)) {
                    $.each(dirtyData.list, function(i, publicItem) {
                        clearList.push({
                            publicId: publicItem.id,
                            publicImg: publicItem.ava,
                            publicName: publicItem.name,
                            publicPosts: publicItem.overall_posts,
                            publicViews: publicItem.views,
                            publicVisitors: publicItem.visitors,
                            publicPostsPerDay: publicItem.posts_days_rel,
                            publicSbPosts: publicItem.sb_posts_count,
                            publicSbLikes: publicItem.sb_posts_rate,
                            publicAuthorsPosts: publicItem.auth_posts,
                            publicAuthorsLikes: publicItem.auth_likes_eff,
                            publicAuthorsReposts: publicItem.auth_reposts_eff,
                            publicGrowthViews: publicItem.avg_vie_grouth,
                            publicGrowthVisitors: intval(publicItem.abs_vis_grow),
                            publicGrowthVisitorsRelative: intval(publicItem.rel_vis_grow)
                        });
                    });
                }
            }
            callback(clearList, clearPeriod, clearListType);
        });
    },
    add_list: function(title, callback) {
        simpleAjax('setGroup', {
            groupName: title
        }, function(dirtyData) {
            callback(true);
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
    sort_list: function(listId, index, callback) {
        $.ajax({
            url: controlsRoot + 'setGroupOrder/',
            data: {
                groupId: listId,
                index: index
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    callback();
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
            dataType: 'json',
            success: function (resp) {
                if (resp.success) {
                    callback(resp.data);
                }
            }
        });
    }
};
$.extend(Events.eventList, Eventlist);
