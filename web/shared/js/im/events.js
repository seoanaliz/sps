/**
 * Events
 */
var Events = {
    delay: 0,
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
    add_user: function(token, callback) {
        simpleAjax('saveAt', {access_token: token}, function() {
            callback(true);
        })
    },
    get_user: function(callback) {
        simpleAjax('addUser', function(data) {
            callback({
                id: data.userId,
                name: data.name,
                photo: data.ava
            });
        });
    },
    get_lists: function(callback) {
        simpleAjax('getGroupList', function(dirtyData) {
            var clearData = [];
            var count = 0;
            $.each(dirtyData, function(i, dirtyList) {
                if (typeof dirtyList == 'number') {
                    count = dirtyList;
                } else {
                    clearData.push({
                        id: dirtyList.group_id,
                        title: dirtyList.name,
                        count: dirtyList.unread,
                        isRead: dirtyList.isRead
                    });
                }
            });

            callback(clearData, count);
        });
    },
    get_dialogs_list: function(listId, offset, limit, callback) {
        var params = {
            groupId: listId == Configs.commonDialogsList ? undefined : listId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getGroupDialogs', params, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyDialog) {
                var clearUser = {
                    id: dirtyDialog.uid.userId,
                    name: dirtyDialog.uid.name,
                    photo: dirtyDialog.uid.ava,
                    isOnline: (dirtyDialog.uid.online != 0)
                };
                clearData.push({
                    id: dirtyDialog.id,
                    user: clearUser
                });
                UsersCollection.add(clearUser.id, clearUser);
            });
            callback(clearData);
        });
    },
    get_dialogs: function(listId, offset, limit, callback) {
        var params = {
            groupId: listId == Configs.commonDialogsList ? undefined : listId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getDialogsList', params, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyDialog) {
                var clearUser = {
                    id: dirtyDialog.uid.userId,
                    name: dirtyDialog.uid.name,
                    photo: dirtyDialog.uid.ava,
                    isOnline: (dirtyDialog.uid.online != 0)
                };
                var clearText = dirtyDialog.body || dirtyDialog.title;
                clearText = clearText.split('<br>');
                if (clearText.length > 2) {
                    clearText = clearText.slice(0, 2).join('<br>') + '...';
                } else {
                    clearText = clearText.join('<br>');
                }
                if (clearText.length > 250) {
                    clearText = clearText.substring(0, 200) + '...';
                }
                clearData.push({
                    id: dirtyDialog.id,
                    isNew: (dirtyDialog.read_state != 1),
                    isViewer: (dirtyDialog.out != 0),
                    viewer: Configs.viewer,
                    user: clearUser,
                    text: clearText,
                    timestamp: dirtyDialog.date,
                    lists: (typeof dirtyDialog.groups == 'string') ? [] : dirtyDialog.groups
                });
                UsersCollection.add(clearUser.id, clearUser);
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
            var clearData = [];
            var dirtyUsers = dirtyData.dialogers;
            var dirtyMessages = dirtyData.messages;
            var clearUsers = {};
            var clearMessages = [];
            $.each(dirtyUsers, function(i, dirtyUser) {
                var clearUser = {
                    id: dirtyUser.userId,
                    name: dirtyUser.name,
                    photo: dirtyUser.ava,
                    isOnline: (dirtyUser.online != 0)
                };
                clearUsers[dirtyUser.userId] = clearUser;
                UsersCollection.add(clearUser.id, clearUser);
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
                    isViewer: (dirtyMessage.out != 0),
                    text: makeMsg(dirtyMessage.body.split('<br>').join('\n'), true),
                    dialogId: dirtyMessage.dialog_id,
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
            callback(data);
        });
    },
    message_mark_as_read: function(messageId, dialogId, callback) {
        simpleAjax('markMes', {mids: messageId, dialogsId: dialogId}, function() {
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
    },
    get_templates: function(listId, callback) {
        simpleAjax('findTemplate', {groupId: listId, search: ''}, function(data) {
            var clearTemplates = [];
            $.each(data, function(i, dirtyTemplate) {
                clearTemplates.push({
                    title: dirtyTemplate.text,
                    id: dirtyTemplate.tmpl_id
                });
            });
            callback(clearTemplates);
        });
    },
    edit_template: function(tmplId, text, listId, callback) {
        simpleAjax('addTemplate', {tmplId: tmplId, text: text, groupIds: listId}, function() {
            callback(true);
        });
    },
    add_template: function(text, listId, callback) {
        simpleAjax('addTemplate', {text: text, groupIds: listId}, function() {
            callback(true);
        });
    },
    delete_template: function(tmplId, callback) {
        simpleAjax('deleteTemplate', {tmplId: tmplId}, function() {
            callback(true);
        });
    },
    set_list_as_read: function(listId, callback) {
        simpleAjax('toggleReadRead', {groupId: listId, read: true}, function() {
            callback(true);
        });
    },
    set_list_as_new: function(listId, callback) {
        simpleAjax('toggleReadRead', {groupId: listId, read: false}, function() {
            callback(true);
        });
    }
};
$.extend(Events.eventList, Eventlist);
