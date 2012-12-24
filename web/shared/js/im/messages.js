var Messages = EndlessPage.extend({
    _template: MESSAGES,
    _modelClass: MessagesModel,
    _templateItem: MESSAGES_ITEM,
    _templateLoading: MESSAGES_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.messages',
    _service: 'get_messages',
    _pageLoaded: null,
    _isTop: true,

    _events: {
        'click: .button.send': 'clickSend',
        'hover: .message.new': 'hoverMessage',
        'keydown: textarea': 'keyDownTextarea'
    },

    clickSend: function() {
        var t = this;
        t.sendMessage();
    },
    keyDownTextarea: function(e) {
        var t = this;
        if ((e.ctrlKey || e.metaKey) && e.keyCode == KEY.ENTER) {
            t.sendMessage();
        }
    },
    hoverMessage: function(e) {
        var t = this;
        t.trigger('hoverMessage', e);
    },

    renderTemplateLoading: function() {
        var t = this;
        var dialogId = t.pageId();
        var messageModel = dialogCollection.get(dialogId);
        var messageId = messageModel ? messageModel.messageId() : false;
        var userId = new UserModel(dialogCollection.get(dialogId).user()).id();
        t.model().user(userCollection.get(userId));
        t.model().viewer(userCollection.get(Configs.vkId));
        if (messageCollection.get(messageId)) {
            t.model().preloadList([messageCollection.get(messageId).data()]);
        }

        t._super.apply(this, arguments);
        t.makeList(t.el().find(t._itemsSelector));
    },
    onShow: function() {
        var t = this;
        t.updateTopPadding();
        t.scrollBottom();
    },
    onLoad: function(data) {
        var t = this;
        var user = data.user;
        var viewer = data.viewer;
        var messages = data.list;
        if (!messages.length) {
            t.ended(true);
        }
        t.model().id(t.pageId());
        t.model().user(user);
        t.model().viewer(viewer);
        t.model().list(t.model().list().concat(messages));

        var userModel = new UserModel(user);
        userCollection.add(userModel.id(), userModel);

        for (var i in messages) {
            if (!messages.hasOwnProperty(i)) continue;
            if (!messages[i].isViewer) messages[i].user = userModel.data();
            var messageModel = new MessageModel(messages[i]);
            messageCollection.add(messageModel.id(), messageModel);
        }
    },
    onRender: function() {
        var t = this;
        t.onShow();
        t.makeTextarea(t.el().find('textarea:first'));
        if (t.checkAtTop()) {
            t.onScroll();
        }
    },
    makeList: function($list) {
        $list.find('.videos').imageComposition({width: 500, height: 240});
        $list.find('.photos').imageComposition({width: 500, height: 300});
        $list.find('.date').each(function() {
            var $date = $(this);
            var timestamp = intval($date.text());
            $date.html(makeDate(timestamp));
        });
    },
    makeTextarea: function($textarea) {
        var t = this;
        $textarea.placeholder();
        $textarea.autoResize();
        $textarea.inputMemory('message' + t.pageId());
        $textarea.focus();
        $textarea[0].scrollTop = $textarea[0].scrollHeight;
        t.updateAutocomplite();
    },
    updateAutocomplite: function() {
        var t = this;
        var $textarea = t.el().find('textarea:first');
        var dialogId = t.pageId();
        var dialogModel = dialogCollection.get(dialogId);
        var listId = dialogModel.lists()[dialogModel.lists().length-1];
        var deferred = Control.fire('get_templates', {listId: listId});
        deferred.success(function(data) {
            $textarea.autocomplete({
                position: 'top',
                notFoundText: '',
                data: data,
                strictSearch: true,
                getValue: function() {
                    var text = $.trim($textarea.val());
                    return text ? text : 'notShowAllItems';
                },
                onchange: function(item) {
                    $textarea.val(item.title);
                }
            });
        });
    },
    updateTopPadding: function() {
        var t = this;
        if (!t.isVisible()) return;
        var $messages = t.el().find(t._itemsSelector);
        $messages.css('padding-top', $(window).height() - $messages.height() - 152);
    },
    scrollBottom: function() {
        var t = this;
        if (!t.isVisible()) return;
        $(window).scrollTop($(document).height());
    },
    sendMessage: function() {
        var t = this;
        var $el = t.el();
        var $textarea = $el.find('textarea');
        var text = $.trim($textarea.val());

        if (text) {
            $textarea.val('');
            var newMessageModel = new MessageModel({
                id: 'loading',
                isNew: true,
                isViewer: true,
                text: makeMsg(text),
                timestamp: Math.floor(new Date().getTime() / 1000),
                viewer: userCollection.get(Configs.vkId),
                dialogId: t.pageId()
            });
            var $newMessage = t.addMessage(newMessageModel);
            $newMessage.addClass('loading');
            $textarea.focus();
            var deferred = Control.fire('send_message', {pageId: t.pageId(), text: text});
            deferred.success(function(messageId) {
                if (!messageId) {
                    $textarea.val(text);
                    $newMessage.remove();
                    return;
                }
                $newMessage.removeClass('loading').attr('data-id', messageId);
            });
        } else {
            $textarea.focus();
        }
    },
    addMessage: function(messageModel) {
        var t = this;
        if (!(messageModel instanceof MessageModel)) throw new TypeError('Message is not correct');
        if (messageModel.dialogId() != t.pageId()) return false;

        var $el = t.el();
        var $message = $(t.tmpl()(t._templateItem, messageModel));
        var $oldMessage = $el.find('[data-id=' + messageModel.id() + ']');
        if ($oldMessage.length) {
            $oldMessage.remove();
        }
        $message.appendTo($el.find(t._itemsSelector));
        t.makeList($message);
        t.scrollBottom();
        return $message;
    },
    readMessage: function(messageId) {
        var t = this;
        var $el = t.el();
        var $message = $el.find('.message[data-id=' + messageId + ']');
        $message.removeClass('new');
    },
    onScroll: function() {
        this._super.apply(this, arguments);
        var t = this;
        t.updateTopPadding();
    }
});
