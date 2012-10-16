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
    // Shortcut
    data: function(key, value) {
        if (typeof key === 'object') {
            this.setData(key);
            return this._data;
        } else if (key && value) {
            key = key.toString();
            return this.set(key, value);
        } else if (key) {
            key = key.toString();
            return this.get(key);
        } else {
            return this._data;
        }
    }
});

var UserModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            name: '...',
            photo: 'http://vk.com/images/camera_c.gif',
            isOnline: false
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var TabsModel = Model.extend({
    init: function() {
        this._defData = {
            list: null,
            dialog: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var TabModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            label: '...',
            isSelected: false,
            isOnline: false,
            isOnList: false
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var DialogsModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            list: [new DialogModel()]
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var DialogModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            isNew: false,
            isViewer: false,
            viewer: new UserModel(),
            user: new UserModel(),
            text: '',
            timestamp: 0,
            attachments: [],
            lists: [],
            messageId: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var MessagesModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            user: new UserModel(),
            viewer: new UserModel(),
            list: []
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var MessageModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            isNew: false,
            isViewer: false,
            viewer: new UserModel(),
            user: new UserModel(),
            text: '',
            timestamp: 0,
            attachments: [],
            dialogId: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var ListsModel = Model.extend({
    init: function() {
        this._defData = {
            list: [new ListModel()],
            counter: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var ListModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            title: '...',
            counter: null,
            isRead: false,
            isSelected: false,
            isDraggable: true
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});