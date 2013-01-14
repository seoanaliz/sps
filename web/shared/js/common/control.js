/**
 * @description Основной класс для вызова серверных методов
 */
var Control = {
    root: '',
    fire: function(controlName, data) {
        var t = this;
        var params = $.extend({}, t.commonParams);
        var control = t.controlMap[controlName];
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
        var jQueryObj = $.ajax({
            url: t.root + control.name + '/',
            dataType: 'json',
            data: params
        });
        return {
            success: function(callback) {
                jQueryObj.success(function(data) {
                    if (typeof callback != 'function') {
                        return;
                    }
                    if (typeof t.commonResponse == 'function') {
                        data = t.commonResponse(data);
                    }
                    if (typeof control.response == 'function') {
                        data = control.response(data);
                    }
                    callback(data);
                });
                return this.success;
            },
            error: function(callback) {
                jQueryObj.error(function(data) {
                    callback(data);
                });
                return this.error;
            }
        }
    }
};
