var UserModel = Model.extend({
    init: function() {
        this.defData('id', null);
        this.defData('name', '...');
        this.defData('photo', 'http://vk.com/images/camera_c.gif');
        this.defData('isOnline', false);
        this._super.apply(this, arguments);
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
        this.defData('id', null);
        this.defData('listTab', null);
        this.defData('dialogTab', null);
        this._super.apply(this, arguments);
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
        this.defData('id', null);
        this.defData('label', '...');
        this.defData('isSelected', false);
        this.defData('isOnline', false);
        this.defData('isOnList', false);
        this._super.apply(this, arguments);
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

var PageModel = Model.extend({
    init: function() {
        this._super.apply(this, arguments);
        this.defData('id', null);
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    }
});

var EndlessPageModel = PageModel.extend({
    init: function() {
        this.defData('list', []);
        this.defData('preloadList', {});
        this._super.apply(this, arguments);
    },
    list: function(list) {
        if (arguments.length) list = list || [];
        return this.data('list', list);
    },
    preloadData: function(preloadList) {
        if (arguments.length) preloadList = preloadList || {};
        return this.data('preloadList', preloadList);
    }
});

var DialogsModel = EndlessPageModel.extend({
});

var DialogModel = Model.extend({
    init: function() {
        this.defData('id', null);
        this.defData('isNew', false);
        this.defData('isViewer', false);
        this.defData('viewer', new UserModel());
        this.defData('user', new UserModel());
        this.defData('text', '');
        this.defData('timestamp', 0);
        this.defData('attachments', []);
        this.defData('lists', []);
        this.defData('messageId', null);
        this._super.apply(this, arguments);
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
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
    },
    lists: function(lists) {
        if (arguments.length) lists = lists.length || [];
        return this.data('lists', lists);
    },
    messageId: function(messageId) {
        if (arguments.length) messageId = intval(messageId);
        return this.data('messageId', messageId);
    }
});

var MessagesModel = EndlessPageModel.extend({
    init: function() {
        this.defData('viewer', new UserModel());
        this.defData('user', new UserModel());
        this.defData('preloadList', []);
        this._super.apply(this, arguments);
    },
    user: function(user) {
        if (arguments.length) user = user || new UserModel();
        return this.data('user', user);
    },
    viewer: function(viewer) {
        if (arguments.length) viewer = viewer || new UserModel();
        return this.data('viewer', viewer);
    },
    preloadList: function(preloadList) {
        if (arguments.length) preloadList = preloadList || [];
        return this.data('preloadList', preloadList);
    }
});

var MessageModel = Model.extend({
    init: function() {
        this.defData('id', null);
        this.defData('isNew', false);
        this.defData('isViewer', false);
        this.defData('viewer', new UserModel());
        this.defData('user', new UserModel());
        this.defData('text', '');
        this.defData('timestamp', 0);
        this.defData('attachments', []);
        this.defData('dialogId', null);
        this._super.apply(this, arguments);
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
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
    },
    dialogId: function(dialogId) {
        if (arguments.length) dialogId = intval(dialogId);
        return this.data('dialogId', dialogId);
    }
});

var ListsModel = Model.extend({
    init: function() {
        this.defData('id', null);
        this.defData('list', []);
        this.defData('counter', null);
        this._super.apply(this, arguments);
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
        this.defData('id', null);
        this.defData('title', '...');
        this.defData('counter', null);
        this.defData('isRead', false);
        this.defData('isSelected', false);
        this.defData('isDraggable', true);
        this.defData('isEditable', true);
        this._super.apply(this, arguments);
    },
    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    title: function(title) {
        if (arguments.length) title = title + '';
        return this.data('title', title);
    },
    counter: function(counter) {
        if (arguments.length) counter = intval(counter);
        return this.data('counter', counter);
    },
    isRead: function(isRead) {
        if (arguments.length) isRead = !!isRead;
        return this.data('isRead', isRead);
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
