var QueueWidget = Event.extend({
    init: function() {
        var t = this;
        t.$queue = $('#queue');
        t.initAutoload();
    },

    /**
     * Загрузка ленты очереди
     * @param {number=} timestamp
     * @param {boolean=} isUp
     * @returns {Deferred}
     */
    loadPages: function(timestamp, isUp) {
        return Control.fire('get_queue', {
            direction: isUp ? 'up' : 'down',
            timestamp: timestamp,
            targetFeedId: Elements.rightdd(),
            type: Elements.rightType()
        })
    },

    /**
     * Обновление ленты очереди
     * @param {number} timestamp
     * @returns {Deferred}
     */
    update: function(timestamp) {
        var t = this;
        var targetFeedId = Elements.rightdd();
        timestamp = timestamp || Elements.calendar();
        var isPageChanged = t.getPageTimestamp(t.getCurrentPage()) != timestamp;
        var deferred = new Deferred();

        if (!targetFeedId) {
            setTimeout(function() {
                deferred.fireError('targetFeedId does not exist!');
            }, 0);
        } else {
            t.loadPages(timestamp).success(function(data) {
                if (data) {
                    var $page = $(data);
                    t.$queue.html($page);
                    Elements.initDraggable($page);
                    Elements.initDroppable($('#right-panel'));
                    Elements.initImages($page);
                    Elements.initLinks($page);
                    $page.find('.post.blocked').draggable('disable');
                } else {
                    t.$queue.empty();
                }

                $.cookie('currentTargetFeedId', targetFeedId, {expires: 7, path: '/', secure: false});
                t.trigger('changeCurrentPage', $page);

                if (isPageChanged) {
                    t.$queue.data('cancelEvent', true).scrollTop(0);
                }

                if (Elements.rightType() == 'all') {
                    $('.queue-title.add-button').hide();
                } else {
                    $('.queue-title.add-button').show();
                }

                t.clearCache();
                deferred.fireSuccess(data);
            }).error(function(error) {
                deferred.fireError(error);
            });
        }

        return deferred;
    },

    initQueue: function() {
        var t = this;
        var $queue = this.$queue;

        // Удаление постов
        $queue.delegate('.delete', 'click', function() {
            var $post = $(this).closest('.post');
            var $page = $post.closest('.queue-page');
            var pid = $post.data('id');

            Events.fire('rightcolumn_deletepost', pid, function() {
                t.updatePage($page);
            });
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
            var $page = $post.closest('.queue-page');
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
                        if (state) {
                            t.updatePage($page);
                        }
                    });
                }
            } else if (!time) {
                if ($post.hasClass('new')) {
                    $post.transition({height: 0}, 200, function() {
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
            var $page = $post.closest('.queue-page');
            var gridLineId = $post.data('grid-id');
            var gridLineItemId = $post.data('grid-item-id');

            var time = ($input.val() == '__:__') ? '' : $input.val().split('_').join('0');
            var qid = $post.find('.post').data('queue-id');
            $input.blur().hide().val(time);

            if (time) {
                Events.fire('rightcolumn_removal_time_edit', gridLineId, gridLineItemId, time, qid, function() {
                    t.updatePage($page);
                });
            }
        });

        $queue.delegate('.datepicker', 'click', function() {
            var $target = $(this);
            var $header = $target.parent();

            if (!$header.data('datepicker')) {
                var $datepicker = $('<input type="text" />');
                var $slot = $target.closest('.slot');
                var $page = $slot.closest('.queue-page');
                var $time = $slot.find('.time');
                var gridLineId = $slot.data('grid-id');
                var startDate = $slot.data('start-date');
                var endDate = $slot.data('end-date');
                var defStartDate = $slot.data('start-date');
                var defEndDate = $slot.data('end-date');
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
                        $slot.data('start-date', $.datepick.formatDate(dates[0]));
                        $slot.data('end-date', $.datepick.formatDate(dates[1]));
                        startDate = $slot.data('start-date');
                        endDate = $slot.data('end-date');
                    },
                    onShow: function() {
                        $header.find('span.datepicker').addClass('active');
                        $queue.css('overflow', 'hidden');
                    },
                    onClose: function() {
                        time = $time.text();
                        $header.find('span.datepicker').removeClass('active');
                        $queue.css('overflow', 'auto');
                        if ($slot.hasClass('new')) {
                            // Добавление ячейки
                            Events.fire('rightcolumn_save_slot', gridLineId, time, startDate, endDate, function() {
                                t.updatePage($page);
                            });
                        } else {
                            // Редактироваиние ячейки
                            if (defStartDate != startDate || defEndDate != endDate) {
                                Events.fire('rightcolumn_save_slot', gridLineId, time, startDate, endDate, function() {
                                    t.updatePage($page);
                                });
                            }
                        }
                    }
                });
                $datepicker.val(startDate + ' - ' + endDate).focus();
            }
        });

        // Показать полностью в правом меню
        $queue.delegate('.toggle-text', 'click', function() {
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

        t.initSlotCreate();
        t.initInlineCreate();
    },

    initSlotCreate: function() {
        var t = this;
        t.$queue.delegate('.add-button', 'click', function() {
            var $newSlot = $(QUEUE_SLOT_ADD);
            var $page = $(this).closest('.queue-page');
            var dateString = $.datepick.formatDate(new Date(t.getPageTimestamp($page) * 1000));
            $newSlot.prependTo($page).transition({height: 110}, 200);
            if ($page.position().top < 0) {
                t.$queue.scrollTop(t.$queue.scrollTop() + $page.position().top);
            }
            $newSlot.data('start-date', dateString);
            $newSlot.data('end-date', dateString);
            $newSlot.find('.time').click();
        });
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

        $('.queue-footer .add-button').click(function() {
            $queue.scrollTo(0);
            var $newPost = $(QUEUE_SLOT_ADD);
            $newPost.prependTo($queue).animate({height: 110}, 200);
            $newPost.find('.time').click();
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
        var photos = imageUploader && imageUploader.getPhotos();
        var $page = $slot.closest('.queue-page');

        if (text || photos) {
            $slot.addClass('locked');
            app.savePost({
                text: text,
                photos: photos
            }).success(function(data) {
                if (data && data.articleId) {
                    var postId = data.articleId;
                    Events.fire('post_moved', postId, $slot.data('id'), null, function() {
                        t.updatePage($page);
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
            if (t.$queue.data('cancelEvent')) {
                t.$queue.data('cancelEvent', false);
                return;
            }

            var scrollTop = t.$queue.scrollTop();
            var queueHeight = t.$queue.height();
            var $pages = t.getPages();

            if (scrollTop <= 0) {
                t.showNextTopPage();
            } else if (scrollTop + queueHeight >= t.$queue[0].scrollHeight) {
                t.showNextBottomPage();
            }

            $pages.each(function() {
                var $page = $(this);
                if ($page.position().top + $page.outerHeight() > 0) {
                    t.setCurrentPage($page);
                    return false;
                }
            });
        });

        t.on('changeCurrentPage', function($page) {
            t.getPages().find('.queue-title').removeClass('fixed');
            $page.find('.queue-title').first().addClass('fixed');
        });
    },

    /**
     * Возвращает все страницы в ленте очереди
     * @returns {jQuery}
     */
    getPages: function() {
        return this._$pages || (this._$pages = this.$queue.find('.queue-page'));
    },

    /**
     * Возвращает данные страницы
     * @param {number} timestamp
     * @param {boolean=false} isUp
     * @returns {Deferred}
     */
    getPageData: function(timestamp, isUp) {
        var t = this;
        var deferred = new Deferred();

        if (t.getCachedPageData(timestamp) !== undefined) {
            deferred.fireSuccess(t.getCachedPageData(timestamp));
        } else {
            return t.loadPages(timestamp, isUp).success(function(data) {
                t.setCachedPageData(timestamp, data);
                deferred.fireSuccess(data);
            });
        }

        return deferred;
    },

    /**
     * @returns {Deferred}
     */
    getNextTopPageData: function() {
        return this.getPageData(this.getFirstPageTimestamp() + (TIME.DAY / 1000), true);
    },

    /**
     * @returns {Deferred}
     */
    getNextBottomPageData: function() {
        return this.getPageData(this.getLastPageTimestamp() - (TIME.DAY / 1000), false);
    },

    /**
     * @param {number} timestamp
     * @returns {*}
     */
    getCachedPageData: function(timestamp) {
        return this._chachedPages ? this._chachedPages[timestamp] : undefined;
    },

    /**
     * @param {number} timestamp
     * @param {*} data
     */
    setCachedPageData: function(timestamp, data) {
        if (!this._chachedPages) {
            this._chachedPages = {};
        }
        this._chachedPages[timestamp] = data;
    },

    /**
     * @returns {jQuery}
     */
    getCurrentPage: function() {
        return this._$currentPage || (this._$currentPage = this.getPages().first());
    },

    /**
     * @param {jQuery} $page
     */
    setCurrentPage: function($page) {
        if (!this._$currentPage || $page[0] != this._$currentPage[0]) {
            this._$currentPage = $page;
            this.trigger('changeCurrentPage', $page);
        }
    },

    /**
     * @returns {number}
     */
    getFirstPageTimestamp: function() {
        return this.getPageTimestamp(this.getPages().first());
    },

    /**
     * @returns {number}
     */
    getLastPageTimestamp: function() {
        return this.getPageTimestamp(this.getPages().last());
    },

    /**
     * @param $page
     * @returns {number}
     */
    getPageTimestamp: function($page) {
        return intval($page.data('timestamp'));
    },

    /**
     * Загрузка страницы в кэш
     */
    preloadNextTopPage: function() {
        this.getNextTopPageData();
    },

    /**
     * Загрузка страницы в кэш
     */
    preloadNextBottomPage: function() {
        this.getNextBottomPageData();
    },

    /**
     * Очистить кэш и всю хуйню
     */
    clearCache: function() {
        this._chachedPages = null;
        this._$currentPage = null;
        this._$pages = null;
    },

    /**
     * Добавить страницу в DOM
     * @param {string} pageData
     * @param {boolean} isTop
     */
    appendPageData: function(pageData, isTop) {
        var t = this;
        var $page = $(pageData);

        if (!$page.hasClass('queue-page')) {
            return;
        }

        if (isTop) {
            t.$queue.prepend($page);
        } else {
            t.$queue.append($page);
        }

        Elements.initDraggable($page);
        Elements.initDroppable($('#right-panel'));
        Elements.initImages($page);
        Elements.initLinks($page);
        $page.find('.post.blocked').draggable('disable');
        t._$pages = null;
    },

    /**
     * Показать следующую страницу сверху
     */
    showNextTopPage: function() {
        var t = this;

        Elements.getWallLoader().show();
        t.getNextTopPageData().always(function() {
            Elements.getWallLoader().hide();
        }).success(function(pageData) {
            var oldScrollHeight = t.$queue[0].scrollHeight;
            t.appendPageData(pageData, true);
            t.preloadNextTopPage();
            t.$queue.scrollTop(t.$queue[0].scrollHeight - oldScrollHeight);
        });
    },

    /**
     * Показать следующую страницу снизу
     */
    showNextBottomPage: function() {
        var t = this;

        Elements.getWallLoader().show();
        t.getNextBottomPageData().always(function() {
            Elements.getWallLoader().hide();
        }).success(function(pageData) {
            t.appendPageData(pageData, false);
            t.preloadNextBottomPage();
        });
    },

    /**
     * Обновляет страницу в очереди
     * @param {jQuery} $page
     * @returns {Deferred}
     */
    updatePage: function($page) {
        var t = this;
        var deferred = new Deferred();
        var pageScroll = -$page.position().top;

        t.update(t.getPageTimestamp($page)).success(function() {
            t.$queue.scrollTop(pageScroll);
            deferred.fireSuccess();
        }).error(function() {
            deferred.fireError();
        });

        return deferred;
    }
});
