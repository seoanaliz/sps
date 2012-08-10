var Event = Class.extend({
    _callbacks: {},

    on: function(eventName, callback) {
        if (!this._callbacks[eventName]) this._callbacks[eventName] = [];
        this._callbacks[eventName].push(callback);

        return this;
    },

    off: function(eventName, callback) {
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i] === callback) {
                this._callbacks[eventName][i] = null;
            }
        }

        return this;
    },

    trigger: function(eventName) {
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i]) this._callbacks[eventName][i].apply(this, Array.prototype.slice.call(arguments, 1));
        }

        return this;
    }
});