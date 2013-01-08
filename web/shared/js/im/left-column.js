var LeftColumn = Widget.extend({
    _template: LEFT_COLUMN,
    _dialogs: null,
    _dialogsSelector: '#list-dialogs',
    _messages: null,
    _messagesSelector: '#list-messages',
    _tabs: null,
    _tabsSelector: '.header',

    run: function() {
        var t = this;
        t.renderTemplate();

        t._tabs = new Tabs({
            selector: t._tabsSelector,
            model: new TabsModel()
        });
        t._messages = new Messages({
            selector: t._messagesSelector,
            model: new MessagesModel()
        });
        t._dialogs = new Dialogs({
            selector: t._dialogsSelector,
            model: new DialogsModel()
        });

        t._dialogs.on('clickDialog', function(e) {
            var $dialog = $(e.currentTarget);
            var dialogId = $dialog.data('id');
            t.showDialog(dialogId, true);
        });

        t._dialogs.on('addList', function() {
            t.trigger('addList');
        });

        t._dialogs.on('addToList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._dialogs.on('removeFromList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._messages.on('hoverMessage', function(e) {
            var $message = $(e.currentTarget);
            var messageId = $message.data('id');
            var dialogId = t._messages.pageId();
            if ($message.hasClass('viewer')) return;
            t._messages.readMessage(messageId);
            t._dialogs.readDialog(dialogId);
            Events.fire('message_mark_as_read', messageId, dialogId, function() {
                t.trigger('markAsRead');
            });
        });

        t._tabs.on('clickDialog', function(e) {
            var $tab = $(e.currentTarget);
            var dialogId = $tab.data('id');
            t.showDialog(dialogId, true);
        });

        t._tabs.on('clickList', function(e) {
            var $tab = $(e.currentTarget);
            var listId = $tab.data('id');
            t.showList(listId, true);
        });

        t._tabs.on('addList', function() {
            t.trigger('addList');
            t._messages.updateAutocomplite();
        });

        t._tabs.on('clickFilter', function() {
            t._dialogs.toggleFilter();
        });

        t._tabs.on('addToList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._tabs.on('removeFromList', function(listId, dialogId) {
            if (dialogId == t._messages.pageId()) {
                t._messages.updateAutocomplite();
            }
        });

        t._tabs.on('templatesUpdate', function() {
            t._messages.updateAutocomplite();
        });
    },
    onScroll: function(e) {
        var t = this;
        t._messages.onScroll(e);
        t._dialogs.onScroll(e);
    },

    showList: function(listId, isTrigger) {
        var t = this;
        if (!t._dialogs.isVisible()) {
            t._messages.hide();
            t._dialogs.show();
        }
        t._dialogs.changePage(listId);
        t._tabs.setList(listId);
        if (isTrigger) t.trigger('changeList', listId);
    },

    showDialog: function(dialogId, isTrigger) {
        var t = this;
        if (!t._messages.isVisible()) {
            t._dialogs.hide();
            t._messages.show();
        }
        t._messages.changePage(dialogId);
        t._tabs.setDialog(dialogId);
        if (isTrigger) t.trigger('changeDialog', dialogId);
    },

    setOnline: function(userId) {
        var t = this;
        t._tabs.setOnline(userId);
    },
    setOffline: function(userId) {
        var t = this;
        t._tabs.setOffline(userId);
    },
    addMessage: function(messageModel) {
        var t = this;
        t._messages.addMessage(messageModel);
    },
    addDialog: function(dialog) {
        var t = this;
        t._dialogs.addDialog(dialog);
    },
    readMessage: function(messageId) {
        var t = this;
        t._messages.readMessage(messageId);
    },
    readDialog: function(dialogId) {
        var t = this;
        t._dialogs.readDialog(dialogId);
    }
});
