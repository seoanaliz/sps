function makeMsg(msg) {
    function clean(str) {
        return str ? str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
    }

    function indexOf(arr, value, from) {
        for (var i = from || 0, l = (arr || []).length; i < l; i++) {
            if (arr[i] == value) return i;
        }
        return -1;
    }

    return clean(msg).replace(/\n/g, '<br>').replace(/(@)?((https?:\/\/)?)((([A-Za-z0-9][A-Za-z0-9\-\_\.]*[A-Za-z0-9])|(([а-яА-Я0-9\-\_\.]+\.рф)))(\/([A-Za-zА-Яа-я0-9\-\_#%&?+\/\.=;:~]*[^\.\,;\(\)\?\<\&\s:])?)?)/ig, function () {
        var domain = arguments[5], url = arguments[4], full = arguments[0], protocol = arguments[2] || 'http://';
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

        if (full.length > 55) {
            full = full.substr(0, 53) + '..';
        }

        if (domain.match(/^([a-zA-Z0-9\.\_\-]+\.)?(vkontakte\.ru|vk\.com|vk\.cc|vkadre\.ru|vshtate\.ru|userapi\.com)$/)) {
            url = url.replace(/[^a-zA-Z0-9#%;_\-.\/?&=\[\]]/g, encodeURIComponent);
            return '<a href="'+ (protocol + url).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '" target="_blank">' + full + '</a>';
        }
        return '<a href="http://vk.com/away.php?utf=1&to=' + encodeURIComponent(protocol + url) + '" target="_blank" onclick="return goAway(\''+ clean(protocol + url) + '\', {}, event);">' + full + '</a>';
    })
}

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
    get_dialogs_list: function(listId, offset, limit, callback) {
        var params = {
            groupId: listId == 999999 ? undefined : listId,
            offset: offset,
            limit: limit
        };
        simpleAjax('getGroupDialogs', params, function(dirtyData) {
            var clearData = [];
            $.each(dirtyData, function(i, dirtyDialog) {
                clearData.push({
                    id: dirtyDialog.id,
                    user: {
                        id: dirtyDialog.uid.userId,
                        name: dirtyDialog.uid.name,
                        photo: dirtyDialog.uid.ava,
                        isOnline: (dirtyDialog.uid.online != 0)
                    }
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
                    user: {
                        id: dirtyDialog.uid.userId,
                        name: dirtyDialog.uid.name,
                        photo: dirtyDialog.uid.ava,
                        isOnline: (dirtyDialog.uid.online != 0)
                    },
                    text: clearText,
                    timestamp: dirtyDialog.date,
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
                    isViewer: (dirtyMessage.out != 0),
                    text: makeMsg(dirtyMessage.body.split('<br>').join('\n')),
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
                id: data,
                isNew: false,
                isViewer: true,
                text: makeMsg(text),
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