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

var MetricsModel = Model.extend({
    init: function() {
        this._defData = {
            a: '-',
            b: '-',
            c: '-'
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    a: function(a) {
        if (arguments.length) a = a;
        return this.data('a', a);
    },
    b: function(b) {
        if (arguments.length) b = b;
        return this.data('b', b);
    },
    c: function(c) {
        if (arguments.length) c = c;
        return this.data('c', c);
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
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    name: function(name) {
        if (arguments.length) name += '';
        return this.data('name', name);
    },
    photo: function(photo) {
        if (arguments.length) photo += '';
        return this.data('photo', photo);
    },
    isOnline: function(isOnline) {
        if (arguments.length) isOnline = !!isOnline;
        return this.data('isOnline', isOnline);
    }
});

var TabsModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            listTab: null,
            dialogTab: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    listTab: function(listTab) {
        if (arguments.length) listTab = listTab || new TabModel();
        return this.data('listTab', listTab);
    },
    dialogTab: function(dialogTab) {
        if (arguments.length) dialogTab = dialogTab || new TabModel();
        return this.data('dialogTab', dialogTab);
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
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    label: function(label) {
        if (arguments.length) label = label + '';
        return this.data('label', label);
    },
    isSelected: function(isSelected) {
        if (arguments.length) isSelected = !!isSelected;
        return this.data('isSelected', isSelected);
    },
    isOnline: function(isOnline) {
        if (arguments.length) isOnline = !!isOnline;
        return this.data('isOnline', isOnline);
    },
    isOnList: function(isOnList) {
        if (arguments.length) isOnList = !!isOnList;
        return this.data('isOnList', isOnList);
    }
});

var AuthorsModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            list: []
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    list: function(list) {
        if (arguments.length) list = list || [];
        return this.data('list', list);
    }
});

var AuthorModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            user: new UserModel(),
            metrick1: new MetricsModel(),
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    metrick1: function(metrick1) {
        if (arguments.length) metrick1 = metrick1 || new MetricsModel();
        return this.data('metrick1', metrick1);
    },

    user: function(user) {
        if (arguments.length) user = user || new UserModel();
        return this.data('user', user);
    }
//    text: function(text) {
//        if (arguments.length) text = text + '';
//        return this.data('text', text);
//    },
//    timestamp: function(timestamp) {
//        if (arguments.length) timestamp = intval(timestamp);
//        return this.data('timestamp', timestamp);
//    },
//    attachments: function(attachments) {
//        if (arguments.length) attachments = attachments.length || [];
//        return this.data('attachments', attachments);
//    },
//    lists: function(lists) {
//        if (arguments.length) lists = lists.length || [];
//        return this.data('lists', lists);
//    }
});

var MessagesModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            user: new UserModel(),
            viewer: new UserModel(),
            list: [],
            preloadList: []
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    user: function(user) {
        if (arguments.length) user = user || new UserModel();
        return this.data('user', user);
    },
    viewer: function(viewer) {
        if (arguments.length) viewer = viewer || new UserModel();
        return this.data('viewer', viewer);
    },
    list: function(list) {
        if (arguments.length) list = list || [];
        return this.data('list', list);
    },
    preloadList: function(preloadList) {
        if (arguments.length) preloadList = preloadList || [];
        return this.data('preloadList', preloadList);
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
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    dialogId: function(dialogId) {
        if (arguments.length) dialogId = intval(dialogId);
        return this.data('dialogId', dialogId);
    },
    isNew: function(isNew) {
        if (arguments.length) isNew = !!isNew;
        return this.data('isNew', isNew);
    },
    isViewer: function(isViewer) {
        if (arguments.length) isViewer = !!isViewer;
        return this.data('isViewer', isViewer);
    },
    viewer: function(viewer) {
        if (arguments.length) viewer = viewer || new UserModel();
        return this.data('viewer', viewer);
    },
    user: function(user) {
        if (arguments.length) user = user || new UserModel();
        return this.data('user', user);
    },
    text: function(text) {
        if (arguments.length) text = text + '';
        return this.data('text', text);
    },
    timestamp: function(timestamp) {
        if (arguments.length) timestamp = intval(timestamp);
        return this.data('timestamp', timestamp);
    },
    attachments: function(attachments) {
        if (arguments.length) attachments = attachments.length || [];
        return this.data('attachments', attachments);
    }
});

var ListsModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            list: [],
            counter: null
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    list: function(list) {
        if (arguments.length) list = list || [];
        return this.data('list', list);
    },
    counter: function(counter) {
        if (arguments.length) counter = intval(counter);
        return this.data('counter', counter);
    }
});

var ListModel = Model.extend({
    init: function() {
        this._defData = {
            id: null,
            title: '...',
            isSelected: false,
            isDraggable: true
        };
        this._super.apply(this, Array.prototype.slice.call(arguments, 0));
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    title: function(title) {
        if (arguments.length) title = title + '';
        return this.data('title', title);
    },
    isSelected: function(isSelected) {
        if (arguments.length) isSelected = !!isSelected;
        return this.data('isSelected', isSelected);
    },
    isDraggable: function(isDraggable) {
        if (arguments.length) isDraggable = !!isDraggable;
        return this.data('isDraggable', isDraggable);
    },
    isEditable: function(isEditable) {
        if (arguments.length) isEditable = !!isEditable;
        return this.data('isEditable', isEditable);
    }
});