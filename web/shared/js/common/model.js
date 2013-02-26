var Model = (function() {
    var Model = Event.extend({
        _data: null,
        _defData: null,

        init: function(data) {
            this._data = {};
            this.setData(data);
        },
        setData: function(data) {
            this._data = $.extend(this.defData(), data);
        },
        get: function(key) {
            return this._data[key];
        },
        set: function(key, value) {
            this._data[key] = value;
            this.trigger(Model.EVENT_CHANGE);
            return this;
        },
        data: function(key, value) {
            if (typeof key === 'object' && typeof value === 'undefined') {
                this.setData(key);
                return this._data;
            } else if (typeof key !== 'undefined' && typeof value !== 'undefined') {
                key += '';
                return this.set(key, value);
            } else if (key) {
                key += '';
                return this.get(key);
            } else {
                return this._data;
            }
        },
        defData: function(key, value) {
            if (!this._defData) {
                this._defData = {};
            }
            if (typeof key === 'object' && typeof value === 'undefined') {
                this._defData = key;
                return this._defData;
            } else if (typeof key !== 'undefined' && typeof value !== 'undefined') {
                key += '';
                return this._defData[key] = value;
            } else if (key) {
                key += '';
                return this._defData[key];
            } else {
                return this._defData;
            }
        }
    });
    Model.EVENT_CHANGE = 'change';

    return Model;
})();
