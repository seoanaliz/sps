/**
 * @description Основной класс для вызова серверных методов
 */
var Control = {
    root: '',
    dataType: 'json',
    controlMap: {},
    fire: function(key, data) {
        var t = this;
        var params = $.extend({}, t.commonParams);
        var control = t.controlMap[key] || {};
        var dataType = control.dataType || t.dataType;
        var controlName = control.name || key;
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
        var jQueryObj = $.ajax({
            url: root + controlName + '/',
            dataType: dataType,
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
