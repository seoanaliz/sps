var RightPanelWidget = Event.extend({
    init: function() {
        this.$leftPanel = $('#left-panel');
        this.$rightPanel = $('#right-panel');
        this.$rightPanelExpander = $('#right-panel-expander');
        this.$rightPanelBackground = $('#right-panel-background');
        this.$calendar = $('#calendar');

        this.initRightPanel();
        this.initCalendar();
        this.initExpander();
        this.initVkAvatar();
        this.initQueue();
    },

    initRightPanel: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;
        var $rightPanel = t.$rightPanel;

        $('#right-drop-down').dropdown({
            data: rightPanelData,
            type: 'radio',
            addClass: 'right',
            onchange: function(item) {
                $(this)
                    .data('selected', item.id)
                    .find('.caption').text(item.title);
                if (item.icon) {
                    var icon = $(this).find('.icon img');
                    if (!icon.length) {
                        icon = $('<img src="' + item.icon + '"/>').appendTo($(this).find('.icon'))
                    }
                    icon.attr('src', item.icon);
                }

                var targetFeedId = Elements.rightdd(),
                    cookieData = '',
                    sourceType = '',
                    targetType = '';

                // проставление типа источника
                cookieData = $.cookie('sourceTypes' + targetFeedId);
                sourceType = $('.left-panel .type-selector a[data-type="' + cookieData + '"]');
                if (sourceType.length == 0) {
                    sourceType = $leftPanel.find('.type-selector a[data-type="source"]');
                }
                $leftPanel.find('.type-selector a').removeClass('active');
                sourceType.addClass('active');

                // проставление типа ленты отправки
                cookieData = $.cookie('targetTypes' + targetFeedId);
                targetType = $rightPanel.find('.type-selector a[data-type="' + cookieData + '"]');
                if (targetType.length == 0) {
                    targetType = $rightPanel.find('.type-selector a[data-type="content"]');
                }
                $rightPanel.find('.type-selector a').removeClass('active');
                targetType.addClass('active');

                Events.fire('rightcolumn_dropdown_change');
            },
            oncreate: function() {
                $(this).find('.default').removeClass('default');
                Elements.rightdd($('#right-drop-down').dropdown('getMenu').find('.ui-dropdown-menu-item.active').data('id'));
            }
        });

        // Вкладки в правом меню
        $rightPanel.find('.type-selector a').click(function(e) {
            e.preventDefault();

            if (articlesLoading) {
                return;
            }

            $rightPanel.find('.type-selector a').removeClass('active');
            $(this).addClass('active');

            $.cookie('targetTypes' + Elements.rightdd(), Elements.rightType());
            t.loadQueue();
        });
    },

    initCalendar: function() {
        var t = this;
        var $calendar = t.$calendar;
        $calendar
            .datepicker()
            .keydown(function(e){
                if(!(e.keyCode >= 112 && e.keyCode <= 123 || e.keyCode < 32)) {
                    e.preventDefault();
                }
            })
            .change(function(){
                $(this).parent().find('.caption').toggleClass('default', !$(this).val().length);
                t.loadQueue();
            });

        $('.calendar .tip, #calendar-fix').click(function(){
            $calendar.focus();
        });

        // Приведение вида календаря из 22.12.2012 в 22 декабря
        var d = $calendar.val().split('.');
        var date = [d[1], d[0], d[2]].join('/');
        $calendar.datepicker('setDate', new Date(date)).trigger('change');

        // Кнопки вперед-назад в календаре
        $('.calendar .prev').click(function(){
            var date = $calendar.datepicker('getDate').getTime();
            $calendar.datepicker('setDate', new Date(date - TIME.DAY)).trigger('change');
        });
        $('.calendar .next').click(function(){
            var date = $calendar.datepicker('getDate').getTime();
            $calendar.datepicker('setDate', new Date(date + TIME.DAY)).trigger('change');
        });
    },

    initExpander: function() {
        var t = this;
        var $rightPanel = t.$rightPanel;
        var $rightPanelExpander = t.$rightPanelExpander;
        var $rightPanelBackground = t.$rightPanelBackground;
        $rightPanelExpander.click(function() {
            if ($rightPanel.hasClass('expanded')) {
                t.compact();
            } else {
                t.expand();
            }
        });
        $rightPanelBackground.click(function() {
            t.compact();
        });
    },

    expand: function() {
        var t = this;
        var $rightPanel = t.$rightPanel;
        var $rightPanelBackground = t.$rightPanelBackground;
        $rightPanel.addClass('expanded');
        $rightPanel.find('.images-ready').imageComposition();
        $rightPanelBackground.show();
        $('body').width($('body').width()).css('overflow-y', 'hidden');
    },

    compact: function() {
        var t = this;
        var $rightPanel = t.$rightPanel;
        var $rightPanelBackground = t.$rightPanelBackground;
        $rightPanel.removeClass('expanded');
        $rightPanelBackground.hide();
        $('body').width('auto').css('overflow-y', 'scroll');
    },

    initVkAvatar: function() {
        VK.init({
            apiId: vk_appId,
            nameTransportPath: '/xd_receiver.htm'
        });
        getInitData();

        function getInitData() {
            var code;
            code = 'return {';
            code += 'me: API.getProfiles({uids: API.getVariable({key: 1280}), fields: "photo"})[0]';
            code += '};';
            VK.Api.call('execute', {'code': code}, onGetInitData);
        }
        function onGetInitData(data) {
            var r;
            if (data.response) {
                r = data.response;
                if (r.me) {
                    var $userInfo = $('.user-info');
                    $('.user-name a', $userInfo).text(r.me.first_name + ' ' + r.me.last_name);
                    $('.user-name a', $userInfo).attr('href', 'http://vk.com/id' + r.me.uid);
                    $('.user-photo img', $userInfo).attr('src', r.me.photo);
                }
            }
        }
    },

    getQueueWidget: function() {
        return this.queueWidget || (this.queueWidget = new QueueWidget());
    },

    initQueue: function() {
        this.getQueueWidget().initQueue();
    },

    loadQueue: function() {
        this.getQueueWidget().load();
    }
});