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
            growth: 'diff_abs'
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
                        lists: ($.isArray(publicItem.group_id) && publicItem.group_id.length) ? publicItem.group_id : [],
                        users: users
                    });
                });
            }
            var clearPeriod = [];
            if (dirtyData.min_max) {
                clearPeriod = [
                    dirtyData.min_max.min,
                    dirtyData.min_max.max
                ];
            }

            callback(clearList, clearPeriod);
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
    }
};
$.extend(Events.eventList, Eventlist);