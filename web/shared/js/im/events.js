/**
 * Events
 */
var Events = {
    delay: 200,
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
        url: Events.url + method + '/',
        dataType: 'json',
        data: $.extend({
            userId: cur.dataUser.uid,
            type: 'mes'
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
    get_lists: function(callback) {
        callback(Data.lists);
    },
    get_dialogs: function(listId, callback) {
        callback(Data.dialogs);
    },
    get_messages: function(dialogId, callback) {
        callback(Data.messages);
    },
    send_message: function(text, callback) {
        callback($.extend(Data.messages[0], {
            text: text.split('\n').join('<br/>'),
            timestamp: Math.floor(new Date().getTime() / 1000)
        }));
    },
    add_to_list: function(dialogId, listId, callback) {
        callback(true);
    },
    remove_from_list: function(dialogId, listId, callback) {
        callback(true);
    }
};
$.extend(Events.eventList, Eventlist);