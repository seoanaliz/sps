Control = $.extend(Control, {
    root: controlsRoot,

    // Обработка каждого запроса
    commonParams: {
        type: 'mes',
        userId: 0
    },

    // Обработка каждого ответа
    commonResponse: function(data) {
        return data.response;
    },

    // Обработка запросов и ответов отдельных методов
    controlMap: {
        add_user: {
            name: 'saveAt'
        }
    }
});

/**
 * Events
 * @deprecated
 * @see Control
 */
var Events = {
    delay: 0,
    isDebug: false,
    eventList: {},
    fire: function(name){
        var t = this;
        var args = Array.prototype.slice.call(arguments, 1);
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

function simpleAjax(method, data, callback) {
    $.ajax({
        url: controlsRoot + method + '/',
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
}


