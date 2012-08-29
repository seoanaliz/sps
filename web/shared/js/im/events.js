/**
 * Events
 */
var Events = {
    delay: 0,
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

var simpleAjax = function(method, data, callback) {
    $.ajax({
        url: Configs.controlsRoot + method + '/',
        dataType: 'json',
        data: $.extend({
            userId: Configs.vkId,
            type: 'mes'
        }, data),
        success: function (result) {
            if (result && result.response) {
                if ($.isFunction(data)) callback = data;
                if ($.isFunction(callback)) callback(result.response);
            } else {
                if ($.isFunction(data)) callback = data;
                if ($.isFunction(callback)) callback(false);
            }
        }
    });
};

var Eventlist = {
    get_user: function(userId, token, callback) {
        simpleAjax('saveAt', {userId: userId, access_token: token}, function() {
            callback(Data.users[0]);
        })
    },
    get_lists: function(callback) {
        simpleAjax('getGroupList', function(dirtyData) {
            callback(Data.lists);
        });
    },
    get_dialogs: function(listId, callback) {
        simpleAjax('getDialogsList', {groupId: listId}, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyDialog) {
                clearData.push({
                    id: dirtyDialog.mid,
                    isNew: (dirtyDialog.read_state != 1),
                    user: {
                        id: dirtyDialog.uid.userId,
                        name: dirtyDialog.uid.name,
                        photo: dirtyDialog.uid.ava,
                        isOnline: (dirtyDialog.uid.online != 0)
                    },
                    lastMessage: {
                        text: dirtyDialog.body || '...',
                        timestamp: dirtyDialog.date
                    }
                });
            });
            callback(clearData);
        });
    },
    get_messages: function(dialogId, callback) {
        simpleAjax('getDialog', {dialogId: dialogId}, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyMessage) {
                clearData.push({
                    id: 1,
                    text: 'Hello!!!',
                    user: Data.users[0],
                    timestamp: 1234567890
                });
            });
            callback(clearData);
        });
    },
    send_message: function(dialogId, text, callback) {
        simpleAjax('messages.send', {dialogId: dialogId, text: text}, function() {
            callback($.extend(Data.messages[0], {
                text: text.split('\n').join('<br/>'),
                timestamp: Math.floor(new Date().getTime() / 1000)
            }));
        });
    },
    message_mark_as_read: function(messageId, callback) {
        simpleAjax('markMes', {mids: messageId}, function() {
            callback(true);
        });
    },
    add_list: function(listName, callback) {
        simpleAjax('setGroup', {groupName: listName}, function() {
            callback(true);
        });
    },
    remove_list: function(listId, callback) {
        simpleAjax('deleteGroup', {groupId: listId}, function() {
            callback(true);
        });
    },
    add_to_list: function(dialogId, listId, callback) {
        simpleAjax('implEntryToGroup', {entryId: dialogId, groupId: listId}, function() {
            callback(true);
        });
    },
    remove_from_list: function(dialogId, listId, callback) {
        simpleAjax('exlEntryFromGroup', {entryId: dialogId, groupId: listId}, function() {
            callback(true);
        });
    }
};
$.extend(Events.eventList, Eventlist);