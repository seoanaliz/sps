/**
 * Events
 */
var Events = {
    url: Configs.controlsRoot,
    delay: Configs.eventsDelay,
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
                if(window.console && console.log) {
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
        simpleAjax('addUser', function(dirtyData) {
            callback(true);
        });
    },
    load_list: function(callback) {
        simpleAjax('getGroupList', function(dirtyData) {
            var clearData = [];
            if ($.isArray(dirtyData))
                $.each(dirtyData, function(i, data) {
                    clearData.push({
                        itemId: data.group_id,
                        itemTitle: data.name,
                        itemFave: data.fave
                    });
                });
            callback(clearData);
        });
    },
    load_bookmarks: function(callback) {
        simpleAjax('getGroupList', {filter: 'bookmark'}, function(dirtyData) {
            var clearData = [];
            if ($.isArray(dirtyData)) {
                $.each(dirtyData, function(i, data) {
                    clearData.push({
                        itemId: data.group_id,
                        itemTitle: data.name,
                        itemFave: data.fave
                    });
                });
            }
            callback(clearData);
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

        var sortByClear = {
            followers: 'quantity',
            contacts: '',
            growth: 'diff_abs',
            isActive: 'active',
            inSearch: 'in_search',
            visitors: 'visitors'
        };
        simpleAjax('getEntries', {
            groupId: params.listId,
            offset: params.offset,
            limit: params.limit,
            sortBy: sortByClear[params.sortBy],
            sortReverse: params.sortReverse ? 1 : 0,
            search: params.search,
            period: params.period,
            min: params.audienceMin,
            max: params.audienceMax,
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
                            publicId: publicItem.id,
                            publicImg: publicItem.ava,
                            publicName: publicItem.name,
                            publicFollowers: publicItem.quantity,
                            publicGrowthNum: publicItem.diff_abs,
                            publicGrowthPer: publicItem.diff_rel,
                            publicIsActive: !!publicItem.active,
                            publicInSearch: !!publicItem.in_search,
                            publicVisitors: publicItem.visitors,
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
                            publicAuthorsLikes: publicItem.auth_likes_eff,
                            publicAuthorsPosts: publicItem.auth_posts,
                            publicAuthorsReposts: publicItem.auth_reposts_eff,
                            publicGrowthVisitors: publicItem.avg_vis_grouth,
                            publicGrowthViews: publicItem.avg_vie_grouth,
                            publicPosts: publicItem.overall_posts,
                            publicPostsPerDay: publicItem.posts_days_rel,
                            publicSbPosts: publicItem.sb_posts_count,
                            publicSbLikes: publicItem.sb_posts_rate,
                            publicVisitors: publicItem.visitors,
                            publicViews: publicItem.views
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
    hide_public: function(public_id, callback) {
        simpleAjax('togglePublVisibil', {
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    add_to_bookmark: function(listId, callback) {
        simpleAjax('toggleGroupFave', {
            groupId: listId
        }, function(dirtyData) {
            callback(true);
        });
    },
    remove_from_bookmark: function(listId, callback) {
        simpleAjax('toggleGroupFave', {
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
    get_report_list: function(limit, offset, callback) {
        simpleAjax('getReportList', {limit: limit, offset: offset}, function(data) {
            callback(data);
        });
    },
    add_report: function(ourPublicId, publicId, time, callback) {
        simpleAjax('addReport', {targetPublicId: ourPublicId, barterPublicId: publicId, startTime: time}, function() {
            callback(true);
        })
    }
};
$.extend(Events.eventList, Eventlist);