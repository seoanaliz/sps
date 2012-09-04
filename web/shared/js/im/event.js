var Event = Class.extend({
    on: function(eventName, callback) {
        var events = eventName.split(' ');
        if (events.length > 1) {
            for (var event in events) {
                this.on(event, callback);
            }
        } else {
            eventName = events[0];
        }

        if (!this._callbacks) this._callbacks = {};
        if (!this._callbacks[eventName]) this._callbacks[eventName] = [];
        this._callbacks[eventName].push(callback);

        return this;
    },

    off: function(eventName, callback) {
        var events = eventName.split(' ');
        if (events.length > 1) {
            for (var event in events) {
                this.off(event, callback);
            }
        } else {
            eventName = events[0];
        }

        if (!this._callbacks) this._callbacks = {};
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i] === callback) {
                this._callbacks[eventName][i] = null;
            }
        }

        return this;
    },

    trigger: function(eventName) {
        var events = eventName.split(' ');
        if (events.length > 1) {
            for (var event in events) {
                this.trigger(event);
            }
        } else {
            eventName = events[0];
        }

        if (!this._callbacks) this._callbacks = {};
        for (var i in this._callbacks[eventName]) {
            if (this._callbacks[eventName][i]) this._callbacks[eventName][i].apply(this, Array.prototype.slice.call(arguments, 1));
        }

        return this;
    }
});