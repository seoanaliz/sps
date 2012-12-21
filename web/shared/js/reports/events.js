/**
 * Events
 */
var Events = {
    url: Configs.controlsRoot,
    delay: Configs.eventsDelay,
    isDebug: Configs.eventsIsDebug,
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
        url: Events.url + method + '/',
        dataType: 'json',
        data: data,
        success: function(result) {
            if (result && result.response) {

                if (result.response == 'matches') {
                    alert('Такой монитор уже есть');
                    return;
                }
                if (result.response =='wrong publics data') {
                    alert('Не получилось распознать паблики. Попробуйте ввести название в виде "http://vk.com/public123456" ');
                    return;
                }
                if ($.isFunction(data)) callback = data;
                callback(result.response);
            }
        }
    });
};

var Eventlist = {
    get_result_list: function(limit, offset, callback) {
        simpleAjax('getReportList', {limit: limit, offset: offset, state: 'complete'}, function(data) {
            callback(data);
        });
    },
    delete_report: function(ourPublicId, publicId, callback) {

        simpleAjax('deleteReport', {reportId: publicId, groupId: ourPublicId}, function(data) {
            callback(data);
        });
    },
    get_monitor_list: function(limit, offset, callback) {
        simpleAjax('getReportList', {limit: limit, offset: offset}, function(data) {
            callback(data);
        });
    },
    add_report: function(ourPublicId, publicId, timeStart, timeStop, callback) {
        simpleAjax('addReport', {
            targetPublicId: ourPublicId,
            barterPublicId: publicId,
            startTime: timeStart,
            stopTime: timeStop
        }, function(data) {
            callback(data);
        })
    }
};
$.extend(Events.eventList, Eventlist);
