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
                    Elements.initDroppable();
                    Elements.initImages($page);
                    Elements.initLinks($page);
                    $page.find('.post .images').imageComposition();
                    $page.find('.post.blocked').draggable('disable');
                } else {
                    t.$queue.empty();
                }

                $.cookie('currentTargetFeedId', targetFeedId, {expires: 7, path: '/', secure: false});
                t.trigger('changeCurrentPage', $page);

//todo scroll
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

    deleteArticleInSlot: function($slot, isEmpty) {
        if (typeof isEmpty === 'undefined') {
            isEmpty = false;
        }
        
        var $post = $slot.find('.post');
        var pid = $post.data('queue-id');

        var eventName = isEmpty ? 'rightcolumn_render_empty' : 'rightcolumn_deletepost';
        Events.fire(eventName, pid, $slot.data('grid-id'), $slot.data('id'),
            function(id, $slot) {
                return function(isOk, data) {
                    if (!isEmpty) {
                        var csslass = 'hidden_' + id;
                        $('#wall').find('.' + csslass).removeClass(csslass).show();
                    }
                    if (isOk && data && data.html) {
                        var $content = $(data.html);
                        $slot.replaceWith($content);
                        Elements.attachDroppable($content);
                    }
                }
            }(pid, $slot)
        );
    },

    initQueue: function() {
        var t = this;
        var $queue = this.$queue;

        // Удаление постов
        $queue.delegate('.delete', 'click', function() {
            var $slot = $(this).closest('.slot');
            t.deleteArticleInSlot($slot);
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
            var timestamp =  $page.data('timestamp');
            var time = ($input.val() == '__:__') ? '' : $input.val().split('_').join('0');
            var qid = $post.find('.post').data('queue-id');
            $input.blur().hide().val(time);

            if (time && time != $time.text()) {
                $time.text(time);
                if (!$post.hasClass('new')) {
                    // Редактирование времени ячейки для текущего дня
                    Events.fire('rightcolumn_time_edit', gridLineId, gridLineItemId, time, timestamp, qid, function(isOk){
                        if (isOk) {
                            t.updatePage($page);
                        }
                    });
                } else {
                    Events.fire('create-grid-line', time, $post.data('start-date'), $post.data('end-date'), function(isOk, data) {
                        if (isOk && data && data.html) {
                            var $newSlot = $(data.html);
                            $post.replaceWith($newSlot);
                            Elements.attachDroppable($newSlot);
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

        $queue.delegate('.repeater', 'click', function () {
            var $slot = $(this).closest('.slot');

            var gridLineId = $slot.data('grid-id');
            var timestamp = $slot.data('id');
            Events.fire('article-queue-toggle-repeat', gridLineId, timestamp, function(isOk, data) {
                if (isOk) {
                    if (data) {
                        if (!data.success && data.message) {
                            popupError(data.message, {timeout: 7000});
                        }
                        if (data.success) {
                            var cssClass = 'gridLine_' + gridLineId;
                            if (data.repeat) {
                                t.updatePage($slot.closest('.queue-page'));
                            } else { // no-repeat
                                t.clearCache();
                                $queue.find('.' + cssClass).removeClass('repeat');
                                if (data.endDate) {
                                    var $elems = $();
                                    var heightToDelete = 0;
                                    var endDate = parseInt(data.endDate, 10);
                                    $queue.find('.queue-page').each(function(_, elem) {
                                        var currentDate = parseInt(elem.getAttribute('data-timestamp'), 10);
                                        if (currentDate > endDate) {
                                            var $toDelete = $(elem).find('.' + cssClass);
                                            var heightCorrection = 0; // в некоторых браузерах необходима коррекция высоты на 1px
                                            heightToDelete += $toDelete[0].scrollHeight + heightCorrection;
                                            $elems = $elems.add($toDelete);
                                            
                                            //<div class="empty-queue">Пусто</div>
                                        }
                                    });
                                    if ($elems.length) {
                                        t.$queue.scrollTop(t.$queue.scrollTop() - heightToDelete);
                                        $elems.remove();
                                    }
                                }
                            }
                        }
                    }
                }
            });
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

        if (text || photos) {
            $slot.addClass('locked');
            app.savePost({
                text: text,
                photos: photos
            }).success(function(data) {
                if (data && data.articleId) {
                    var postId = data.articleId;
                    Events.fire('post_moved', postId, $slot.data('id'), null, function(isOk, data) {
                        if (isOk && data && data.html) {
                            t.setSlotArticleHtml($slot, data.html);
                        }
                    });
                }
            });
        } else {
            $textarea.focus();
        }
    },

    setSlotArticleHtml: function ($slot, html) {
        var $page = $(html);
        $slot.replaceWith($page);
        Elements.initDraggable($page);
        Elements.initImages($page);
        Elements.initLinks($page);
        $page.find('.post .images').imageComposition();
        $page.find('.post.blocked').draggable('disable');
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
            } else if (scrollTop + queueHeight + 10 >= t.$queue[0].scrollHeight) {
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
        Elements.initDroppable();
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
