var QueueWidget = Event.extend({
    init: function() {
        this.$queue = $('#queue');
        this.initAutoload();
    },

    /**
     * Загрузка ленты очереди
     * @return Deferred
     */
    loadPage: function() {
        return Control.fire('get_queue', {
            targetFeedId: Elements.rightdd(),
            timestamp: Elements.calendar(),
            type: Elements.rightType()
        })
    },

    /**
     * Обновление ленты очереди
     * @return Deferred|bool
     */
    update: function() {
        var t = this;
        var targetFeedId = Elements.rightdd();

        if (!targetFeedId) {
            return false;
        }

        $.cookie('currentTargetFeedId', targetFeedId, {expires: 7, path: '/', secure: false});

        if (Elements.rightType() == 'all') {
            $('.queue-footer').hide();
        } else {
            $('.queue-footer').show();
        }

        return t.loadPage().success(function(data) {
            if (data) {
                var tmpEl = document.createElement('div');
                var $block = $(tmpEl).html(data);
                t.$queue.show().html($block);
                Elements.initDraggable($block);
                Elements.initDroppable($('#right-panel'));
                Elements.initImages($block);
                Elements.initLinks($block);
                $block.find('.post.blocked').draggable('disable');
            } else {
                t.$queue.empty();
            }
            t.renderSize();
        });
    },

    renderSize: function() {
        var size = this.$queue.find('.post').length;
        $('.queue-title').text((size == 0 ? 'ничего не' : size) + ' ' + Lang.declOfNum( size, ['запланирована', 'запланировано', 'запланировано'] ));
    },

    initQueue: function() {
        var t = this;
        var $queue = this.$queue;

        // Удаление постов
        $queue.delegate('.delete', 'click', function() {
            var $post = $(this).closest('.post'),
            pid = $post.data('id');
            Events.fire('rightcolumn_deletepost', pid);
        });

        // Смена даты
        $queue.delegate('.time', 'click', function() {
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
        });

        $queue.delegate('.time-edit', 'blur keydown', function(e) {
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
                    $post.animate({height: 0}, 200, function() {
                        $(this).remove();
                    });
                }
            }
        });

        $queue.delegate('.time-of-removal', 'click', function() {
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
        });

        $queue.delegate('.time-of-removal-edit', 'blur keydown', function(e) {
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
                Events.fire('rightcolumn_removal_time_edit', gridLineId, gridLineItemId, time, qid, function(state) {
                    if (state) {
                        t.updateQueue();
                    }
                });
            }
        });

        $queue.delegate('.datepicker', 'click', function() {
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
                        $queue.css('overflow', 'hidden');
                    },
                    onClose: function() {
                        time = $time.text();
                        $header.find('span.datepicker').removeClass('active');
                        $queue.css('overflow', 'auto');
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

        // Показать полностью в правом меню
        $queue.delegate('.toggle-text', 'click', function(e) {
            $(this).parent().toggleClass('collapsed');
        });

        // Показать полностью в раскрытом правом меню
        $queue.delegate('.show-cut', 'click', function(e) {
            var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut = $content.find('.cut').html();

            $shortcut.html(shortcut + ' ' + cut);
            $(this).remove();

            e.preventDefault();
        });

        $('.queue-footer .add-button').click(function() {
            $queue.scrollTo(0);
            var $newPost = $(QUEUE_SLOT_ADD);
            $newPost.prependTo($queue).animate({height: 110}, 200);
            $newPost.find('.time').click();
        });

        t.initInlineCreate();
    },

    /**
     * Инициализация создания публикации в ячейке
     * @task 13268
     */
    initInlineCreate: function() {
        var t = this;
        var $queue = this.$queue;

        $queue.delegate('.slot.empty:not(.new):not(.edit)', 'click', function(e) {
            if (e.target != e.currentTarget) {
                return;
            }

            var $slot = $(this);
            var $textarea = $slot.find('textarea');
            $queue.find('.slot.edit').removeClass('edit');
            $slot.addClass('edit');
            $textarea.focus();

            if ($slot.data('new-post-inited')) {
                return;
            }
            $slot.data('new-post-inited', true);

            $slot.data('imageUploader', app.imageUploader({
                $element: $slot.find('.upload'),
                $listElement: $slot.find('.attachments')
            }));

            $textarea.keyup(function(e) {
                if (e.ctrlKey && e.keyCode == KEY.ENTER) {
                    t.saveArticle($slot);
                }
            });
        });

        $queue.delegate('.slot.edit .cancel', 'click', function() {
            var $slot = $(this).closest('.slot');
            $slot.removeClass('edit');
        });

        $queue.delegate('.slot.edit .save', 'click', function() {
            var $slot = $(this).closest('.slot');
            t.saveArticle($slot);
        });
    },

    /**
     * Сохранение ячейки в очереди
     * @task 13268
     * @param $slot - ячейка, которую нужно сохранить
     */
    saveArticle: function($slot) {
        var t = this;
        var $textarea = $slot.find('textarea');
        var text = $.trim($textarea.val());
        var imageUploader = $slot.data('imageUploader');
        var files = imageUploader && imageUploader.getPhotos();
        if (text || files) {
            $slot.addClass('locked');
            Events.fire('post', text, files, '', null, function(data) {
                if (data && data.articleId) {
                    var postId = data.articleId;
                    Events.fire('post_moved', postId, $slot.data('id'), null, function() {
                        t.update();
                    });
                }
            });
        } else {
            $textarea.focus();
        }
    },

    /**
     * Бесконечный скроллинг в ленте отправки
     * @task 13271
     */
    initAutoload: function() {
        var t = this;
        t.$queue.scroll(function() {
            var scrollTop = t.$queue.scrollTop();
            if (scrollTop <= 0) {
                t.showNextTopPage();
            } else if (scrollTop >= t.$queue[0].scrollHeight - t.$queue.outerHeight()) {
                t.showNextBottomPage();
            }
        });
    },

    getPageData: function(id) {
        var t = this;

        if (t.getCachedPageData(id)) {
            return t.getCachedPageData(id);
        } else {
            return t.loadPage().success(function(data) {
                t.setCachedPageData(id, data);
            });
        }
    },

    setCachedPageData: function(id, data) {
        this.getCachedPages()[id] = data;
    },

    getCachedPageData: function(id) {
        return this.getCachedPages()[id];
    },

    getCachedPages: function() {
        return this._chachedPages || (this._chachedPages = {});
    },

    setFirstPageId: function(id) {
        this._firstPageId = id;
    },

    getFirstPageId: function() {
        return this._firstPageId || (this._firstPageId = 0);
    },

    setLastPageId: function(id) {
        this._lastPageId = id;
    },

    getLastPageId: function() {
        return this._lastPageId || (this._lastPageId = 0);
    },

    appendPageData: function(id, pageData, isTop) {
        var t = this;

        if (isTop) {
            t.$queue.prepend(pageData);
            t.$queue.find('.queue-title').removeClass('fixed');
            t.$queue.find('.queue-title:first').addClass('fixed');
        } else {
            t.$queue.find('.queue-title').removeClass('fixed');
            t.$queue.find('.queue-title:last').addClass('fixed');
            t.$queue.append(pageData);
        }
    },

    showNextTopPage: function() {
        var t = this;
        var pageData = t.getPageData(t.getFirstPageId());

        if (typeof pageData.success == 'function') {
            Elements.getWallLoader().show();
            pageData.success(function() {
                Elements.getWallLoader().hide();
                t.showNextTopPage();
            });
        } else {
            var oldScrollHeight = t.$queue[0].scrollHeight;
            t.setFirstPageId(t.getFirstPageId() - 1);
            t.setCachedPageData(t.getFirstPageId() - 1, t.getPageData(t.getFirstPageId() - 1));
            t.appendPageData(t.getFirstPageId(), pageData, true);
            t.$queue.scrollTop(t.$queue[0].scrollHeight - oldScrollHeight);
        }
    },

    showNextBottomPage: function() {
        var t = this;
        var pageData = t.getPageData(t.getLastPageId());

        if (typeof pageData.success == 'function') {
            Elements.getWallLoader().show();
            pageData.success(function() {
                Elements.getWallLoader().hide();
                t.showNextBottomPage();
            });
        } else {
            t.setLastPageId(t.getLastPageId() + 1);
            t.setCachedPageData(t.getLastPageId() + 1, t.getPageData(t.getLastPageId() + 1));
            t.appendPageData(t.getLastPageId(), pageData, false);
        }
    }
});
