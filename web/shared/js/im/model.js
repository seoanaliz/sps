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
    },
    get: function(key) {
        return this._data[key];
    },
    set: function(key, value) {
        this._data[key] = value;
        return this;
    }
});

var UserModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            name: '...',
            photo: 'http://vk.com/images/camera_c.gif',
            isOnline: false
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var TabsModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            list: null,
            dialog: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var TabModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            label: '...',
            isSelected: false,
            isOnline: false,
            isOnList: false
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var DialogsModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            list: [new DialogModel()]
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var DialogModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
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
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var MessagesModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            user: new UserModel(),
            viewer: new UserModel(),
            list: [new MessageModel()]
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var MessageModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            isNew: false,
            isViewer: false,
            viewer: new UserModel(),
            user: new UserModel(),
            text: '',
            timestamp: 0,
            attachments: [],
            dialogId: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var ListsModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            list: [new ListModel()],
            counter: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var ListModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            title: '...',
            counter: null,
            isRead: false,
            isSelected: false,
            isDraggable: true
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});