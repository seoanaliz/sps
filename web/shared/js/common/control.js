/**
 * @description Основной объект для вызова серверных методов
 */
var Control = {
    root: '',
    dataType: 'json',
    controlMap: {},

    /**
     * Шорткат для Control.fire
     * @param {string} method
     * @param {object=} params
     * @param {function=} callback
     * @returns {Deferred}
     */
    call: function(method, params, callback) {
        return this.fire.apply(this, arguments);
    },

    /**
     * ...
     * @param {string} method
     * @param {object=} data
     * @param {function=} callback
     * @returns {Deferred}
     */
    fire: function(method, data, callback) {
        var t = this;
        var params = $.extend({}, t.commonParams, t.defaultParams);
        var control = t.controlMap[method] || {};
        var dataType = control.dataType || t.dataType;
        var controlName = control.name || method;
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
                } else {
                    callback(data);
                }
                deferred.fireSuccess(data);
            },
            error: function() {
                deferred.fireError();
            }
        });

        return deferred;
    },

    /**
     * ...
     * @param {string} method
     * @param {object=} params
     * @param {function=} callback
     * @returns {Deferred}
     */
    callVK: function(method, params, callback) {
        var deferred = new Deferred();

        if (!$.cookie('accessToken')) {
            deferred.fireError('accessToken is not exist!');
        } else {
            $.ajax({
                dataType: 'jsonp',
                url: 'https://api.vk.com/method/' + method,
                data: $.extend({access_token: $.cookie('accessToken')}, params)
            }).always(function(data) {
                if (data && data.response) {
                    if (typeof callback == 'function') {
                        callback(data.response);
                    }
                    deferred.fireSuccess(data.response);
                } else {
                    deferred.fireError(data);
                }
            });
        }

        return deferred;
    }
};
