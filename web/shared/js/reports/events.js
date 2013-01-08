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
                var error_text = '';
                if (result.response == 'matches')
                     error_text = 'На это время уже назначен подобный обмен';
                else if(result.response =='wrong publics data')
                    error_text = 'Не получилось распознать паблики. Попробуйте ввести' +
                        ' название в виде "http://vk.com/public123456"';
                if (error_text) {
                    var confirmBox = new Box({
                        title: 'Ошибка',
                        html: error_text,
                        buttons: [
                            {label: 'Ок'}
                        ]
                    }).show();

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
        tmpDate = new Date();
        simpleAjax('addReport', {
            targetPublicId: ourPublicId,
            barterPublicId: publicId,
            startTime: timeStart,
            stopTime: timeStop,
            timeShift: tmpDate.getTimezoneOffset()
        }, function(data) {
            callback(data);
        })
    }
};
$.extend(Events.eventList, Eventlist);
