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
        simpleAjax('saveAt', {}, function() {
            callback(Data.users[0]);
        })
    },
    get_lists: function(callback) {
        simpleAjax('getGroupList', function() {
            callback(Data.lists);
        });
    },
    get_dialogs: function(listId, callback) {
        simpleAjax('getDialogsList', {}, function() {
            callback(Data.dialogs);
        });
    },
    get_messages: function(dialogId, callback) {
        simpleAjax('getDialog', {}, function() {
            callback(Data.messages);
        });
    },
    send_message: function(text, callback) {
        simpleAjax('messages.send', {}, function() {
            callback($.extend(Data.messages[0], {
                text: text.split('\n').join('<br/>'),
                timestamp: Math.floor(new Date().getTime() / 1000)
            }));
        });
    },
    message_mark_as_read: function(messageId, callback) {
        simpleAjax('markMes', {}, function() {
            callback(true);
        });
    },
    add_to_list: function(dialogId, listId, callback) {
        callback(true);
    },
    remove_from_list: function(dialogId, listId, callback) {
        callback(true);
    }
};
$.extend(Events.eventList, Eventlist);