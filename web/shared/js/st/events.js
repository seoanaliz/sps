/**
 * Events
 */
var Events = {
    url: controlsRoot,
    delay: 0,
    eventList: {},
    fire: function(name, args){
        var t = this;
        args = Array.prototype.slice.call(arguments, 1);
        if ($.isFunction(t.eventList[name])) {
            try {
                setTimeout(function() {
                    console.log(name + ':');
                    console.log(args.slice(0, -1));
                    console.log('-------');
                    t.eventList[name].apply(window, args);
                }, t.delay);
            } catch(e) {
                if(console && $.isFunction(console.log)) {
                    console.log(e);
                }
            }
        }
    }
};

var simpleAjax = function(method, data, callback) {
    var timeout;

    clearTimeout(timeout);
    timeout = setTimeout(function() {
        $('#global-loader').fadeIn(200);
    }, Configs.globalLoaderTimeout);

    $.ajax({
        url: Events.url + method + '/',
        dataType: 'json',
        data: $.extend({
            userId: DataUser.uid
        }, data),
        success: function (result) {
            clearTimeout(timeout);
            $('#global-loader').fadeOut(200);

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
    load_table: function(list_id, offset, limit, callback) {
        simpleAjax('getEntries', {
            groupId: list_id,
            offset: offset,
            limit: limit
        }, function(dirtyData) {
            var clearData = [];
            if ($.isArray(dirtyData))
                $.each(dirtyData, function(i, data) {
                    //todo: доделать
                    var users = [];
                    $.each(data.admins, function(i, data) {
                        users.push({
                            userId: data.vk_id,
                            userName: data.name,
                            userPhoto: data.ava,
                            userDescription: data.role || '&nbsp;'
                        });
                    });
                    clearData.push({
                        publicId: data.id,
                        publicImg: data.ava,
                        publicName: data.name,
                        publicFollowers: data.quantity,
                        publicGrowthNum: data.diff_abs,
                        publicGrowthPer: data.diff_rel,
                        lists: data.group_id ? [data.group_id] : null,
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
            callback(false);
        });
    },
    change_user: function(user_id, list_id, public_id, callback) {
        simpleAjax('selectSAdmin', {
            adminId: user_id,
            groupId: list_id,
            publId: public_id
        }, function(dirtyData) {
            callback(false);
        });
    }
};
$.extend(Events.eventList, Eventlist);