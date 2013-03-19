/**
 * @description Основной объект для вызова серверных методов
 */
var Control = {
    root: '',
    dataType: 'json',
    controlMap: {},

    call: function() {
        this.fire.apply(this, arguments);
    },

    fire: function(key, data, callback) {
        var t = this;
        var params = $.extend({}, t.commonParams, t.defaultParams);
        var control = t.controlMap[key] || {};
        var dataType = control.dataType || t.dataType;
        var controlName = control.name || key;
        var controlDefaultParams = control.defaultParams || {};
        var root = control.root || t.root;
        for (var paramKey in data) {
            if (!data.hasOwnProperty(paramKey)) {
                continue;
            }
            if (typeof data[paramKey] == 'boolean') {
                data[paramKey] = intval(data[paramKey]);
            }
            var mappedParamKey = control.params && control.params[paramKey];
            if (mappedParamKey) {
                params[mappedParamKey] = data[paramKey];
            } else {
                params[paramKey] = data[paramKey];
            }
        }
        var deferred = new Deferred;

        $.ajax({
            url: root + controlName + '/',
            dataType: dataType,
            data: $.extend(controlDefaultParams, params),
            success: function(data) {
                if (typeof callback != 'function') {
                    if (typeof t.commonResponse == 'function') {
                        data = t.commonResponse(data);
                    }
                    if (typeof control.response == 'function') {
                        data = control.response(data);
                    }
                    if (typeof callback == 'function') {
                        callback(data);
                    }
                }
                deferred.fireSuccess(data);
            },
            error: function() {
                deferred.fireError();
            }
        });

        return deferred;
    }
};
