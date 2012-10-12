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
            lists: [],
            dialogs: []
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var TabModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            label: '...',
            isSelected: false
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var DialogsModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            id: null,
            list: [new DialogModel().data()]
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
            viewer: new UserModel().data(),
            user: new UserModel().data(),
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
            viewer: new UserModel().data(),
            user: new UserModel().data(),
            list: [new MessageModel().data()]
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
            viewer: new UserModel().data(),
            user: new UserModel().data(),
            text: '',
            timestamp: 0,
            attachments: [],
            dialogId: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var GroupsModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            list: [new GroupModel().data()],
            counter: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});

var GroupModel = Model.extend({
    init: function(data) {
        this._defData = $.extend({
            isRead: false,
            id: null,
            title: '',
            counter: null
        }, data);
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    }
});