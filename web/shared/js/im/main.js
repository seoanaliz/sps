var Configs = {
    vkId: 4718705,
    appId: vk_appId,
    controlsRoot: controlsRoot
};

var cur = {
    dataUser: {}
};

$(document).ready(function() {
    new IM({
        el: '#main'
    });
});

/* Instant Messenger */
var IM = Widget.extend({
    template: MAIN,
    leftColumn: null,
    rightColumn: null,

    run: function() {
        var t = this;

        t._super();
        t.leftColumn = t.initLeftColumn();
        t.rightColumn = t.initRightColumn();

        t.rightColumn.on('selectDialogs', function(id) {
            t.leftColumn.showDialogs(id);
        });
        t.rightColumn.on('selectMessages', function(id) {
            t.leftColumn.showMessages(id);
        });
    },

    initLeftColumn: function() {
        return new LeftColumn({
            el: '.left-column'
        });
    },
    initRightColumn: function() {
        return new RightColumn({
            el: '.right-column'
        });
    }
});

var LeftColumn = Widget.extend({
    template: LEFT_COLUMN,
    dialogs: null,
    messages: null,
    tabs: null,

    run: function() {
        var t = this;

        t._super();
        t.dialogs = t.initDialogs();
        t.messages = t.initMessages();
        t.tabs = t.initTabs();
        t.showDialogs();
    },

    initTabs: function() {
        var t = this;
        var tabs = new Tabs({
            el: $(this.el).find('.header'),
            data: {tabs: []}
        });
        tabs.on('select', function(id) {
            if (id == 'messages') {
                t.showMessages(id);
            } else {
                t.showDialogs(id);
            }
        });

        return tabs;
    },
    initDialogs: function() {
        var t = this;
        var dialogs = new Dialogs({
            el: $(this.el).find('.list'),
            data: {list: Data.dialogs},
            run: function() {}
        });
        dialogs.on('select', function(id) {
            t.showMessages(id);
        });

        return dialogs;
    },
    initMessages: function() {
        var t = this;
        var messages = new Messages({
            el: $(this.el).find('.list'),
            data: {list: Data.dialogs},
            run: function() {}
        });

        return messages;
    },

    showDialogs: function(listId) {
        var t = this;

        t.dialogs.renderTemplate();
        t.tabs.addTab('dialogs', 'Диалоги');
        t.tabs.selectTab('dialogs');
    },
    showMessages: function(dialogId) {
        var t = this;

        t.messages.renderTemplate();
        t.tabs.addTab('messages', 'Сообщения');
        t.tabs.selectTab('messages');
    }
});

var RightColumn = Widget.extend({
    template: RIGHT_COLUMN,
    list: null,

    run: function() {
        var t = this;

        t._super();
        t.list = t.initList();
    },

    initList: function() {
        var t = this;
        var list = new List({
            el: $(this.el).find('.list'),
            data: {list: Data.lists}
        });
        list.on('selectDialogs', function(id) {
            t.trigger('selectDialogs');
        });
        list.on('selectMessages', function(id) {
            t.trigger('selectMessages');
        });

        return list;
    }
});

