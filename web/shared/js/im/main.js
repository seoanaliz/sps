var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    hostName: hostname,
    commonDialogsList: 999999,
    disableAutocomplete: false
};

var userCollection = new UserCollection();
var messageCollection = new Collection();

var Main = Widget.extend({
    template: MAIN,

    _leftColumn: null,
    _leftColumnSelector: '#left-column',
    _rightColumn: null,
    _rightColumnSelector: '#right-column',

    run: function() {
        var t = this;
        t.renderTemplate();

        t._leftColumn = new LeftColumn({
            selector: t._leftColumnSelector
        });

        t._rightColumn = new RightColumn({
            selector: t._rightColumnSelector,
            model: new GroupsModel()
        });

        t.on('scroll', function(e) {
            t._leftColumn.onScroll(e);
        });
    }
});

var LeftColumn = Widget.extend({
    template: LEFT_COLUMN,

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
        t._dialogs.show();
        t._dialogs.on('clickDialog', function(e) {
            var $dialog = $(e.currentTarget);
            var dialogId = $dialog.data('id');
            t._dialogs.hide();
            t._messages.show().changePage(dialogId);
        });
    },

    onScroll: function(e) {
        var t = this;
        t._messages.onScroll(e);
        t._dialogs.onScroll(e);
    }
});

var Page = Widget.extend({
    _isVisible: false,
    _isBlock: false,

    init: function() {
        var t = this;
        t._super.apply(t, Array.prototype.slice.call(arguments, 0));
    },
    isVisible: function() {
        return !!this._isVisible;
    },
    show: function() {
        var t = this;
        t._isVisible = true;
        t.$el.show();
        return this;
    },
    hide: function() {
        var t = this;
        t._isVisible = false;
        t.$el.hide();
        return this;
    },
    isLock: function() {
        return !!this._isBlock;
    },
    lock: function() {
        this._isBlock = true;
    },
    unlock: function() {
        this._isBlock = false;
    }
});

var EndlessPage = Page.extend({
    _templateLoading: '',
    _templateItem: '',
    _itemsLimit: 20,
    _itemsSelector: '',
    _service: 'get_dialogs',
    _pageLoaded: 0,
    _pageId: null,
    _isTop: false,

    run: function() {
        var t = this;

        if (t._pageId) {
            t.changePage(t._pageId);
        }
    },
    renderTemplateLoading: function() {
        var t = this;
        t.$el.html(t.tmpl(t._templateLoading, (t.model && t.model.data())));
        return this;
    },
    changePage: function(pageId) {
        var t = this;
        t._pageId = pageId;

        t.renderTemplateLoading();

        var limit = t._itemsLimit;
        var offset = t._pageLoaded * limit;
        Events.fire(t._service, t._pageId, offset, limit, function(data) {
            t.model.data(data);
            t.renderTemplate();
        });
    },
    showMore: function() {
        var t = this;
        var nextPage = t._pageLoaded + 1;
        var limit = t._itemsLimit;
        var offset = nextPage * limit;

        if (!t.isLock()) {
            t.lock();
            Events.fire(t._service, t._pageId, offset, limit, function(data) {
                t._pageLoaded = nextPage;

                var $list = t.$el.find(t._itemsSelector);
                var $first = $list.first();
                var bottom = $(window).scrollTop() - $first.offset().top;
                var html = '';
                $.each(data.list, function(i, obj) {
                    html += tmpl(t._templateItem, obj);
                });
                if (t._isTop) {
                    $list.prepend(html);
                    $(window).scrollTop(top);
                } else {
                    $list.append(html);
                }

                t.unlock();
            });
        }
    },
    checkAtTop: function() {
        var t = this;
        return !!($(window).scrollTop() < 300);
    },
    checkAtBottom: function() {
        var t = this;
        return !!($(window).scrollTop() >= $(document).height() - $(window).height() - 300);
    },
    onScroll: function() {
        var t = this;
        if (!t.isVisible()) return;
        if (t.checkAtTop() && t._isTop || t.checkAtBottom() && !t._isTop) {
            t.showMore();
        }
    }
});

var Dialogs = EndlessPage.extend({
    template: DIALOGS,
    modelClass: DialogsModel,

    _templateItem: DIALOGS_ITEM,
    _templateLoading: DIALOGS_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.dialogs',
    _service: 'get_dialogs',
    _pageLoaded: 0,
    _pageId: Configs.commonDialogsList,

    events: {
        'click: .dialog': 'clickDialog',
        'click: .action.icon': 'clickPlus'
    },

    clickDialog: function(e) {
        var t = this;
        t.trigger('clickDialog', e);
    },
    clickPlus: function(e) {
        var t = this;
        t.trigger('clickPlus', e);
    }
});

var Messages = EndlessPage.extend({
    template: MESSAGES,
    modelClass: MessagesModel,

    _templateItem: MESSAGES_ITEM,
    _templateLoading: MESSAGES_LOADING,
    _itemsLimit: 20,
    _itemsSelector: '.messages',
    _service: 'get_messages',
    _pageLoaded: 0,
    _isTop: true
});

var RightColumn = Widget.extend({
    template: RIGHT_COLUMN,
    modelClass: GroupsModel
});

var Tabs = Widget.extend({
    template: TABS,
    modelClass: TabsModel
});

/**
 * Initialization
 */
$(document).ready(function() {
    if (!$('#main').length) {
        return;
    }

    if (!Configs.vkId) {
        location.replace('/login/?' + btoa('im'));
        return;
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }

    if (!Configs.token) {
        location.replace('/im/login/? ' + btoa('im'));
        return;
    }

    var main = new Main({
        selector: '#main'
    });
    $(window).on('scroll resize', function() {
        main.trigger('scroll');
    });
});
