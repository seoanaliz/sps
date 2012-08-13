var Event = Class.extend({
    on: function(eventName, callback) {
        if (!this._callbacks) this._callbacks = {};
        if (!this._callbacks[eventName]) this._callbacks[eventName] = [];
        this._callbacks[eventName].push(callback);

        return this;
    },

    off: function(eventName, callback) {
        if (!this._callbacks) this._callbacks = {};
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i] === callback) {
                this._callbacks[eventName][i] = null;
            }
        }

        return this;
    },

    trigger: function(eventName) {
        if (!this._callbacks) this._callbacks = {};
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i]) this._callbacks[eventName][i].apply(this, Array.prototype.slice.call(arguments, 1));
        }

        return this;
    }
});