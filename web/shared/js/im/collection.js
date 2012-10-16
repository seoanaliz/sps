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
        if (!key && key !== 0) {
            console.log(key);
            console.log(model);
            throw new Error('Key is not correct');
        } else if (!(model instanceof this._modelClass)) {
            throw new TypeError('Model is not correct');
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

var DialogCollection = Collection.extend({
    _modelClass: DialogModel
});

var ListCollection = Collection.extend({
    _modelClass: ListModel
});

var TabCollection = Collection.extend({
    _modelClass: TabModel
});