var pattern = /\b(https?|ftp):\/\/([\-A-Z0-9.]+)(\/[\-A-Z0-9+&@#\/%=~_|!:,.;]*)?(\?[A-Z0-9+&@#\/%=~_|!:,.;]*)?/im;
var easydateParams = {
    date_parse: function(date) {
        if (!date) return;
        var d = date.split('.');
        return Date.parse([d[1], d[0], d[2]].join('/'));
    },
    uneasy_format: function(date) {
        return date.toLocaleDateString();
    }
};

var UserGroupModel = Model.extend({
    init: function() {
        this.defData('id', null);
        this.defData('name', '...');
        this.defData('isSelected', false);
        this._super.apply(this, arguments);
    },

    id: function(id) {
        if (arguments.length) id = intval(id);
        return this.data('id', id);
    },
    name: function(name) {
        if (arguments.length) name = name + '';
        return this.data('name', name);
    },
    isSelected: function(isSelected) {
        if (arguments.length) isSelected = !!isSelected;
        return this.data('isSelected', isSelected);
    }
});
var UserGroupCollection = Collection.extend({
    modelClass: UserGroupModel
});
var userGroupCollection = new UserGroupCollection();

$(document).ready(function(){
    $.mask.definitions['2']='[012]';
    $.mask.definitions['3']='[0123]';
    $.mask.definitions['5']='[012345]';
    $.datepick.setDefaults($.datepick.regional['ru']);
    $.datepicker.setDefaults({
        dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dayNamesShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        monthNames: ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        firstDay: 1,
        showAnim: '',
        dateFormat: 'd MM yy',
        altField: '#calendar-fix',
        altFormat: 'd MM'
    });

    var $leftPanel = $('.left-panel');
    var $multiSelect = $("#source-select");
    var $calendar = $('#calendar');

    // Календарь
    $calendar
        .datepicker()
        .keydown(function(e){
            if(!(e.keyCode >= 112 && e.keyCode <= 123 || e.keyCode < 32)) {
                e.preventDefault();
            }
        })
        .change(function(){
            $(this).parent().find(".caption").toggleClass("default", !$(this).val().length);
            Events.fire('calendar_change');
        });

    $(".calendar .tip, #calendar-fix").click(function(){
        $calendar.focus();
    });

    // Приведение вида календаря из 22.12.2012 в 22 декабря
    (function() {
        var d = $("#calendar").val().split('.');
        var i = d[1];
        d[1] = d[0];
        d[0] = i;
        var date = d.join('/');
        $("#calendar").datepicker('setDate', new Date(date)).trigger('change');
    })();

    // Кнопки вперед-назад в календаре
    (function() {
        $(".calendar .prev").click(function(){
            var date = $calendar.datepicker('getDate').getTime();
            $calendar.datepicker('setDate', new Date(date - TIME.DAY)).trigger('change');
        });
        $(".calendar .next").click(function(){
            var date = $calendar.datepicker('getDate').getTime();
            $calendar.datepicker('setDate', new Date(date + TIME.DAY)).trigger('change');
        });
    })();

    // Left menu multiselect
    $multiSelect.multiselect({
        minWidth: 250,
        height: 250,
        classes: $multiSelect.data('classes'),
        checkAllText: 'Выделить все',
        uncheckAllText: 'Сбросить',
        noneSelectedText: '<span class="gray">Источник не выбран</span>',
        selectedText: function(i) {
            return '<span class="counter">' + i + '</span> '
                + Lang.declOfNum(i, ['источник выбран', 'источника выбрано', 'источников выбрано']);
        },
        checkAll: function(){
            Events.fire('leftcolumn_dropdown_change');
        },
        uncheckAll: function(){
            Events.fire('leftcolumn_dropdown_change');
        }
    });
    $multiSelect.bind("multiselectclick", function(event, ui){
        Events.fire('leftcolumn_dropdown_change');
    });

    // right dropdown
    $("#right-drop-down").dropdown({
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
                sourceType = $('.left-panel .type-selector a[data-type="source"]');
            }
            $(".left-panel .type-selector a").removeClass('active');
            sourceType.addClass('active');

            // проставление типа ленты отправки
            cookieData = $.cookie('targetTypes' + targetFeedId);
            targetType = $('.right-panel .type-selector a[data-type="' + cookieData + '"]');
            if (targetType.length == 0) {
                targetType = $('.right-panel .type-selector a[data-type="content"]');
            }
            $(".right-panel .type-selector a").removeClass('active');
            targetType.addClass('active');

            Events.fire('rightcolumn_dropdown_change');
        },
        oncreate: function() {
            $(this).find('.default').removeClass('default');
            Elements.rightdd($("#right-drop-down").dropdown('getMenu').find('.ui-dropdown-menu-item.active').data('id'));
        }
    });

    $('.left-panel .drop-down').change(function() {
        Events.fire('leftcolumn_dropdown_change');
    });

    $('.wall-title .filter a').dropdown({
        width: 'auto',
        addClass: 'wall-title-menu',
        position: 'right',
        data: [
            {title: 'новые записи', type : 'new'},
            {title: 'старые записи', type : 'old'},
            {title: 'лучшие записи', type : 'best'}
        ],
        oncreate: function() {},
        onopen: function() {},
        onclose: function() {},
        onchange: function(item) {
            $('.wall-title a').text(item.title).data('type', item.type);
            Events.fire('leftcolumn_sort_type_change');
        }
    });

    // Вкладки Источники Мои публикации Авторские Альбомы Topface в левом меню
    $leftPanel.find('.type-selector').delegate('.sourceType', 'click', function() {
        if (articlesLoading) {
            return;
        }

        $leftPanel.find(".type-selector .sourceType").removeClass('active');
        $(this).addClass('active');

        if ($(this).data('type') == 'authors-list') {
            $('body').addClass('editor-mode');
            $(window).data('disable-load-more', true);
            updateAuthorListPage();
        } else {
            $('body').removeClass('editor-mode');
            $(window).data('disable-load-more', false);
            Events.fire('rightcolumn_dropdown_change');
        }
    });

    // Список авторов
    function updateAuthorListPage(method) {
        Control.fire(method || 'authors_get', {
            targetFeedId: Elements.rightdd()
        }).success(function(data) {
            var $container = $('#wall');
            $container.html(data);

            var $navigation = $container.find('.authors-types');
            $navigation.delegate('.tab', 'click', function() {
                $navigation.find('.tab.selected').removeClass('selected');
                $(this).addClass('selected');
                updateAuthorListPage('authors_get');
            });

            var $input = $container.find('.author-link');
            $input.placeholder();
            $input.keyup(function(e) {
                if (e.keyCode == KEY.ENTER) {
                    $input.blur();
                    var authorId = $input.val().replace(new RegExp('(/)*(http:)?(vk.com)?(id[0-9]+)?', 'g'), '$4');
                    var confirmBox = new Box({
                        id: 'addAuthor' + authorId,
                        title: 'Добавление автора',
                        html: tmpl(BOX_LOADING, {height: 100}),
                        buttons: [
                            {label: 'Добавить автора', onclick: addAuthor},
                            {label: 'Отменить', isWhite: true}
                        ],
                        onshow: function($box) {
                            var box = this;

                            VK.Api.call('users.get', {uids: authorId, fields: 'photo_medium_rec', name_case: 'acc'}, function(dataVK) {
                                if (!dataVK.response) {
                                    return box.setHTML('Пользователь не найден');
                                }
                                var user = dataVK.response[0];
                                var clearUser = {
                                    id: user.uid,
                                    name: user.first_name + ' ' + user.last_name,
                                    photo: user.photo_medium_rec
                                };
                                authorId = clearUser.id;
                                var text = tmpl(BOX_ADD_AUTHOR, {user: clearUser});
                                box.setHTML(tmpl(BOX_AUTHOR, {text: text, user: clearUser}));
                            });
                        }
                    });
                    confirmBox.show();
                }
                function addAuthor() {
                    var box = this;
                    box.setHTML(tmpl(BOX_LOADING, {height: 100}));
                    box.setButtons([{label: 'Закрыть'}]);
                    Control.fire('author_add', {
                        authorId: authorId,
                        targetFeedId: Elements.rightdd()
                    }).success(function(data) {
                        box.remove();
                        updateAuthorListPage();
                    });
                }
            });

            if ($container.data('initedList')) {
                return;
            }
            $container.data('initedList', true);
            $container.delegate('.add-to-list', 'click', function() {
                var $target = $(this);
                var $author = $target.closest('.author');
                (function updateDropdown() {
                    var authorId = $author.data('id');

                    var authorGroupIds = $author.data('group-ids') ? ($author.data('group-ids') + '').split(',') : [];
                    var authorGroups = [];
                    $.each(userGroupCollection.get(), function(id, userGroupModel) {
                        if ($.inArray(userGroupModel.id() + '', authorGroupIds) !== -1) {
                            userGroupModel.isSelected(true);
                        } else {
                            userGroupModel.isSelected(false);
                        }
                        authorGroups.push({
                            id: userGroupModel.id(),
                            title: userGroupModel.name(),
                            isActive: userGroupModel.isSelected()
                        });
                    });
                    $target.dropdown({
                        isShow: true,
                        position: 'right',
                        width: 'auto',
                        type: 'checkbox',
                        addClass: 'ui-dropdown-add-to-list',
                        data: $.merge(authorGroups, [
                            {id: 'add_list', title: 'Создать список'}
                        ]),
                        onopen: function() {
                            $target.addClass('active');
                        },
                        onclose: function() {
                            $target.removeClass('active');
                        },
                        onchange: function(item) {
                            $(this).dropdown('open');
                        },
                        onselect: function(item) {
                            if (item.id == 'add_list') {
                                var $item = $(this).dropdown('getItem', 'add_list');
                                var $menu = $(this).dropdown('getMenu');
                                var $input = $menu.find('input');
                                $item.removeClass('active');
                                if ($input.length) {
                                    $input.focus();
                                } else {
                                    $item.before('<div class="wrap"><input type="text" placeholder="Название списка..." /></div>');
                                    $input = $menu.find('input');
                                    $input.focus();
                                    $input.keydown(function(e) {
                                        if (e.keyCode == KEY.ENTER) {
                                            var newUserGroupModel = new UserGroupModel();
                                            newUserGroupModel.name($input.val());
                                            Control.fire('add_list', {
                                                name: newUserGroupModel.name(),
                                                targetFeedId: Elements.rightdd()
                                            }).success(function(data) {
                                                newUserGroupModel.id(data.userGroup.id);
                                                userGroupCollection.add(newUserGroupModel.id(), newUserGroupModel);
                                                updateDropdown();
                                            });
                                        }
                                    });
                                    $(this).dropdown('refreshPosition');
                                }
                            } else {
                                authorGroupIds.push(item.id + '');
                                $author.data('group-ids', authorGroupIds.join(','));
                                Control.fire('add_to_list', {
                                    userId: authorId,
                                    listId: item.id
                                });
                            }
                        },
                        onunselect: function(item) {
                            var index = $.inArray(item.id + '', authorGroupIds);
                            if (index !== -1) {
                                authorGroupIds.splice(index, 1);
                            }
                            $author.data('group-ids', authorGroupIds.join(','));
                            Control.fire('remove_from_list', {
                                userId: authorId,
                                listId: item.id
                            });
                        }
                    });
                })();
            });
            $container.delegate('.delete', 'click', function() {
                var $author = $(this).closest('.author');
                var authorId = $author.data('id');
                var confirmDeleteBox = new Box({
                    id: 'confirmDeleteBox' + authorId,
                    title: 'Удаление автора',
                    html: tmpl(BOX_LOADING, {height: 100}),
                    buttons: [
                        {label: 'Удалить', onclick: function() {
                            Control.fire('author_remove', {
                                authorId: authorId,
                                targetFeedId: Elements.rightdd()
                            }).success(function(data) {
                                $author.remove();
                                confirmDeleteBox.hide();
                            });
                        }},
                        {label: 'Отменить', isWhite: true}
                    ],
                    onshow: function($box) {
                        var box = this;

                        VK.Api.call('users.get', {uids: authorId, fields: 'photo_medium_rec', name_case: 'acc'}, function(dataVK) {
                            if (!dataVK.response) {
                                return box.setHTML('Пользователь не найден');
                            }
                            var user = dataVK.response[0];
                            var clearUser = {
                                id: user.uid,
                                name: user.first_name + ' ' + user.last_name,
                                photo: user.photo_medium_rec
                            };
                            authorId = clearUser.id;
                            var text = tmpl(BOX_DELETE_AUTHOR, {user: clearUser});
                            box.setHTML(tmpl(BOX_AUTHOR, {text: text, user: clearUser}));
                        });
                    }
                }).show();
            });
        });
    }

    // Подвкладки Авторов: Новые Одобренные Отклоненные
    $leftPanel.find('.authors-tabs').delegate('.tab', 'click', function() {
        if (articlesLoading) {
            return;
        }

        var $tab = $(this);
        $leftPanel.find('.authors-tabs .tab').removeClass('selected');
        $tab.addClass('selected');

        wallPage = 0;
        loadArticles(true);
    });

    // Кастомные подвкладки
    $leftPanel.find('.user-groups-tabs').delegate('.tab', 'click', function() {
        if (articlesLoading) {
            return;
        }

        var $tab = $(this);
        $leftPanel.find('.user-groups-tabs .tab').removeClass('selected');
        $tab.addClass('selected');

        loadArticles(true);
    });

    // Вкладки в правом меню
    $(".right-panel .type-selector a").click(function(e){
        e.preventDefault();

        if (articlesLoading) {
            return;
        }

        $(".right-panel .type-selector a").removeClass('active');
        $(this).addClass('active');

        Events.fire('rightcolumn_type_change');
    });

    // Wall init
    $("#wall")
        .delegate(".post > .delete", "click", function(){
            var elem = $(this).closest(".post"),
                pid = elem.data("id"),
                gid = elem.data('group');
            Events.fire('leftcolumn_deletepost', pid, function(state){
                if (state) {
                    var deleteMessageId = 'deleted-post-' + pid;
                    var $deleteMessage = $('#' + deleteMessageId);
                    if ($deleteMessage.length) {
                        // если уже удаляли пост, то сообщение об удалении уже в DOMе
                        $deleteMessage.show();
                    } else {
                        // иначе добавляем
                        elem.before($(
                            '<div id="' + deleteMessageId + '" class="bb post deleted-post" data-group="' + gid + '" data-id="' + pid + '">' +
                                'Пост удален. <a class="recover">Восстановить</a><br/>' +
                                '<span class="button ignore">Не показывать новости сообщества</span>' +
                            '</div>'
                        ));
                    }

                    elem.hide();
                }
            });
        })
        .delegate('.post .ignore', 'click', function() {
            var elem = $(this).closest(".post"),
                gid = elem.data("group");
            var $menu = $multiSelect.multiselect('widget');
            $menu.find('[value=' + gid + ']:checkbox').each(function() {
                this.click();
            });
        })
        .delegate('.post .recover', 'click', function() {
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_recoverpost', pid, function(state){
                if(state) {
                    elem.hide().next().show();
                }
            });
        })
        .delegate('.show-all-postponed', 'click', function() {
            var $target = $(this);
            if ($target.data('block')) {
                var $posts = $target.data('block');
                if ($posts.first().is(':visible')) {
                    $target.html($target.data('def-html'));
                    $posts.hide();
                } else {
                    $target.html('Скрыть записи на рассмотрении');
                    $posts.show();
                }
            } else {
                $target.data('def-html', $target.html());

                wallPage = 0;
                Elements.getWallLoader().show();
                Control.fire('get_articles', {
                    page: wallPage,
                    sortType: Elements.getSortType(),
                    type: Elements.leftType(),
                    targetFeedId: Elements.rightdd(),
                    userGroupId: Elements.getUserGroupId(),
                    articlesOnly: true,
                    mode: 'deferred'
                }).success(function(html) {
                    if (html) {
                        var $posts = $(html);
                        $target.data('block', $posts);
                        $target.after($posts);
                        $target.html('Скрыть записи на рассмотрении');
                        Elements.initImages($posts);
                        Elements.initLinks($posts);
                    } else {
                        $target.remove();
                    }
                    Elements.getWallLoader().hide();
                });
            }
        });

    $("#queue")
        // Удаление постов
        .delegate(".delete", "click", function(){
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('rightcolumn_deletepost', pid, function(state){
                if(state) {
                    elem.remove();
                }
            });
        })
        // Смена даты
        .delegate('.time', 'click', function() {
            var $time = $(this);
            var $post = $time.closest('.slot-header');
            var $input = $time.data('time-edit');

            if (!$input) {
                $input = $('<input />')
                    .attr('type', 'text')
                    .attr('class', 'time-edit')
                    .width($time.width() + 2)
                    .val($time.text())
                    .mask('29:59')
                    .appendTo($post);
                $time.data('time-edit', $input);
            } else {
                $input.show();
            }
            $input.focus().select();
        })
        .delegate('.time-edit', 'blur keydown', function(e) {
            var $input = $(this);

            if (e.type == 'keydown' && e.keyCode != KEY.ENTER) {
                return;
            }
            if (e.type == 'focusout' && !e.originalEvent) {
                return;
            }

            var $post = $input.closest('.slot');
            var $time = $post.find('.time');
            var gridLineId = $post.data('grid-id');
            var gridLineItemId = $post.data('grid-item-id');

            var time = ($input.val() == '__:__') ? '' : $input.val().split('_').join('0');
            var qid = $post.find('.post').data('queue-id');
            $input.blur().hide().val(time);

            if (time && time != $time.text()) {
                $time.text(time);
                if (!$post.hasClass('new')) {
                    // Редактирование времени ячейки для текущего дня
                    Events.fire('rightcolumn_time_edit', gridLineId, gridLineItemId, time, qid, function(state){
                        if (state) {}
                    });
                }
            } else if (!time) {
                if ($post.hasClass('new')) {
                    $post.animate({height: 0}, 200, function() {$(this).remove()});
                }
            }
        })
        .delegate('.time-of-removal', 'click', function() {
            var $time = $(this);
            var $post = $time.closest('.slot-header');
            var $input = $time.data('time-of-removal-edit');

            if (!$input) {
                $input = $('<input />')
                    .attr('type', 'text')
                    .attr('class', 'time-of-removal-edit')
                    .width($time.width() + 2)
                    .mask('29:59')
                    .appendTo($post);
                $time.data('time-of-removal-edit', $input);
            } else {
                $input.show();
            }
            $input.focus().select();
        })
        .delegate('.time-of-removal-edit', 'blur keydown', function(e) {
            var $input = $(this);

            if (e.type == 'keydown' && e.keyCode != KEY.ENTER) {
                return;
            }
            if (e.type == 'focusout' && !e.originalEvent) {
                return;
            }

            var $post = $input.closest('.slot');
            var gridLineId = $post.data('grid-id');
            var gridLineItemId = $post.data('grid-item-id');

            var time = ($input.val() == '__:__') ? '' : $input.val().split('_').join('0');
            var qid = $post.find('.post').data('queue-id');
            $input.blur().hide().val(time);

            if (time) {
                Events.fire('rightcolumn_removal_time_edit', gridLineId, gridLineItemId, time, qid, function(state){
                    if (state) {}
                });
            }
        })
        .delegate('.datepicker', 'click', function() {
            var $target = $(this);
            var $header = $target.parent();

            if (!$header.data('datepicker')) {
                var $datepicker = $('<input type="text" />');
                var $post = $target.closest('.slot');
                var $time = $post.find('.time');
                var gridLineId = $post.data('grid-id');
                var startDate = $post.data('start-date');
                var endDate = $post.data('end-date');
                var defStartDate = $post.data('start-date');
                var defEndDate = $post.data('end-date');
                var time = $time.text();

                $header.data('datepicker', $datepicker);
                $target.after($datepicker);
                $target.remove();
                $datepicker.datepick({
                    rangeSelect: true,
                    showTrigger: $target,
                    showAnim: 'fadeIn',
                    showSpeed: 'fast',
                    monthsToShow: 2,
                    minDate: 0,
                    renderer: $.extend($.datepick.defaultRenderer, {
                        picker: $.datepick.defaultRenderer.picker.replace(/\{link:today\}/, '')
                    }),
                    onSelect: function(dates) {
                        $post.data('start-date', $.datepick.formatDate(dates[0]));
                        $post.data('end-date', $.datepick.formatDate(dates[1]));
                        startDate = $post.data('start-date');
                        endDate = $post.data('end-date');
                    },
                    onShow: function() {
                        $header.find('span.datepicker').addClass('active');
                        $('#queue').css('overflow', 'hidden');
                    },
                    onClose: function() {
                        time = $time.text();
                        $header.find('span.datepicker').removeClass('active');
                        $('#queue').css('overflow', 'auto');
                        if ($post.hasClass('new')) {
                            // Добавление ячейки
                            Events.fire('rightcolumn_save_slot', gridLineId, time, startDate, endDate, function(state){
                                if (state) {}
                            });
                        } else {
                            // Редактироваиние ячейки
                            if (defStartDate != startDate || defEndDate != endDate) {
                                Events.fire('rightcolumn_save_slot', gridLineId, time, startDate, endDate, function(state) {
                                    if (state) {}
                                });
                            }
                        }
                    }
                });
                $datepicker.val(startDate + ' - ' + endDate).focus();
            }
        });

    $('.queue-footer .add-button').click(function() {
        $("#queue").scrollTo(0);
        var $newPost = $(
            '<div class="new slot empty">' +
                '<div class="slot-header">' +
                    '<span class="time">__:__</span>' +
                    '<span class="datepicker"></span>' +
                '</div>' +
            '</div>'
        ).prependTo('#queue').animate({height: 105}, 200);
        $newPost.find('.time').click();
    });

    // Очистка текста
    $leftPanel.delegate(".clear-text", "click", function(){
        var $post = $(this).closest(".post");
        var postId = $post.data("id");

        if (confirm("Вы уверены, что хотите очистить текст записи?") ) {
            Events.fire('leftcolumn_clear_post_text', postId, function(state){
                if (state) {
                    $post.find('div.shortcut').html('');
                    $post.find('div.cut').html('');
                    $post.find('a.show-cut').remove();
                }
            });
        }
    });

    // Отклонение или одобрения авторских постов
    $leftPanel.delegate('.moderation .button.approve', 'click', function() {
        var $post = $(this).closest('.post');
        var postId = $post.data('id');
        Events.fire('leftcolumn_approve_post', postId, function() {
            $post.slideUp(200);
        });
    });
    $leftPanel.delegate('.moderation .button.reject', 'click', function() {
        var $post = $(this).closest('.post');
        var postId = $post.data('id');
        var $newComment = $post.find('.new-comment');
        var $moderation = $post.find('.moderation');
        $moderation.hide();
        $newComment.show();
        $newComment.find('textarea').focus();
    });

    // Автоподгрузка записей
    (function() {
        var $window = $(window);
        $window.scroll(function() {
            if (!$window.data('disable-load-more') && $window.scrollTop() > ($(document).height() - $window.height() * 2)) {
                Events.fire('wall_load_more', function(state) {});
            }
        });
    })();

    // Добавление записи в борд
    (function(){
        var $form = $(".newpost"),
            $input = $("textarea", $form),
            $tip = $(".tip", $form);

        var $linkInfo = $('.link-info', $form),
            $linkDescription = $('.link-description', $linkInfo),
            $linkStatus = $('.link-status', $linkInfo),
            foundLink, foundDomain;

        $tip.click(function(){$input.focus();});
        $form.click(function(e){ e.stopPropagation(); });
        $input
            .focus(function(){
                $form.removeClass("collapsed");
                $(window).bind("click", stop);
            })
            .bind('paste', function() {
                setTimeout(function() {
                    parseUrl($input.val());
                }, 10);
            })
            .autoResize()
            .keyup(function (e) {
                if (e.ctrlKey && e.keyCode == KEY.ENTER) {
                    $form.find('.save').click();
                }
            }).keyup()
        ;

        var parseUrl = function(txt){
            var matches = txt.match(pattern);

            // если приаттачили ссылку
            if (matches && matches[0] && matches[1] && !foundLink) {
                foundLink   = matches[0];
                foundDomain = matches[2];

                Events.fire("post_describe_link", foundLink, function(result) {
                    if (result) {
                        $linkDescription.empty();
                        $linkStatus.empty();

                        var $descriptionLayout = $('<div></div>',{'class':'post_describe_layout'});
                        $linkDescription.append($descriptionLayout);

                        // отрисовываем ссылку
                        if (result.img) {
                            var $imgBlock = $('<div></div>',{'class':'post_describe_image','title':'Редактировать картинку'}).css(
                                {
                                    'background-image' : 'url('+result.img+')'
                                }
                            );

                            $linkDescription.prepend($imgBlock);
                        }
                        if (result.title) {
                            var $a = $('<a />', {
                                href: foundLink,
                                target: '_blank',
                                html: '<span>'+result.title+'</span>',
                                title:'Редактировать заголовок'
                            });
                            var $h = $('<div></div>',{'class':'post_describe_header'});
                            $h.append($a);
                            $descriptionLayout.append($h);
                        }
                        if (result.description) {
                            var $p = $('<p />', {
                                html: '<span>'+result.description+'</span>',
                                title:'Редактировать описание'
                            });
                            $descriptionLayout.append($p);
                        }

                        editPostDescribeLink.load($h,$p,$imgBlock,result.imgOriginal);

                        var $span = $('<span />', { text: 'Ссылка: ' });
                        $span.append($('<a />', { href: foundLink, target: '_blank', text: foundDomain }));

                        var $deleteLink = $('<a />', { 'class': 'delete-link', text: 'удалить' }).click(function() {
                            // убираем аттач ссылки
                            deleteLink();
                        });
                        var $reloadLink = $('<a />', { 'class': 'reload-link', text: 'обновить', 'css' : {'display': 'none'} }).click(function() {
                            link = foundLink;
                            deleteLink();
                            parseUrl(link);
                        });
                        $span.append($deleteLink);
                        $span.append($reloadLink);

                        $linkStatus.html($span);

                        $linkInfo.show();
                    }
                });
            }
        };

        // Редактирование ссылки
        var editPostDescribeLink = {
            load: function ($header,$description,$image,$imageSrc) {
                this.header = $header;
                this.description = $description;
                this.image = $image;
                this.imageSrc = $imageSrc;
                this.renderEditor();
            },
            renderEditor: function() {
                var $editField = $('<input />',{type:'text',id:'post_header'});
                var $editArea = $('<textarea />',{id: 'post_description'});
                if (this.header) {
                    this.header.append($editField.val(this.header.text()));
                }
                if (this.description) {
                    this.description.append($editArea.val(this.description.text()));
                }

                this.bindEvts();
            },
            bindEvts: function() {
                var t = this;
                if (this.header) {
                    this.header.click(function() {
                        t.edit(t.header);
                        return false;
                    });
                }
                if (this.description) {
                    this.description.click(function() {
                        t.edit(t.description);
                        return false;
                    });
                }
                if (this.image) {
                    this.image.click(function() {
                        t.editImage(t.description);
                        return false;
                    });
                }
            },
            editImage: function() {
                this.renderEditImagePopup();
            },
            renderEditImagePopup: function() {
                var $popup = $('<div></div>',{
                    'class': 'editImagePopup',
                    'html': '<h2>Редактировать изображение</h2>'+
                        '<table><tr><td><img src="'+this.imageSrc+'" id="originalImage" /></td>'+
                        '<td><div class="previewContainer">'+
                        '<div class="previewLayout"><img id="preview" src="'+this.imageSrc+'" /></div>'+
                        '<div class="button save">Сохранить</div>'+
                        '<div id="attach-image-file" class="buttons attach-file">'+
                        '</div>'+
                        '</div></td></tr></table><b class="close"></b>'
                }),
                    t = this;
                $('body').append($popup);
                $('<div class="substrate"></div>').appendTo('body');
                $('#originalImage').load(function(){
                    $popup.css({
                        left: $('body').width()/2 - $popup.width()/2,
                        top: $('.link-info').position().top
                    });
                    $('.substrate').css({
                        height: $(document).height()
                    });
                });

                $popup.find('.save').click(function() {
                    t.post();
                });


                this.closeImagePopup($popup);
                this.crop();
                this.upload();
            },
            closeImagePopup: function($popup) {
                $('.substrate,.editImagePopup .close').click(function() {
                    $('.substrate').remove();
                    $popup.remove();
                });
            },
            crop: function() {
                var t = this;
                this.originalImage = $('#originalImage');
                this.previewImage = $('#preview');
                this.originalImage.load(function (){
                    t.Jcrop = $.Jcrop($(this), {
                        onChange: t.showPreview,
                        onSelect: t.showPreview,
                        aspectRatio : 2.06,
                        minSize: [130,63],
                        setSelect: [0,0,130,63]
                    });
                });
            },
            upload: function() {
                var t = this;
                try {
                    new qq.FileUploader({
                        debug: true,
                        element: $('#attach-image-file')[0],
                        action: root + 'int/controls/image-upload/',
                        template: ' <div class="qq-uploader">' +
                            '<ul class="qq-upload-list"></ul>' +
                            //'<a href="#" class="button qq-upload-button">Загрузить картинку</a>' +
                            '</div>',
                        onComplete: function(id, fileName, responseJSON) {
                            popupNotice('Не реализовано');
                        }
                    });
                } catch (e) {}
            },
            showPreview: function (coords,t) {
                var rx = $('.previewLayout').width() / coords.w;
                var ry = $('.previewLayout').height() / coords.h;

                $('#preview').css({
                    width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                    height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                    marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                    marginTop: '-' + Math.round(ry * coords.y) + 'px'
                });
                editPostDescribeLink.coords = coords;
            },
            edit: function($elem) {
                var t = this;
                $elem.find('span').hide();
                $elem.find('input,textarea')
                    .css({display: 'block'})
                    .trigger('focus')
                    .unbind('blur')
                    .bind('blur',function(){
                        var $this = $(this);
                        $elem.find('span').text($this.val()).show();
                        $this.hide();
                        t.post();
                    });
            },
            post: function() {
                var t = this,
                    data = {
                        header: $('#post_header').val(),
                        description: $('#post_description').val(),
                        coords: t.coords,
                        link: $('.post_describe_header').find('a').attr('href')
                    };

                $('.editImagePopup .close').click();

                Events.fire('post_link_data', data, function(state){

                });
            }
        };

        var clearForm = function(){
            $input.data("id", 0).val('');
            $('.qq-upload-list').html('');
            deleteLink();
        };

        var stop = function(){
            $(window).unbind("click", stop);

            if(!$input.val().length && !$(".qq-upload-list li").length && !$linkInfo.is(":visible")) {
                $input.data("id", 0);
                $form.addClass("collapsed");
                deleteLink();
            }
        };

        var deleteLink = function(){
            $linkDescription.empty();
            $linkStatus.empty();
            $linkInfo.hide();
            foundLink = false;
            foundDomain = false;
        };

        $form.delegate(".save", "click", function(e){
            var link = $linkStatus.find('a').attr('href');
            var photos = [];
            $('.newpost .qq-upload-success').each(function(){
                var photo = {};
                photo.filename = $(this).find('input:hidden').val();
                photo.title = $(this).find('textarea').val();
                photos.push(photo);
            });
            if (!($.trim($input.val() || photos.length || link))) {
                return $input.focus();
            } else {
                $form.addClass("spinner");
                Events.fire("post",
                    $input.val(),
                    photos,
                    link,
                    $input.data("id"),
                    function(state){
                        if(state) {
                            clearForm();
                            stop();
                        }
                        $form.removeClass("spinner");
                        $input.blur();
                    }
                );
            }
        });
        $form.delegate(".cancel", "click" ,function(e){
            clearForm();
            $input.val('').blur();
            $form.addClass('collapsed');
            e.preventDefault();
        });
        $form.delegate(".image-attach", "click" ,function(e){
            $input.focus();
            $('.newpost .qq-upload-button').trigger('focus');
        });

        // Быстрое редактирование поста в левой колонке
        $leftPanel.delegate(".post .content .shortcut", "click", function(){
            var $post = $(this).closest(".post"),
                $content = $post.find('> .content'),
                postId = $post.data("id");

            if ($post.editing) return;

            Events.fire('load_post_edit', postId, function(state, data){
                if (state && data) {
                    new SimpleEditPost(postId, $post, $content, data);
                }
            });
        });

        // Редактирование поста в левом меню
        $leftPanel.delegate(".post .edit", "click", function(){
            var $post = $(this).closest(".post"),
                $content = $post.find('> .content'),
                $buttonPanel = $post.find('> .bottom.d-hide'),
                postId = $post.data("id");

            if ($post.editing) return;

            Events.fire('load_post_edit', postId, function(state, data){
                if (state && data) {

                    (function($post, $el, data) {

                        function setSelectionRange(input, selectionStart, selectionEnd) {
                            if (input.setSelectionRange) {
                                input.focus();
                                input.setSelectionRange(selectionStart, selectionEnd);
                            }
                            else if (input.createTextRange) {
                                var range = input.createTextRange();
                                range.collapse(true);
                                range.moveEnd('character', selectionEnd);
                                range.moveStart('character', selectionStart);
                                range.select();
                            }
                        }
                        function setCaretToPos (input, pos) {
                            setSelectionRange(input, pos, pos);
                        }

                        function parseUrl(txt, callback) {
                            var matches = txt.match(pattern);
                            if (matches && matches[0] && matches[1]) {
                                var foundLink = matches[0];
                                var foundDomain = matches[2];
                                if ($.isFunction(callback)) callback(foundLink, foundDomain);
                            }
                        }
                        function addLink(link, domain, el) {
                            Events.fire("post_describe_link", link, function(data) {
                                var savePost = function(d) {
                                    d = d || {};
                                    Events.fire('post_link_data', [
                                        {
                                            link: d.link || link,
                                            header: d.title || data.title,
                                            coords: d.coords || data.coords,
                                            description: d.description || data.description
                                        }, function(data) {
                                            if (data) {
                                                if (data.img) {
                                                    el.find('.link-img').css('background-image', 'url(' + data.img + ')');
                                                }
                                                popupSuccess('Изменения сохранены');
                                            }
                                        }
                                    ]);
                                };
                                var $del = $('<div/>', {class: 'delete-attach'}).click(function() {
                                    $links.html('');
                                });
                                el.html(linkTplFull);
                                el.find('a').attr('href', link).html(domain);
                                el.find('.link-status-content').append($del);

                                if (data.img) {
                                    el.find('.link-img')
                                        .css('background-image', 'url(' + data.img + ')')
                                        .click(function() {
                                            var originalImage = new Image();
                                            originalImage.src = data.imgOriginal;
                                            originalImage.onload = function () {
                                                var linkImageCoords = {};
                                                var closePopup = function() {
                                                    $popup.remove();
                                                    $bg.remove();
                                                };
                                                var showPreview = function(coords)
                                                {
                                                    linkImageCoords = coords;
                                                    var $preview = $popup.find('.preview');
                                                    var rx = $preview.width() / coords.w;
                                                    var ry = $preview.height() / coords.h;

                                                    $preview.find('> img').css({
                                                        width: Math.round(rx * $('.jcrop-holder').width()) + 'px',
                                                        height: Math.round(ry * $('.jcrop-holder').height()) + 'px',
                                                        marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                                                        marginTop: '-' + Math.round(ry * coords.y) + 'px'
                                                    });
                                                };
                                                var $bg = $('<div/>', {class: 'popup-bg'}).appendTo('body');
                                                var $popup = $('<div/>', {
                                                        'class': 'popup-image-edit',
                                                        'html': '<div class="title">Редактировать изображение</div>'+
                                                            '<div class="close"></div>' +
                                                            '<div class="left-column">' +
                                                                '<div class="original"><img src="'+originalImage.src+'" /></div>' +
                                                            '</div>' +
                                                            '<div class="right-column">' +
                                                                '<div class="preview"><img src="'+originalImage.src+'" /></div>'+
                                                                '<div class="button save">Сохранить</div>'+
                                                            '</div>'
                                                    })
                                                    .appendTo('body');

                                                $bg.click(closePopup);
                                                $popup.css({'margin-left': -$popup.width()/2});
                                                $popup.find('.close').click(closePopup);
                                                $popup.find('.save').click(function() {
                                                    data.coords = linkImageCoords;
                                                    savePost({coords: linkImageCoords});
                                                    closePopup();
                                                });
                                                $popup.find('.original > img').Jcrop({
                                                    onChange: showPreview,
                                                    onSelect: showPreview,
                                                    aspectRatio: 2.06,
                                                    minSize: [130,63],
                                                    setSelect: [0,0,130,63]
                                                });
                                            };
                                        });
                                } else {
                                    el.find('.link-img').remove();
                                }
                                if (data.title) {
                                    el.find('div.link-description-text a')
                                        .text(data.title)
                                        .click(function() {
                                            var $title = $(this);
                                            $title.attr('contenteditable', true).focus();
                                            return false;
                                        })
                                        .blur(function() {
                                            var $title = $(this);
                                            $title.attr('contenteditable', false);
                                            data.title = $title.text();
                                            savePost({title: $title.text()});
                                        });
                                }
                                if (data.description) {
                                    el.find('div.link-description-text p')
                                        .text(data.description)
                                        .click(function() {
                                            var $description = $(this);
                                            $description.attr('contenteditable', true).focus();
                                            return false;
                                        })
                                        .blur(function() {
                                            var $description = $(this);
                                            $description.attr('contenteditable', false);
                                            data.description = $description.text();
                                            savePost({description: $description.text()});
                                        });
                                }
                            });
                        }
                        function addPhoto(path, filename, url, el) {
                            var $photo = $('<span/>', {class: 'attachment'})
                                .append('<img src="' + path + '" alt="" />')
                                .append($('<div />', {class: 'delete-attach', title: 'Удалить'})
                                .click(function() {
                                        $photo.remove();
                                    })
                                )
                                .append($('<input />', {type: 'hidden', name: 'filename', value: filename, "class" : 'filename'}))
                                .append($('<input />', {type: 'hidden', name: 'url', value: url, "class" : 'url'}))
                                .appendTo(el);
                        }

                        var cache = {
                            html: $el.html(),
                            scroll: $(window).scrollTop()
                        };
                        $post.find('> .content').draggable('disable');
                        $post.editing = true;
                        $buttonPanel.hide();
                        $el.html('');

                        var $edit = $('<div/>', {class: 'editing'}).appendTo($el);
                        var $content = $('<div/>').appendTo($edit);
                        var $attachments = $('<div/>', {class: 'attachments'}).appendTo($edit);
                        var $text = $('<textarea/>').appendTo($content);
                        var $links = $('<div/>', {class: 'links link-info-content'}).appendTo($attachments);
                        var $photos = $('<div/>', {class: 'photos'}).appendTo($attachments);
                        var $actions = $('<div/>', {class: 'actions'}).appendTo($edit);
                        var $saveBtn = $('<div/>', {class: 'save button l', html: 'Сохранить'}).click(function() {onSave()}).appendTo($actions);
                        var $cancelBtn = $('<a/>', {class: 'cancel l', html: 'Отменить'}).click(function() {onCancel()}).appendTo($actions);
                        var $uploadBtn = $('<a/>', {class: 'upload r', html: 'Прикрепить'}).appendTo($actions);

                        var uploader = new qq.FileUploader({
                            debug: true,
                            element: $uploadBtn.get(0),
                            action: root + 'int/controls/image-upload/',
                            template: '<div class="qq-uploader">' +
                                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                                '<div class="qq-upload-button">Прикрепить</div>' +
                                '<ul class="qq-upload-list"></ul>' +
                                '</div>',
                            onComplete: function(id, fileName, res) {
                                addPhoto(res.image, res.filename, res.url, $photos);
                            }
                        });
                        var onSave = function() {
                            var text = $text.val();
                            var link = $links.find('a').attr('href');
                            var photos = [];
                            $photos.children().each(function() {
                                var photo = {};
                                photo.filename = $(this).find('.filename').val();
                                photo.url = $(this).find('.url').val();
                                photos.push(photo);
                            });
                            if (!($.trim(text) || link || photos.length)) {
                                return $text.focus();
                            } else {
                                Events.fire("post",
                                    text,
                                    photos,
                                    link,
                                    postId,
                                    function(data) {}
                                );
                            }
                        };
                        var onCancel = function() {
                            $post.find('> .content').draggable('enable');
                            $post.editing = false;
                            $buttonPanel.show();
                            $el.html(cache.html);
                            $edit.remove();
                        };

                        if (true || data.text) {
                            var text = data.text;
                            $text
                                .val(text.split('<br />').join('')) // because it's textarea
                                .appendTo($content)
                                .bind('paste', function(e) {
                                    setTimeout(function() {
                                        parseUrl($text.val(), function(link, domain) {
                                            if ($text.link && $links.html() || $text.link == link) return;
                                            $text.link = link;
                                            addLink(link, domain, $links);
                                        });
                                    }, 0);
                                })
                                .bind('keyup', function(e) {
                                    if (e.ctrlKey && e.keyCode == KEY.ENTER) {
                                        onSave();
                                    }
                                })
                                .autoResize()
                                .keyup().focus();
                            setCaretToPos($text.get(0), text.length);
                        }

                        if (data.link) {
                            var link = data.link;
                            parseUrl(data.link, function(link, domain) {
                                addLink(link, domain, $links);
                            });
                        }

                        if (data.photos) {
                            var photos = eval(data.photos);
                            $(photos).each(function() {
                                addPhoto(this.path, this.filename, this.url, $photos);
                            });
                        }
                    })($post, $content, data);
                }
            });
        });
    })();

    // Комментирование записи
    $leftPanel.delegate('.post > .comments .new-comment textarea', 'focus', function() {
        $(this).autoResize();
        var $newComment = $(this).closest('.new-comment');
        $newComment.addClass('open');
    });
    $leftPanel.delegate('.post > .comments .new-comment textarea', 'keyup', function(e) {
        if (e.ctrlKey && e.keyCode == KEY.ENTER) {
            var $newComment = $(this).closest('.new-comment');
            var $sendBtn = $newComment.find('.send');
            $sendBtn.click();
        }
    });
    $leftPanel.delegate('.post > .comments .comment > .delete', 'click', function(e) {
        var $target = $(this);
        var $comment = $target.closest('.comment');
        var commentId = $comment.data('id');
        Events.fire('comment_delete', commentId, function() {
            $comment.data('html', $comment.html());
            $comment.addClass('deleted').html('Комментарий удален. <a class="restore">Восстановить</a>.');
        });
    });
    $leftPanel.delegate('.post > .comments .comment.deleted > .restore', 'click', function() {
        var $target = $(this);
        var $comment = $target.closest('.comment');
        var commentId = $comment.data('id');
        Events.fire('comment_restore', commentId, function() {
            $comment.removeClass('deleted').html($comment.data('html'));
        });
    });
    $leftPanel.delegate('.post > .comments .new-comment .send', 'click', function() {
        var $target = $(this);
        var $post = $target.closest('.post');
        var $newComment = $target.closest('.new-comment');
        var $textarea = $newComment.find('textarea');
        var $button = $newComment.find('.send:not(.load)');
        var $commentsList = $('.comments > .list', $post);
        var postId = $post.data('id');
        if (!$textarea.val()) {
            $textarea.focus();
        } else {
            $button.addClass('load');
            Events.fire('comment_post', postId, $textarea.val(), function(html) {
                $button.removeClass('load');
                $textarea.val('').focus();
                $commentsList.append(html).find('.date').easydate(easydateParams);

                var $moderation = $newComment.next('.moderation');
                if ($moderation.length && !$moderation.data('checked')) {
                    $moderation.data('checked', true);
                    Events.fire('leftcolumn_reject_post', postId, function() {
                        $post.slideUp(200);
                    });
                }
            });
        }
    });
    $leftPanel.delegate('.post > .comments .show-more:not(.hide):not(.load)', 'click', function() {
        var $target = $(this);
        var $post = $target.closest('.post');
        var $commentsList = $('.comments > .list', $post);
        var postId = $post.data('id');
        var tmpText = $target.text();
        $target.addClass('load').html('&nbsp;');
        Events.fire('comment_load', {postId: postId, all: true}, function(html) {
            $target.removeClass('load').html(tmpText);
            $commentsList.html(html).find('.date').easydate(easydateParams);
        });
    });
    $leftPanel.delegate('.post > .comments .show-more.hide:not(.load)', 'click', function() {
        var $target = $(this);
        var $post = $target.closest('.post');
        var $commentsList = $('.comments > .list', $post);
        var postId = $post.data('id');
        var tmpText = $target.text();
        $target.addClass('load').html('&nbsp;');
        Events.fire('comment_load', {postId: postId, all: false}, function(html) {
            $target.removeClass('load').html(tmpText);
            $commentsList.html(html).find('.date').easydate(easydateParams);
        });
    });
    $(document).on('mousedown', function(e) {
        var $newComment = $(e.target).closest('.new-comment.open');
        if (!$newComment.length) {
            $('.new-comment.open').each(function() {
                var $newComment = $(this);
                var $textarea = $newComment.find('textarea');
                if (!$textarea.val()) {
                    $newComment.removeClass('open');
                    $textarea.height('auto');

                    var $moderation = $newComment.next('.moderation');
                    if ($moderation.length && !$moderation.data('checked')) {
                        $newComment.hide();
                        $moderation.show();
                    }
                }
            });
        }
    });

    // Показать полностью в левом меню
    $leftPanel.delegate(".show-cut", "click" ,function(e){
        var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut      = $content.find('.cut').html();

        $shortcut.html(shortcut + ' ' + cut);
        $(this).remove();

        e.preventDefault();
    });

    $('#wall-switcher a').click(function() {
        var $target = $(this);
        $target.hide();
        $target.parent().find('a[data-switch-to="' + $target.data('type') + '"]').show();
        loadArticles(true);
    });

    // Показать полностью в правом меню
    $(".right-panel").delegate(".toggle-text", "click", function(e) {
        $(this).parent().toggleClass('collapsed');
    });

    // Кнопка "наверх"
    (function(w) {
        var $elem = $('#go-to-top');
        $elem.click(function() {
            $(w).scrollTop(0);
        });
        $(w).bind('scroll', function(e) {
            if (e.currentTarget.scrollY <= 0) {
                $elem.hide();
            } else if (!$elem.is(':visible')) {
                $elem.show();
            }
        });
    })(window);

    // Подгрузка имени и аватара
    (function() {
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
    })();
});

