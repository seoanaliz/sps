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
        simpleAjax('saveAt', {userId: userId, access_token: token}, function(data) {
            callback({
                id: data.userId,
                name: data.name,
                photo: data.ava
            });
        })
    },
    get_lists: function(callback) {
        simpleAjax('getGroupList', function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyList) {
                clearData.push({
                    id: dirtyList.group_id,
                    title: dirtyList.name
                });
            });

            callback(clearData);
        });
    },
    get_dialogs: function(listId, offset, limit, callback) {
        var params = {
            groupId: listId == 999999 ? undefined : listId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getDialogsList', params, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyDialog) {
                clearData.push({
                    id: dirtyDialog.id,
                    isNew: (dirtyDialog.read_state != 1),
                    user: {
                        id: dirtyDialog.uid.userId,
                        name: dirtyDialog.uid.name,
                        photo: dirtyDialog.uid.ava,
                        isOnline: (dirtyDialog.uid.online != 0)
                    },
                    lastMessage: {
                        text: dirtyDialog.body || dirtyDialog.title,
                        timestamp: dirtyDialog.date
                    },
                    lists: (typeof dirtyDialog.groups == 'string') ? [] : dirtyDialog.groups
                });
            });
            callback(clearData);
        });
    },
    get_messages: function(dialogId, offset, limit, callback) {
        var params = {
            dialogId: dialogId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getDialog', params, function(dirtyData) {
            var uriExp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            var clearData = [];
            var dirtyUsers = dirtyData.dialogers;
            var dirtyMessages = dirtyData.messages;
            var clearUsers = {};
            var clearMessages = [];
            $.each(dirtyUsers, function(i, dirtyUser) {
                clearUsers[dirtyUser.userId] = {
                    id: dirtyUser.userId,
                    name: dirtyUser.name,
                    photo: dirtyUser.ava,
                    isOnline: (dirtyUser.online != 0)
                };
            });
            $.each(dirtyMessages, function(i, dirtyMessage) {
                var clearAttachments = {};
                if (dirtyMessage.attachments) {
                    $.each(dirtyMessage.attachments, function (i, attachment) {
                        if (!clearAttachments[attachment.type]) {
                            clearAttachments[attachment.type] = {
                                type: attachment.type,
                                list: []
                            };
                        }
                        clearAttachments[attachment.type].list.push(attachment[attachment.type]);
                    });
                }
                clearMessages.push({
                    id: dirtyMessage.mid,
                    isNew: (dirtyMessage.read_state != 1),
                    isViewer: (dirtyMessage.from_id == Configs.vkId),
                    text: dirtyMessage.body.replace(uriExp, '<a target="_blank" href="$1">$1</a>'),
                    attachments: clearAttachments,
                    timestamp: dirtyMessage.date,
                    user: clearUsers[dirtyMessage.from_id]
                });
            });
            clearData = {users: clearUsers, messages: clearMessages};
            callback(clearData);
        });
    },
    send_message: function(dialogId, text, callback) {
        simpleAjax('messages.send', {dialogId: dialogId, text: text}, function(data) {
            callback({
                id: 0,
                isNew: true,
                isViewer: true,
                text: $.trim(text.split('\n').join('<br/>').replace(uriExp, '<a target="_blank" href="$1k">$1</a>')),
                timestamp: Math.floor(new Date().getTime() / 1000),
                user: Configs.viewer
            });
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