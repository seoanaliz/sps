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
    load_list: function(callback) {
        simpleAjax('getGroupList', function(dirtyData) {
            var clearData = [];
            if ($.isArray(dirtyData))
                $.each(dirtyData, function(i, data) {
                    clearData.push({
                        itemId: data.group_id,
                        itemTitle: data.name
                    });
                });
            callback(clearData);
        });
    },
    load_table: function(options, callback) {
        var params = $.extend({
            listId: null,
            offset: null,
            limit: null,
            sortBy: '',
            sortReverse: false,
            search: ''
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
            search: params.search
        }, function(dirtyData) {
            var clearData = [];
            if ($.isArray(dirtyData))
                $.each(dirtyData, function(i, data) {
                    var users = [];
                    $.each(data.admins, function(i, data) {
                        users.push({
                            userId: data.vk_id,
                            userName: data.name,
                            userPhoto: data.ava == 'standard' ? 'http://vk.com/images/camera_c.gif' : data.ava,
                            userDescription: data.role || '&nbsp;'
                        });
                    });
                    //var users = DataUsers;
                    clearData.push({
                        publicId: data.id,
                        publicImg: data.ava,
                        publicName: data.name,
                        publicFollowers: data.quantity,
                        publicGrowthNum: data.diff_abs,
                        publicGrowthPer: data.diff_rel,
                        lists: ($.isArray(data.group_id) && data.group_id.length) ? data.group_id : [],
                        users: users
                    });
                });
            callback(clearData);
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
        simpleAjax('implGroup', {
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    remove_from_list: function(public_id, list_id, callback) {
        simpleAjax('exGroup', {
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    },
    change_user: function(user_id, list_id, public_id, callback) {
        simpleAjax('selectSAdmin', {
            adminId: user_id,
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(true);
        });
    }
};
$.extend(Events.eventList, Eventlist);