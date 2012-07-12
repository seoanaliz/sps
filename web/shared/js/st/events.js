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
    $.ajax({
        url: Events.url + method + '/',
        dataType: 'json',
        data: $.extend({
            userId: DataUser.uid
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
                        itemTitle: data.name,
                        itemSelected: false
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
                    clearData.push({
                        publicId: data.id,
                        publicImg: data.ava,
                        publicName: data.name,
                        publicFollowers: data.quantity,
                        publicGrowthNum: '231',
                        publicGrowthPer: '0.53%',
                        users: DataUsers
                    });
                });
            callback(clearData);
        });
    },
    add_list: function(public_id, title, callback) {
        simpleAjax('setGroup', {
            publId: public_id,
            groupName: title
        }, function(dirtyData) {
            callback(false);
        });
    },
    update_list: function(public_id, group_id, title, callback) {
        simpleAjax('setGroup', {
            groupId: group_id,
            publId: public_id,
            groupName: title
        }, function(dirtyData) {
            callback(false);
        });
    },
    remove_list: function(list_id, callback) {
        simpleAjax('setGroup', {
            groupId: title
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