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
    get_viewer: function(callback) {
        simpleAjax('addUser', function(rawUser) {
            callback((rawUser && rawUser.at) ? Cleaner.user(rawUser) : false);
        });
    },
    get_lists: function(callback) {
        simpleAjax('getGroupList', function(rawData) {
            var clearData = [];
            var count = 0;
            $.each(rawData, function(i, rawListItem) {
                if (typeof rawListItem == 'number') {
                    count = rawListItem;
                } else {
                    clearData.push(Cleaner.listItem(rawListItem));
                }
            });

            callback({list: clearData, counter: count});
        });
    },
    get_dialogs: function(listId, offset, limit, callback) {
        var params = {
            groupId: listId == Configs.commonDialogsList ? undefined : listId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getDialogsList', params, function(rawData) {
            var clearData = [];
            $.each(rawData, function(i, rawDialog) {
                var clearDialog = Cleaner.dialog(rawDialog);
                clearData.push(clearDialog);
            });
            callback({list: clearData});
        });
    },
    get_messages: function(dialogId, offset, limit, callback) {
        var params = {
            dialogId: dialogId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getDialog', params, function(rawData) {
            var clearData = {};
            var rawUsers = rawData.dialogers;
            var rawMessages = rawData.messages;
            var clearUser = {};
            var clearMessages = [];
            $.each(rawUsers, function(i, rawUser) {
                clearUser = Cleaner.user(rawUser);
                if (clearUser.id != Configs.vkId) {
                    return false;
                }
            });
            $.each(rawMessages, function(i, rawMessage) {
                clearMessages.push(Cleaner.message(rawMessage));
            });
            clearData = {
                user: clearUser,
                viewer: userCollection.get(Configs.vkId).data(),
                list: clearMessages,
                lists: rawData.groupIds
            };
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
            $.each(data, function(i, rawTemplate) {
                clearTemplates.push(Cleaner.template(rawTemplate));
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
        simpleAjax('toggleReadRead', {groupId: listId, read: 1}, function() {
            callback(true);
        });
    },
    set_list_as_new: function(listId, callback) {
        simpleAjax('toggleReadRead', {groupId: listId}, function() {
            callback(true);
        });
    },
    set_list_order: function(listIds, callback) {
        simpleAjax('setGroupOrder', {groupIds: listIds}, function() {
            callback(true);
        });
    }
};
$.extend(Events.eventList, Eventlist);

/**
 * Helpers
 */
var Cleaner = {
    longPollMessage: function(rawContent, isOut) {
        var userModel = typeof rawContent.from_id == 'number' ? userCollection.get(rawContent.from_id) : new UserModel(this.user(rawContent.from_id));
        var viewerModel = userCollection.get(Configs.vkId);

        return {
            id: rawContent.mid,
            isNew: (rawContent.read_state != 1),
            isViewer: isOut,
            text: makeMsg(rawContent.body),
            attachments: [],
            timestamp: rawContent.date,
            user: userModel.data(),
            viewer: viewerModel.data(),
            lists: (rawContent.groups == '-1') ? [Configs.commonDialogsList] : rawContent.groups,
            dialogId: rawContent.dialog_id
        };
    },

    longPollDialog: function(rawContent, isOut) {
        var userModel = typeof rawContent.from_id == 'number' ? userCollection.get(rawContent.from_id) : new UserModel(this.user(rawContent.from_id));
        var viewerModel = userCollection.get(Configs.vkId);

        return {
            id: rawContent.dialog_id,
            isNew: (rawContent.read_state != 1),
            isViewer: isOut,
            text: makeDlg(rawContent.body || rawContent.title),
            attachments: [],
            timestamp: rawContent.date,
            user: userModel.data(),
            viewer: viewerModel.data(),
            lists: (rawContent.groups == '-1') ? [Configs.commonDialogsList] : rawContent.groups,
            messageId: rawContent.mid
        };
    },

    longPollOnline: function(rawContent) {
        var user = rawContent.userId;

        return {
            isOnline: user.online,
            userId: user.userId
        };
    },

    longPollRead: function(rawContent) {
        return {
            id: rawContent.mid,
            dialogId: rawContent.dialog_id
        }
    },

    user: function(rawUser) {
        var clearUser = {};

        if (rawUser && rawUser.userId) {
            clearUser = {
                id: rawUser.userId,
                name: rawUser.name,
                photo: rawUser.ava,
                isOnline: (rawUser.online != 0)
            };
        } else {
            clearUser = {};
        }
        return clearUser;
    },

    attachments: function(rawAttachments) {
        var clearAttachments = {};

        if (rawAttachments) {
            $.each(rawAttachments, function (i, attachment) {
                if (!clearAttachments[attachment.type]) {
                    clearAttachments[attachment.type] = {
                        type: attachment.type,
                        list: []
                    };
                }
                clearAttachments[attachment.type].list.push(attachment[attachment.type]);
            });
        }
        return clearAttachments;
    },

    message: function(rawMessage) {
        var userModel = userCollection.get(rawMessage.from_id) || new UserModel();
        var viewerModel = userCollection.get(Configs.vkId) || new UserModel();

        return {
            id: rawMessage.mid,
            isNew: (rawMessage.read_state != 1),
            isViewer: (rawMessage.out != 0),
            user: userModel.data(),
            viewer: viewerModel.data(),
            text: makeMsg(rawMessage.body.split('<br>').join('\n'), true),
            timestamp: rawMessage.date,
            attachments: this.attachments(rawMessage.attachments),
            dialogId: rawMessage.dialog_id
        };
    },

    dialog: function(rawDialog) {
        var userModel = new UserModel(this.user(rawDialog.uid));
        var viewerModel = userCollection.get(Configs.vkId) || new UserModel();

        return {
            id: rawDialog.id,
            isNew: (rawDialog.read_state != 1),
            isViewer: (rawDialog.out != 0),
            user: userModel.data(),
            viewer: viewerModel.data(),
            text: makeDlg(rawDialog.body || rawDialog.title),
            timestamp: rawDialog.date,
            attachments: [],
            lists: (typeof rawDialog.groups == 'string') ? [] : rawDialog.groups,
            messageId: rawDialog.mid
        };
    },

    listItem: function(rawList) {
        return {
            id: rawList.group_id,
            title: rawList.name,
            counter: rawList.unread,
            isRead: rawList.isRead,
            isSelected: false,
            isDraggable: true
        };
    },

    template: function(rawTemplate) {
        return {
            id: rawTemplate.tmpl_id,
            title: rawTemplate.text
        };
    }
};

function makeMsg(msg, isNotClean) {
    function clean(str) {
        if (isNotClean) {
            return str;
        } else {
            return str ? str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;') : '';
        }
    }

    function indexOf(arr, value, from) {
        for (var i = from || 0, l = (arr || []).length; i < l; i++) {
            if (arr[i] == value) return i;
        }
        return -1;
    }

    return clean(msg).replace(/\n/g, '<br>').replace(/(@)?((https?:\/\/)?)((([A-Za-z0-9][A-Za-z0-9\-\_\.]*[A-Za-z0-9])|(([а-яА-Я0-9\-\_\.]+\.рф)))(\/([A-Za-zА-Яа-я0-9\-\_#%&?+\/\.=;:~]*[^\.\,;\(\)\?\<\&\s:])?)?)/ig, function () {
        var domain = arguments[5],
            url = arguments[4],
            full = arguments[0],
            protocol = arguments[2] || 'http://';
        var pre = arguments[1];

        if (domain.indexOf('.') == -1) return full;
        var topDomain = domain.split('.').pop();
        if (topDomain.length > 5 || indexOf('aero,asia,biz,com,coop,edu,gov,info,int,jobs,mil,mobi,name,net,org,pro,tel,travel,xxx,ru,ua,su,рф,fi,fr,uk,cn,gr,ie,nl,au,co,gd,im,cc,si,ly,gl,be,eu,tv,to,me,io'.split(','), topDomain) == -1) return full;

        if (pre == '@') {
            return full;
        }
        try {
            full = decodeURIComponent(full);
        } catch (e){}

        if (full.length > 255) {
            full = full.substr(0, 253) + '..';
        }

        if (domain.match(/^([a-zA-Z0-9\.\_\-]+\.)?(vkontakte\.ru|vk\.com|vk\.cc|vkadre\.ru|vshtate\.ru|userapi\.com)$/)) {
            url = url.replace(/[^a-zA-Z0-9#%;_\-.\/?&=\[\]]/g, encodeURIComponent);
            return '<a href="'+ (protocol + url).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '" target="_blank">' + full + '</a>';
        }
        return '<a href="http://vk.com/away.php?utf=1&to=' + encodeURIComponent(protocol + url) + '" target="_blank">' + full + '</a>';
    })
}

function makeDlg(text) {
    if (!text) return '';
    var clearText = text;
    clearText = clearText.split('<br>');
    if (clearText.length > 2) {
        clearText = clearText.slice(0, 2).join('<br>') + '...';
    } else {
        clearText = clearText.join('<br>');
    }
    if (clearText.length > 250) {
        clearText = clearText.substring(0, 200) + '...';
    }
    return clearText;
}