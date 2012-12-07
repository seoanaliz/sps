var Model = Event.extend({
    _data: null,
    _defData: null,

    init: function(data) {
        this._data = {};
        this.setData(data);
    },
    setData: function(data) {
        this._defData = this._defData || {};
        this._data = $.extend(this._defData, data);
    },
    get: function(key) {
        return this._data[key];
    },
    set: function(key, value) {
        this._data[key] = value;
        return this;
    },
    data: function(key, value) {
        if (typeof key === 'object') {
            this.setData(key);
            return this._data;
        } else if (typeof key !== 'undefined' && typeof value !== 'undefined') {
            key += '';
            return this.set(key, value);
        } else if (key) {
            key = key.toString();
            return this.get(key);
        } else {
            return this._data;
        }
    }
});