var linkTplFull = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            <div class="link-description-content">\
                <div class="link-img l" />\
                <div class="link-description-text l">\
                    <a href="" target="_blank"></a>\
                    <p></p>\
                </div>\
                <div class="clear"></div>\
            </div>';

var linkTplShort = '<div class="link-status-content"><span>Ссылка: <a href="" target="_blank"></a></span></div>\
            </div>';

 var BOX_AUTHOR =
'<div class="photo" style="float: left; margin-right: 10px; height: 100px;">' +
    '<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
        '<img src="<?=user.photo?>" alt="" />' +
    '</a>' +
'</div>' +
'<div class="info">' +
    '<?=text?>' +
'</div>';

 var BOX_ADD_AUTHOR =
'Вы действительно хотите назначить ' +
'<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
    '<?=user.name?>' +
'</a>' +
' автором?';

 var BOX_DELETE_AUTHOR =
'Вы действительно хотите удалить ' +
'<a href="http://vk.com/id<?=user.id?>" target="_blank">' +
    '<?=user.name?>' +
'</a>' +
' из списка авторов?';

var Elements = {
    initImages: function($block) {
        $block.find(".fancybox-thumb").fancybox({
            prevEffect: 'none',
            nextEffect: 'none',
            closeBtn:false,
            fitToView: false,
            helpers: {
                title: {type : 'inside'},
                buttons: {}
            }
        });

        //логика картинок топа
        $block.find(".post-image-top img").bind("load", function () {
            var src = $(this).attr('src');
            var img = new Image();
            var link = $(this).closest(".post").find('.ajax-loader-ext');

            img.onload = function() {
                if (this.width < 250 && this.height < 250) {
                    //small
                    Elements.initLinkLoader(link, true);
                } else {
                    //big
                    Elements.initLinkLoader(link, false);
                }
            };

            img.src = src;
        });

        $block.find(".timestamp").easydate(easydateParams);
        $block.find(".date").easydate(easydateParams);
        $block.find('.images-ready').imageComposition();
        $('.right-panel .images').imageComposition('right');
    },
    initDraggable: function($block) {
        $block.find('.slot .post.movable .content').addClass('dragged');

        $block.find(".post.movable:not(.blocked) > .content").draggable({
            revert: 'invalid',
            appendTo: 'body',
            cursor: 'move',
            cursorAt: {left: 100, top: 20},
            helper: function() {
                return $('<div/>').html('Укажите, куда поместить пост...').addClass('moving dragged');
            },
            start: function() {
                var self = $(this),
                    $post = self.closest('.post');
                $post.addClass('moving');
            },
            stop: function() {
                var self = $(this),
                    $post = self.closest('.post');
                $post.removeClass('moving');
            }
        });
    },
    initDroppable: function($block) {
        var dragdrop = function(post, slot, queueId, callback, failback){
            Events.fire('post_moved', post, slot, queueId, function(state, newId){
                if (state) {
                    callback(newId);
                } else {
                    failback();
                }
            });
        };

        $block.find('.items .slot').droppable({
            activeClass: "ui-state-active",
            hoverClass: "ui-state-hover",

            drop: function(e, ui) {
                var $target = $(this),
                    $post = $(ui.draggable).closest('.post');

                if ($target.hasClass('empty')) {
                    dragdrop($post.data("id"), $target.data("id"), $post.data("queue-id"), function(newId){
                        if ($post.hasClass('movable')) {
                            $target.html($post);
                        }
                        $target.addClass('image-compositing');
                    });
                }
            }
        });
    },
    initLinks: function($block) {
        $block.find('img.ajax-loader').each(function(){
            Elements.initLinkLoader($(this), true);
        });
    },
    leftdd: function(){
        return $("#source-select").multiselect("getChecked").map(function(){
            return this.value;
        }).get();
    },
    rightdd: function(value){
        if (typeof value == 'undefined') {
            return $("#right-drop-down").data("selected");
        } else {
            $("#right-drop-down").dropdown('getMenu').find('.ui-dropdown-menu-item[data-id="' + value + '"]').mouseup();
        }
    },
    leftType: function(){
        return $('.left-panel .type-selector a.active').data('type');
    },
    rightType: function(){
        return $('.right-panel .type-selector a.active').data('type');
    },
    calendar: function(value){
        if(typeof value == 'undefined') {
            var timestamp = $("#calendar").datepicker("getDate");
            return timestamp ? timestamp.getTime() / 1000 : null;
        } else {
            $("#calendar").datepicker("setDate", value).closest(".calendar").find(".caption").html("&nbsp;");
        }
    },
    initLinkLoader: function(obj, full){
        var container   = obj.parents('div.link-info-content');
        var link        = obj.attr('rel');

        $.ajax({
            url: 'http://im.' + hostname + '/int/controls/parse-url/',
            type: 'GET',
            dataType: 'jsonp',
            data: {
                url: link
            },
            success: function (data) {
                if (full) {
                    container.html(linkTplFull);
                } else {
                    container.html(linkTplShort);
                }

                if (data.img) {
                    container.find('.link-img').css('background-image', 'url(' + data.img + ')');
                } else {
                    container.find('.link-img').remove();
                }
                if (data.title) {
                    container.find('div.link-description-text a').text(data.title);
                }
                if (data.description) {
                    container.find('div.link-description-text p').text(data.description);
                }

                container.find('a').attr('href', link);

                var matches = link.match(pattern);

                var shortLink = link;
                if (matches[2]) {
                    shortLink = matches[2];
                }
                container.find('div.link-status-content span a').text(shortLink);
            }
        });
    },
    getUserGroupId: function() {
        return $('.user-groups-tabs').find('.tab.selected').data('user-group-id') || null;
    },
    getArticleStatus: function() {
        return $('.authors-tabs').find('.tab.selected').data('article-status') || 1;
    },
    getSortType: function() {
        return $('.wall-title a').data('type');
    },
    getWallLoader: function() {
        return $('#wall-load');
    },
    getSwitcherType: function() {
        return $('#wall-switcher').find('a:visible').data('type');
    }
};
