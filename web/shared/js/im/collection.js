var Collection = Class.extend({
    _modelClass: Model,
    _models: null,

    init: function() {
        this._models = {};
    },

    get: function(key) {
        return arguments.length ? this._models[key] : this._models;
    },
    add: function(key, model) {
        if (typeof key === 'undefined') {
            throw new Error('Key is undefined');
        } else if (!(model instanceof this._modelClass)) {
            throw new TypeError('Model not found');
        }
        key = key.toString();
        this._models[key] = model;
        return this;
    },
    remove: function(key) {
        delete this._models[key];
        return this;
    },
    has: function(key) {
        return !!this._models[key];
    }
});

var UserCollection = Collection.extend({
    _modelClass: UserModel
});

var MessageCollection = Collection.extend({
    _modelClass: MessageModel
});
