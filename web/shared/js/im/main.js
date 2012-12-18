var Configs = {
    vkId: $.cookie('uid'),
    token: $.cookie('token'),
    appId: vk_appId,
    controlsRoot: controlsRoot,
    hostName: hostname,
    commonDialogsList: 999999,
    disableAutocomplete: false,
    easyDateParams: {
        live: true,
        set_title: false,
        date_parse: function(date) {
            date = intval(date) * 1000;
            if (!date) return;
            return new Date(date);
        },
        uneasy_format: function(date) {
            return date.toLocaleDateString();
        }
    }
};

var userCollection = new UserCollection();
var messageCollection = new MessageCollection();
var dialogCollection = new DialogCollection();
var listCollection = new ListCollection();

var Main = Widget.extend({
    _template: MAIN,
    _leftColumn: null,
    _leftColumnSelector: '#left-column',
    _rightColumn: null,
    _rightColumnSelector: '#right-column',

    run: function() {
        var t = this;
        t.el().hide();

        Events.fire('get_viewer', function(data) {
            if (!data) {
                location.href = '/im/login/?' + encodeURIComponent('im/');
                return;
            }

            var viewer = new UserModel(data);
            userCollection.add(viewer.id(), viewer);

            t.el().fadeIn(500);
            t.renderTemplate();

            t._leftColumn = new LeftColumn({
                selector: t._leftColumnSelector
            });
            t._rightColumn = new RightColumn({
                selector: t._rightColumnSelector,
                model: new ListsModel()
            });

            t.on('scroll', function(e) {
                t._leftColumn.onScroll(e);
            });

            t._leftColumn.on('changeDialog', function(dialogId) {
                t._rightColumn.setDialog(dialogId);
            });
            t._leftColumn.on('changeList', function(listId) {
                t._rightColumn.setList(listId);
            });
            t._leftColumn.on('addList', function() {
                t._rightColumn.update();
            });
            t._leftColumn.on('markAsRead', function() {
                t._rightColumn.update();
            });
            t._rightColumn.on('changeDialog', function(dialogId) {
                t._leftColumn.showDialog(dialogId);
            });
            t._rightColumn.on('setList', function(listId) {
                t._leftColumn.showList(listId);
            });

            (function poll(ts) {
                $.ajax({
                    url: 'http://im.' + Configs.hostName + '/int/controls/watchDog/',
                    dataType: 'jsonp',
                    data: {
                        userId: Configs.vkId,
                        timeout: 15,
                        ts: ts
                    },
                    success: function(data) {
                        poll(data.response.ts);
                        $.each(data.response.events, function(i, event) {
                            t.fireEvent(event);
                        });
                    }
                });
            })();

            t._leftColumn.showList(Configs.commonDialogsList);
        });
    },

    fireEvent: function(event) {
        var t = this;
        if (!event || !event.type) {
            console.log(['Bad Event: ', event]);
            return;
        }

        switch (event.type) {
            case 'inMessage':
            case 'outMessage':
                (function() {
                    var isViewer = (event.type == 'outMessage');
                    var message = Cleaner.longPollMessage(event.content, isViewer);
                    var dialog = Cleaner.longPollDialog(event.content, isViewer);
                    t._leftColumn.addMessage(new MessageModel(message));
                    t._leftColumn.addDialog(new DialogModel(dialog));

                    if (!message.isViewer) {
                        t._rightColumn.update();
                    }
                })();
                break;
            case 'read':
                (function() {
                    var message = Cleaner.longPollRead(event.content);
                    t._leftColumn.readMessage(message.id);
                    t._leftColumn.readDialog(message.dialogId);
                })();
                break;
            case 'online':
            case 'offline':
                (function() {
                    var online = Cleaner.longPollOnline(event.content);
                    if (online.isOnline) {
                        t._leftColumn.setOnline(online.userId);
                    } else {
                        t._leftColumn.setOffline(online.userId);
                    }
                })();
                break;
        }
    }
});

/**
 * Initialization
 */
$(document).ready(function() {
    var $window = $(window);
    var $main = $('#main');

    if (!$main.length) {
        return;
    }
    if (!Configs.vkId) {
        location.href = '/login/?' + encodeURIComponent('im/');
        return;
    } else {
        $.cookie('uid', Configs.vkId, {expires: 30});
    }

    var main = new Main({
        selector: '#main'
    });
    $window.on('scroll resize', function() {
        main.trigger('scroll');
    });
});

function makeDate(timestamp) {
    var monthNames = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
    var currentDate = new Date();
    var date = new Date(timestamp * 1000);
    var m = date.getMonth();
    var y = date.getFullYear() + '';
    var d = date.getDate() + '';
    var h = date.getHours() + '';
    var min = date.getMinutes() + '';
    var text = (h.length > 1 ? h : '0' + h) + ':' + (min.length > 1 ? min : '0' + min);
    if (currentDate.getDate() != d || currentDate.getMonth() != m || currentDate.getFullYear() != y) {
        text = d + ' ' + monthNames[m].toLowerCase() + ' ' + y + ' в ' + text;
    }
    return text;
}