var Dialogs = Widget.extend({
    template: DIALOGS,

    events: {
        'click: .dialog': 'clickDialog'
    },

    clickDialog: function(e) {
        var t = this;
        t.selectDialog($(e.currentTarget).data('id'), true);
    },

    selectDialog: function(id, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', id);
    },

    renderTemplate: function() {
        var t = this;

        t._super();
        $(t.el).find('.date').easydate({
            live: true,
            date_parse: function(date) {
                date = intval(date) * 1000;
                if (!date) return;
                return new Date(date);
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
    }
});

var Messages = Widget.extend({
    template: MESSAGES,

    events: {
        'click: .message': 'clickMessage'
    },

    clickMessage: function(e) {
        var t = this;
        t.selectMessage($(e.currentTarget).data('id'), true);
    },

    selectMessage: function(id, isTrigger) {
        var t = this;
        if (isTrigger) t.trigger('select', id);
    },

    renderTemplate: function() {
        var t = this;

        t._super();
        $(t.el).find('.date').easydate({
            live: true,
            date_parse: function(date) {
                if (!date) return;
                return new Date(date);
            },
            uneasy_format: function(date) {
                return date.toLocaleDateString();
            }
        });
    }
});

var List = Widget.extend({
    template: LIST,

    events: {
        'click: .item > .title': 'selectDialogs',
        'click: .public': 'selectMessages'
    },

    selectDialogs: function(e) {
        var t = this;
        t.trigger('selectDialogs', $(e.currentTarget).data('id'));
    },
    selectMessages: function(e) {
        var t = this;
        t.trigger('selectMessages', $(e.currentTarget).data('id'));
    }
});

var Tabs = Widget.extend({
    template: TABS,
    tabTemplate: TABS_ITEM,

    events: {
        'click: .tab': 'clickTab'
    },

    clickTab: function(e) {
        var t = this;
        t.selectTab($(e.currentTarget).data('id'), true);
    },

    selectTab: function(id, isTrigger) {
        var t = this;

        $(t.el).find('.tab.selected').removeClass('selected');
        t.getTab(id).addClass('selected');

        if (isTrigger) t.trigger('select', id);
    },
    getTab: function(id) {
        var t = this;

        return $(t.el).find('.tab[data-id="' + id + '"]');
    },
    getSelectedTab: function() {
        var t = this;

        return $(t.el).find('.tab.selected');
    },
    addTab: function(id, title) {
        var t = this;

        if (t.getTab(id).length) return;
        $(t.el).find('.tab-bar').append(tmpl(t.tabTemplate, {id: id, title: title}));
    },
    removeTab: function(id) {
        var t = this;

        t.getTab(id).remove();
    }
});

function test(i) {
    var $0 = window.$0 || document.getElementsByTagName('html')[0];
    var height = 1500 + i;
    var ver = new Date().getTime();
    var src = 'http://placehold.it/5000x' + height + '?' + ver;

    $0.innerHTML = '<img src="' + src + '" />';
    var img = new Image();
    img.onload = function() {
        var newImg = $('img');//document.getElementsByTagName('img')[0];
        console.log(newImg.width() + 'x' + newImg.height());
        //alert(newImg.width + 'x' + newImg.height);
        test(i + 1);
    };
    img.src = src;
}
function test2() {
    var $0 = window.$0 || document.getElementsByTagName('body')[0];
    var ver = new Date().getTime();
    var html = '';
    html += '<div class="attachments">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
    '</div>';

    html += '<div class="attachments">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/94/9417376956f22ed5dd5789cd52dcb1d8.jpg?' + ver + '">' +
    '</div>';

    html += '<div class="attachments">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/94/9417376956f22ed5dd5789cd52dcb1d8.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/ee/eeac12954cbfeccd3973a9a2fc6aa711.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/ed/ed77f5f4d2e169e6455cd79191d6c220.jpg?' + ver + '">' +
    '</div>';

    html += '<div class="attachments">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/94/9417376956f22ed5dd5789cd52dcb1d8.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/ee/eeac12954cbfeccd3973a9a2fc6aa711.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/ed/ed77f5f4d2e169e6455cd79191d6c220.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/ed/ed77f5f4d2e169e6455cd79191d6c220.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/c/c2/c2072e4a7bcb7e4fd91724c8153ad44a.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/3/35/35ceef02404566ebdaccc807a46e9558.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/1/17/175f3f8c61b46e2bc9357ddecd0230f7.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/e/e5/e568f3aaf2747b035439abc3ebcf799a.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/7/75/75b051072085e1f70c64ff18aad6df50.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/7/75/75b051072085e1f70c64ff18aad6df50.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/1/19/19312d9ec14003c869e7e41196959a85.jpg?' + ver + '">' +
        '<img src="http://media.sps.verumnets.ru/article-photos/original/9/90/9029a4c2d4ed4651ef031d148132ffb4.jpg?' + ver + '">' +
    '</div>';

    $0.innerHTML = html;
    $('.attachments').imageComposition();
}
