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

var Eventlist = {
    load_list: function(viewer_id, callback) {
        $.ajax({
            url: Events.url + 'getGroupList/',
            data: {
                userId: viewer_id
            },
            success: function (data) {
                callback(DataList);
            }
        });
    },
    load_table: function(viewer_id, list_id, offset, limit, callback) {
        $.ajax({
            url: Events.url + 'getGroupList/',
            data: {
                userId: viewer_id,
                groupId: list_id,
                offset: offset,
                limit: limit
            },
            success: function (data) {
                callback(DataTable);
            }
        });
    },
    add_list: function(viewer_id, public_id, title, callback) {
        $.ajax({
            url: Events.url + 'setGroup/',
            data: {
                userId: viewer_id,
                publId: public_id,
                groupName: title
            },
            success: function (data) {
                callback(false);
            }
        });
    },
    remove_list: function(viewer_id, list_id, callback) {
        $.ajax({
            url: Events.url + 'deleteGroup/',
            data: {
                userId: viewer_id,
                groupId: list_id
            },
            success: function (data) {
                callback(false);
            }
        });
    },
    change_user: function(viewer_id, user_id, list_id, public_id, callback) {
        $.ajax({
            url: Events.url + 'selectSAdmin/',
            data: {
                userId: viewer_id,
                adminId: user_id,
                groupId: list_id,
                publId: public_id
            },
            success: function (data) {
                callback(false);
            }
        });
    }
};
$.extend(Events.eventList, Eventlist);