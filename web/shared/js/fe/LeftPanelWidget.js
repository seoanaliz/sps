var LeftPanelWidget = Event.extend({
    filterAuthorId: null,
    wallPage: -1,
    wallAutohideEnabled: false,

    init: function() {
        this.$leftPanel = $('#left-panel');
        this.$multiSelect = $('#source-select');
        this.$wall = $('#wall');

        this.initLeftPanel();
        this.initMultiSelect();
        this.initWall();
        this.initWallFilter();
        this.initAddPost();
        this.initEditPost();
        this.initWallAutoload();
        this.initTabs();
        this.initModeration();
        this.initUserFilter();
    },

    initLeftPanel: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;

        // Очистка текста
        $leftPanel.delegate('.clear-text', 'click', function(){
            var $post = $(this).closest('.post');
            var postId = $post.data('id');

            if (confirm('Вы уверены, что хотите очистить текст записи?') ) {
                Events.fire('leftcolumn_clear_post_text', postId, function(state){
                    if (state) {
                        $post.find('.shortcut').html('');
                        $post.find('.cut').html('');
                        $post.find('a.show-cut').remove();
                    }
                });
            }
        });

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
            if ($newComment.length) {
                return;
            }

            $('.new-comment.open').each(function() {
                var $newComment = $(this);
                var $textarea = $newComment.find('textarea');
                if (!$textarea.val()) {
                    $newComment.removeClass('open');
                    $textarea.height('auto');
                    $newComment.trigger('close');
                }
            });
        });

        // Показать полностью в левом меню
        $leftPanel.delegate('.show-cut', 'click', function(e){
            var $content = $(this).closest('.content'),
            $shortcut = $content.find('.shortcut'),
            shortcut = $shortcut.html(),
            cut = $content.find('.cut').html();

            $shortcut.html(shortcut + ' ' + cut);
            $(this).remove();

            e.preventDefault();
        });

        $('#wall-switcher a').click(function() {
            var $target = $(this);
            $target.hide();
            $target.parent().find('a[data-switch-to="' + $target.data('type') + '"]').show();
            t.loadArticles(true);
        });
    },

    initMultiSelect: function() {
        var t = this;
        var $multiSelect = t.$multiSelect;
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
                t.updateMultiSelect();
            },
            uncheckAll: function(){
                t.updateMultiSelect();
            }
        });
        $multiSelect.bind('multiselectclick', function() {
            t.updateMultiSelect();
        });
    },

    updateMultiSelect: function() {
        this.saveMultiSelectData();
        this.loadArticles(true);
    },

    saveMultiSelectData: function() {
        var targetFeedId = Elements.rightdd();
        var leftType = Elements.leftType();
        if (leftType == 'source') {
            $.cookie('sourceFeedIds' + targetFeedId, Elements.leftdd(), { expires: 7, path: '/', secure: false });
        }
    },

    setMultiSelectData: function(sourceFeeds, targetFeedId) {
        var t = this;
        var $multiSelect = t.$multiSelect;
        $multiSelect.find('option').remove();
        for (var i in sourceFeeds) {
            var item = sourceFeeds[i];
            $multiSelect.append('<option value="' + item.id + '">' + item.title + '</option>');
        }

        //get data from cookie
        var cookie = $.cookie('sourceFeedIds' + targetFeedId);
        if (cookie) {
            var selectedSources = cookie.split(',');
            if (selectedSources) {
                var $options = $multiSelect.find('option');
                for (i in selectedSources) {
                    $options.filter('[value="' + selectedSources[i] + '"]').prop('selected', true);
                }
            }
        }

        $multiSelect.multiselect('refresh');
        if (Elements.leftdd().length == 0) {
            $multiSelect.multiselect('checkAll').multiselect('refresh');
        }
    },

    initWall: function() {
        var t = this;
        var $multiSelect = this.$multiSelect;
        var $wall = this.$wall;

        $wall.delegate('.post > .delete', 'click', function(){
            var elem = $(this).closest('.post'),
                pid = elem.data('id'),
                gid = elem.data('group');
            Events.fire('leftcolumn_deletepost', pid, function(state){
                if (state) {
                    var deleteMessageId = 'deleted-post-' + pid;
                    var $deleteMessage = $('#' + deleteMessageId);
                    var isShowIgnoreAllBtn = !/^(authors|my)$/.test(Elements.leftType());
                    if ($deleteMessage.length) {
                        // если уже удаляли пост, то сообщение об удалении уже в DOMе
                        $deleteMessage.show();
                    } else {
                        // иначе добавляем
                        elem.before($(
                            '<div id="' + deleteMessageId + '" class="bb post deleted-post" data-group="' + gid + '" data-id="' + pid + '">' +
                                'Пост удален. <a class="recover">Восстановить</a><br/>' +
                                (isShowIgnoreAllBtn ? '<span class="button ignore">Не показывать новости сообщества</span>' : '') +
                            '</div>'
                        ));
                    }

                    elem.hide();
                }
            });
        });

        $wall.delegate('.post .ignore', 'click', function() {
            var elem = $(this).closest(".post"),
                gid = elem.data("group");
            var $menu = $multiSelect.multiselect('widget');
            $menu.find('[value=' + gid + ']:checkbox').each(function() {
                this.click();
            });
        });

        $wall.delegate('.post .recover', 'click', function() {
            var elem = $(this).closest(".post"),
                pid = elem.data("id");
            Events.fire('leftcolumn_recoverpost', pid, function(state){
                if (state) {
                    elem.hide().next().show();
                }
            });
        });

        $wall.delegate('.show-all-postponed', 'click', function() {
            var $target = $(this);
            if ($target.data('block')) {
                var $posts = $target.data('block');
                if ($posts.first().is(':visible')) {
                    $target.html($target.data('def-html'));
                    $posts.hide();
                } else {
                    $target.html('Скрыть записи в очереди');
                    $posts.show();
                }
            } else {
                $target.data('def-html', $target.html());

                t.wallPage = 0;
                Elements.getWallLoader().show();
                Control.fire('get_articles', {
                    page: t.wallPage,
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
                        $target.html('Скрыть записи в очереди');
                        Elements.initImages($posts);
                        Elements.initLinks($posts);
                    } else {
                        $target.remove();
                    }
                    Elements.getWallLoader().hide();
                });
            }
        });
    },

    initWallFilter: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;
        $leftPanel.find('.drop-down').change(function() {
            t.updateMultiSelect();
        });

        $('.wall-title .filter a').dropdown({
            width: 'auto',
            addClass: 'wall-title-menu',
            position: 'right',
            data: [
                {title: 'новые записи', type: 'new'},
                {title: 'старые записи', type: 'old'},
                {title: 'лучшие записи', type: 'best'}
            ],
            oncreate: function() {},
            onopen: function() {},
            onclose: function() {},
            onchange: function(item) {
                $('.wall-title a').text(item.title).data('type', item.type);
                Events.fire('leftcolumn_sort_type_change');
            }
        });
    },

    updateSlider: function(targetFeedId, sourceType) {
        var t = this;
        var cookie = $.cookie(sourceType + 'FeedRange' + targetFeedId);
        var from = sourceType == App.FEED_TYPE_ALBUMS ? 0 : 50;
        var to = 100;
        if (cookie) {
            var ranges = cookie.split(':');
            if (ranges.length == 2) {
                from = parseInt(ranges[0]);
                to = parseInt(ranges[1]);

                if (from < 0 || from > 100) {
                    from = 50;
                }
                if (to < 0 || to > 100) {
                    to = 100;
                }
            }
        }
        var sliderRange = $('#slider-range');
        sliderRange.data('sourceType', sourceType);

        if (!sliderRange.data('slider')) {
            sliderRange.slider({
                range: true,
                min: 0,
                max: 100,
                animate: 100,
                values: [from, to],
                create: function(event, ui) {
                    t.changeSliderRange();
                },
                slide: function(event, ui) {
                    t.changeSliderRange();
                },
                change: function(event, ui) {
                    t.changeSliderRange();
                    t.loadArticles(true);
                }
            });
        } else {
            sliderRange.slider('values', 0, from);
            sliderRange.slider('values', 1, to);
        }
    },

    changeSliderRange: function() {
        var sliderRange = $('#slider-range');
        var top = sliderRange.slider('values', 1);
        sliderRange.find('a:first').html(sliderRange.slider('values', 0));
        sliderRange.find('a:last').html(top == 100 ? 'TOP' : top);

        var targetFeedId = Elements.rightdd();
        if (targetFeedId) {
            $.cookie(sliderRange.data('sourceType') + 'FeedRange' + targetFeedId, sliderRange.slider('values', 0) + ':' + sliderRange.slider('values', 1), { expires: 7, path: '/', secure: false });
        }
    },

    reloadArticle: function(id) {
        Control.fire('article-item', {
            id: id,
            targetFeedId: Elements.rightdd()
        }).success(function(data) {
            var $elem = $('.post[data-id=' + id + ']');
            var $newElem = $(data);
            $elem.replaceWith($newElem);
            Elements.initDraggable($newElem);
            Elements.initImages($newElem);
            Elements.initLinks($newElem);
        });
    },

    loadArticles: function(clean) {
        var t = this;
        var filterAuthorId = t.filterAuthorId;

        if (articlesLoading) {
            return;
        }

        if (clean) {
            t.filterAuthorId = null;
            t.wallPage = -1;
            $(window).data('disable-load-more', false);
        }

        var sourceType = Elements.leftType();
        var targetFeedId = Elements.rightdd();
        var switcherType = Elements.getSwitcherType();
        var $newPost = $('.newpost');
        var $wallLoader = Elements.getWallLoader();
        t.wallPage++;
        articlesLoading = true;

        $wallLoader.show();
        $newPost.hide();

        var requestData = {
            sortType: Elements.getSortType(),
            page: t.wallPage,
            type: sourceType,
            targetFeedId: targetFeedId
        };

        switch (sourceType) {
            case App.FEED_TYPE_AUTHORS:
                requestData.userGroupId = Elements.getUserGroupId();
                $newPost.show();
                switch (switcherType) {
                    case 'approved':
                        requestData.articleStatus = App.ARTICLE_STATUS_APPROVED;
                        break;
                    case 'deferred':
                        requestData.articleStatus = App.ARTICLE_STATUS_REVIEWING;
                        break;
                }
                break;
            case App.FEED_TYPE_ALBUMS:
                requestData.sourceFeedIds = Elements.leftdd();
                switch (switcherType) {
                    case 'approved':
                        requestData.articleStatus = App.ARTICLE_STATUS_APPROVED;
                        break;
                    case 'deferred':
                        requestData.articleStatus = App.ARTICLE_STATUS_REVIEWING;
                        break;
                }
                break;
            case App.FEED_TYPE_MY:
                requestData.articleStatus = Elements.getArticleStatus();
                requestData.mode = App.FEED_TYPE_MY;
                break;
            case App.FEED_TYPE_ADS:
                requestData.sourceFeedIds = Elements.leftdd();
                requestData.from = 0;
                requestData.to = 100;
                if (requestData.sourceFeedIds.length == 1) {
                    $newPost.show();
                }
                break;
            case App.FEED_TYPE_TOPFACE:
            case App.FEED_TYPE_SOURCE:
                    requestData.sourceFeedIds = Elements.leftdd();
                    var $slider = $('#slider-range');
                    requestData.from = $slider.slider('values', 0);
                    requestData.to = $slider.slider('values', 1);
                break;
            default:
                break;
        }

        if (filterAuthorId) {
            requestData.mode = 'posted';
            requestData.authorId = filterAuthorId;
        }

        if (!clean) {
            requestData.articlesOnly = 1;
        }

        Control.fire('get_articles', requestData).always(function() {
            if (clean) {
                t.$wall.empty();
            }
            $wallLoader.hide();
            articlesLoading = false;
        }).success(function(html) {
            if (!html) {
                $(window).data('disable-load-more', true);
                $('.wall-title span.count').text('нет записей');
            } else {
                var tmpEl = document.createElement('div');
                var $block = $(tmpEl).html(html);
                t.$wall.append($block);
                Elements.initDraggable($block);
                Elements.initDroppable($('#right-panel'));
                Elements.initImages($block);
                Elements.initLinks($block);
                if (!$block.find('.post').length) {
                    $(window).data('disable-load-more', true);
                }
            }
            t.trigger('loadArticles', html);
        }).error(function() {
            t.trigger('loadArticles', false);
        });
    },

    initAddPost: function() {
        var t = this;
        var $form = $('.newpost');
        var $input = $form.find('textarea');
        var $uploadBtn = $form.find('.image-uploader');
        var $attachments = $form.find('.attachments');
        var imageUploader = app.imageUploader({
            $element: $uploadBtn,
            $listElement: $attachments
        });

        var foundLink, foundDomain, repostId;

        $form.click(function(e) {
            e.stopPropagation();
        });

        $input.focus(function(){
            $form.removeClass('collapsed');
            $(window).bind('click', stop);
        });

        $input.bind('paste', function() {
            setTimeout(function() {
                parseUrl($input.val());
            }, 10);
        });

        $input.autoResize();
        $input.keyup(function (e) {
            if (e.ctrlKey && e.keyCode == KEY.ENTER) {
                $form.find('.save').click();
            }
        }).keyup();

        function parseUrl(txt) {
            // если ссылку уже приаттачили
            if (foundLink) {
                return;
            }

            // если репост уже приаттачили
            if (repostId) {
                return;
            }

            var matches = txt.match(pattern);

            if (!matches) {
                return;
            }

            // если приаттачили репост
            if (repostId = t.getPostIdByURL(matches[0])) {
                var code =
                'var p=API.wall.getById({posts:"' + repostId + '"})[0];' +
                'var owner=(p.to_id>0)?API.users.get({uids:p.to_id,fields:"photo,screen_name"})[0]:API.groups.getById({gid:-p.to_id})[0];' +
                'return {owner:owner,post:p};';
                Control.callVKByOpenAPI('execute', {
                    code: code
                }).success(function(data) {
                    var post = data.post;
                    var owner = data.owner;
                    var photos = [];
                    if (owner.first_name) {
                        owner.name = owner.first_name + ' ' + owner.last_name;
                    }

                    for (var i in post.attachments) {
                        if (post.attachments[i].type == 'photo') {
                            photos.push(post.attachments[i]);
                        }
                    }

                    var $post = $(tmpl(ATTACHMENT_PREVIEW_REPOST, {
                        postId: post.id,
                        text: post.text,
                        date: post.date,
                        attachments: {
                            photos: photos
                        },
                        owner: owner
                    }));

                    $attachments.append($post);
                    $uploadBtn.hide();

                    if (photos.length) {
                        $post.find('.images-ready').imageComposition();
                    }
                }).error(function(error) {
                    repostId = false;
                    new Box({title: 'Ошибка', html: error.message}).show();
                });
            }
            // если приаттачили ссылку
            else if (matches[0] && matches[1]) {
                foundLink   = matches[0];
                foundDomain = matches[2];

                Events.fire('post_describe_link', foundLink, function(result) {
                    if (!result) {
                        return;
                    }

                    var $attachment = $(tmpl(ATTACHMENT_PREVIEW_LINK, {
                        image: result.img,
                        title: result.title,
                        description: result.description,
                        text: foundDomain,
                        link: foundLink
                    }));

                    $attachments.append($attachment);

                    editPostDescribeLink.load(
                        $attachment.find('.post_describe_header'),
                        $attachment.find('p'),
                        $attachment.find('.post_describe_image')
                    );
                });
            }
        }

        // Редактирование ссылки
        var editPostDescribeLink = {
            load: function($header, $description, $image) {
                this.header = $header;
                this.description = $description;
                this.image = $image;
                this.renderEditor();
            },
            renderEditor: function() {
                var $editField = $('<input />', {
                    type: 'text',
                    id: 'post_header'
                });
                var $editArea = $('<textarea />', {
                    id: 'post_description'
                });

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

                Events.fire('post_link_data', data);
            }
        };

        function clearForm() {
            $input.data('id', 0).val('');
            $('.attachments').empty();
            repostId = false;
            foundDomain = false;
            foundLink = false;
            $uploadBtn.show();
        }

        function stop() {
            var $linkInfo = $form.find('.link-info');
            $(window).unbind('click', stop);

            if (!$input.val().length && !$('.qq-upload-list li').length && !$linkInfo.is(':visible')) {
                $input.data('id', 0);
                $form.addClass('collapsed');
            }
        }

        $form.delegate('.delete-attachment', 'click', function() {
            var $attachment = $(this).closest('.attachment');

            if ($attachment.hasClass('post')) {
                repostId = false;
                $uploadBtn.show();
            }

            if ($attachment.hasClass('link-info')) {
                foundDomain = false;
                foundLink = false;
            }

            $attachment.remove();
        });

        $form.delegate('.save', 'click', function() {
            var $linkStatus = $form.find('.link-status');
            var link = $linkStatus.find('a').attr('href');
            var photos = imageUploader.getPhotos();
            var text = $.trim($input.val());

            if (!($.trim(text) || link || photos.length || repostId)) {
                return $input.focus();
            } else {
                $form.addClass('spinner');
                $input.blur();

                t.savePost({
                    text: text,
                    link: link,
                    photos: photos,
                    repostExternalId: repostId
                }).always(function() {
                    $form.removeClass('spinner');
                    stop();
                }).success(function() {
                    clearForm();
                    t.loadArticles(true);
                });
            }
        });

        $form.delegate('.cancel', 'click' ,function(e){
            clearForm();
            $input.val('').blur();
            $form.addClass('collapsed');
            e.preventDefault();
        });
    },

    initEditPost: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;

        // Быстрое редактирование поста в левой колонке
//        $leftPanel.delegate('.post.editable .content .shortcut, ', 'click', function() {
//            var $post = $(this).closest('.post'),
//            $content = $post.find('> .content'),
//            postId = $post.data('id');
//
//            if ($post.editing) return;
//
//            Events.fire('load_post_edit', postId, function(state, data){
//                if (state && data) {
//                    new SimpleEditPost(postId, $post, $content, data);
//                }
//            });
//        });

        // Редактирование поста в левом меню
        $leftPanel.delegate('.post .edit,.post.editable .content .shortcut', 'click', function(){
            var $post = $(this).closest('.post'),
            $el = $post.find('> .content'),
            $buttonPanel = $post.find('> .bottom.d-hide'),
            postId = $post.data('id');

            if ($post.editing) return;

            Events.fire('load_post_edit', postId, function(state, data){
                if (state && data) {
                    function setSelectionRange(input, selectionStart, selectionEnd) {
                        if (input.setSelectionRange) {
                            input.focus();
                            input.setSelectionRange(selectionStart, selectionEnd);
                        } else if (input.createTextRange) {
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
                        Events.fire('post_describe_link', link, function(data) {
                            var savePost = function(d) {
                                d = d || {};
                                Events.fire('post_link_data', {
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
                                });
                            };
                            var $del = $('<div/>', {class: 'delete-attach delete'}).click(function() {
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
                                        var showPreview = function(coords) {
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
                                el.find('.link-description-text p')
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
                    var $saveBtn = $('<button/>', {class: 'save button', html: 'Сохранить'}).click(function() {onSave()}).appendTo($actions);
                    var $cancelBtn = $('<button/>', {class: 'cancel button', html: 'Отменить'}).click(function() {onCancel()}).appendTo($actions);
                    var $uploadBtn = $('<a/>', {class: 'upload r', html: 'Прикрепить'}).appendTo($actions);

                    var imageUploader = app.imageUploader({
                        $element: $uploadBtn,
                        $listElement: $attachments
                    });

                    var onSave = function() {
                        var text = $text.val();
                        var link = $links.find('a').attr('href');
                        var photos = imageUploader.getPhotos();
                        var repostId = $post.data('repost-id');
                        if (!($.trim(text) || link || photos.length || repostId)) {
                            return $text.focus();
                        } else {
                            t.savePost({
                                text: text,
                                photos: photos,
                                link: link,
                                articleId: postId,
                                repostExternalId: repostId
                            }).success(function() {
                                t.reloadArticle(data.id);
                            });
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
                        .val(text.split('<br />').join(''))
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
                            imageUploader.addPhoto(this.path, this);
                        });
                    }
                }
            });
        });
    },

    // Автоподгрузка записей
    initWallAutoload: function() {
        var t = this;
        var $window = $(window);
        $window.scroll(function() {
            clearTimeout(t.wallScrollTimeoutAutoload);
            t.wallScrollTimeoutAutoload = setTimeout(function() {
                if (!$window.data('disable-load-more') && $window.scrollTop() > ($(document).height() - $window.height() * 2)) {
                    t.loadArticles(false);
                }
            }, 200);
        });
    },

    // Скрытие постов, которые сейчас не видны в ленте
    initWallAutohide: function() {
        var t = this;

        if (t.wallAutohideInited) {
            return;
        }
        t.wallAutohideInited = true;
        t.wallPostsPositionsTop = null;

        var $window = $(window);
        $window.scroll(onScroll);
        t.$wall.addClass('autohide-enabled');

        function onScroll() {
            clearTimeout(t.wallScrollTimeoutAutohide);
            t.wallScrollTimeoutAutohide = setTimeout(function() {
                var wallPostsPositionsTop = t.wallPostsPositionsTop || t.getWallPostsPositionsTop();
                var scrollTop = $window.scrollTop();
                var focusedElements = [];
                for (var i in wallPostsPositionsTop) {
                    if (!wallPostsPositionsTop.hasOwnProperty(i)) {
                        continue;
                    }
                    i = +i;
                    var positionTop = wallPostsPositionsTop[i];
                    var positionTopNext = wallPostsPositionsTop[i + 1];
                    if ((positionTopNext ? positionTopNext.top >= scrollTop : true) && positionTop.top <= scrollTop + $window.height()) {
                        focusedElements.push({id: positionTop.id, top: positionTop.top});
                    }
                }

                t.$wall.find('.post.show-images').removeClass('show-images');
                for (var i in focusedElements) {
                    if (!focusedElements.hasOwnProperty(i)) {
                        continue;
                    }
                    t.$wall.find('.post[data-id="' + focusedElements[i].id + '"]').addClass('show-images');
                }
            }, 500);
        }
    },

    getWallPostsPositionsTop: function() {
        var t = this;
        var $wall = t.$wall;
        var positionsTop = [];
        $wall.find('.post').each(function() {
            positionsTop.push({
                id: $(this).data('id'),
                top: $(this).offset().top
            });
        });
        return positionsTop;
    },

    initTabs: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;

        // Вкладки Источники Мои публикации Авторские Альбомы Topface в левом меню
        $leftPanel.find('.type-selector').delegate('.sourceType', 'click', function() {
            if (articlesLoading) {
                return;
            }

            $leftPanel.find('.type-selector .sourceType').removeClass('active');
            $(this).addClass('active');

            if ($(this).data('type') == App.FEED_TYPE_AUTHORS_LIST) {
                $('body').addClass('editor-mode');
                $(window).data('disable-load-more', true);
                t.updateAuthorListPage();
            } else {
                $('body').removeClass('editor-mode');
                $(window).data('disable-load-more', false);
                app.updateRightPanelDropdown();
            }
        });

        // Подвкладки Авторов: Новые Одобренные Отклоненные
        $leftPanel.find('.authors-tabs').delegate('.tab', 'click', function() {
            if (articlesLoading) {
                return;
            }

            var $tab = $(this);
            $leftPanel.find('.authors-tabs .tab').removeClass('selected');
            $tab.addClass('selected');
            t.loadArticles(true);
        });

        // Кастомные подвкладки
        $leftPanel.find('.user-groups-tabs').delegate('.tab', 'click', function() {
            if (articlesLoading) {
                return;
            }

            var $tab = $(this);
            $leftPanel.find('.user-groups-tabs .tab').removeClass('selected');
            $tab.addClass('selected');

            t.loadArticles(true);
        });
    },

    // Список авторов
    updateAuthorListPage: function(method) {
        var t = this;
        Control.fire(method || 'authors_get', {
            targetFeedId: Elements.rightdd()
        }).success(function(data) {
            t.$wall.html(data);
            var $container = t.$wall.find(' > .authors-list');

            var $navigation = $container.find('.authors-types');
            $navigation.delegate('.tab', 'click', function() {
                $navigation.find('.tab.selected').removeClass('selected');
                $(this).addClass('selected');
                t.updateAuthorListPage('authors_get');
            });

            var $input = $container.find('.author-link');
            $input.placeholder();
            $input.keyup(function(e) {
                if (e.keyCode == KEY.ENTER) {
                    $input.blur();
                    var authorId = $input.val().replace(new RegExp('(/)*(http[s]?:)?(vk.com)?(id[0-9]+)?', 'g'), '$4');
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
                        t.updateAuthorListPage();
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
    },

    // Отклонение или одобрения авторских постов
    initModeration: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;

        $leftPanel.delegate('.moderation .button.approve', 'click', function() {
            var $post = $(this).closest('.post');
            var postId = $post.data('id');
            t.acceptArticle(postId);
        });

        $leftPanel.delegate('.moderation .button.reject', 'click', function() {
            var $post = $(this).closest('.post');
            var $newComment = $post.find('.new-comment');
            if (!$newComment.length) {
                var postId = $post.data('id');
                t.declineArticle(postId);
            } else {
                var $moderation = $post.find('.moderation');
                $moderation.hide();
                $newComment.show();
                $newComment.find('.button.send').text('Отклонить');
                $newComment.find('textarea').focus();
            }
        });

        $leftPanel.delegate('.post > .comments .new-comment .send', 'click', function() {
            var $post = $(this).closest('.post');
            var $moderation = $post.find('.moderation');
            var postId = $post.data('id');
            if ($moderation.length && !$moderation.data('checked')) {
                $moderation.data('checked', true);
                t.declineArticle(postId);
            }
        });

        $leftPanel.delegate('.new-comment', 'close', function() {
            var $newComment = $(this);
            var $post = $newComment.closest('.post');
            var $moderation = $post.find('.moderation');
            if ($moderation.length && !$moderation.data('checked')) {
                $moderation.show();
                $newComment.hide();
            }
        });
    },

    /**
     * Одобряет запись и скрывает её
     * @param articleId
     * @return Deferred|bool
     */
    acceptArticle: function(articleId) {
        var $post = this.$leftPanel.find('.post[data-id="' + articleId + '"]');
        if (!$post.length) {
            return false;
        }

        return Control.fire('accept_article', {articleId: articleId}, function() {
            $post.slideUp(200, function() {
                $(this).remove();
            });
        });
    },

    /**
     * Отклоняет запись и скрывает её
     * @param articleId
     * @return Deferred|bool
     */
    declineArticle: function(articleId) {
        var $post = this.$leftPanel.find('.post[data-id="' + articleId + '"]');
        if (!$post.length) {
            return false;
        }

        return Control.fire('decline_article', {articleId: articleId}, function() {
            $post.slideUp(200, function() {
                $(this).remove();
            });
        });
    },

    /**
     * Инициализация фильтра ленты по пользователю
     * @task 13477 Лента пользователя
     */
    initUserFilter: function() {
        var t = this;
        var $leftPanel = t.$leftPanel;
        $leftPanel.delegate('.post.author .name', 'click', function() {
            var userId = $(this).closest('.post').data('author-id');
            t.userFilter(userId);
        });
    },

    /**
     * Фильтрация ленты по пользователю
     * @task 13477 Лента пользователя
     */
    userFilter: function(userId) {
        var t = this;
        t.filterAuthorId = userId;
        t.$leftPanel.find('.header .tab.selected').removeClass('selected');
        t.loadArticles(true);
    },

    dropdownChangeLeftPanel: function(data) {
        var t = this;
        var $wallSwitcher = $('#wall-switcher');
        var $multiSelect = $('#source-select');
        var $leftPanel = t.$leftPanel;
        var $leftPanelTabs = $leftPanel.find('.type-selector');
        var $userGroupTabs = $('.user-groups-tabs');
        var targetFeedId = Elements.rightdd();
        var sourceType = Elements.leftType();
        var sourceTypes = data.accessibleSourceTypes;

        if (sourceType != App.FEED_TYPE_SOURCE) {
            $('#slider-text').hide();
            $('#slider-cont').hide();
            $('#filter-list').hide();
        } else {
            $('#slider-text').show();
            $('#slider-cont').show();
            $('#filter-list').show();
        }

        if (data.showSourceList) {
            $multiSelect.multiselect('getButton').removeClass('hidden');
        } else {
            $multiSelect.multiselect('getButton').addClass('hidden');
        }

        // фильтры по типу постов
        if (data.showArticleStatusFilter) {
            $leftPanel.find('.authors-tabs .tab').removeClass('selected');
            $leftPanel.find('.authors-tabs .tab:first').addClass('selected');
            $leftPanel.find('.authors-tabs').show();
        } else {
            $leftPanel.find('.authors-tabs').hide();
        }

        // группы юзеров
        $wallSwitcher.hide();

        if (sourceType == 'authors') {
            if (data.authorsFilters && (data.authorsFilters.all_my_filter || data.authorsFilters.article_status_filter)) {
                var showSwitcherType;

                if (data.authorsFilters.all_my_filter) {
                    showSwitcherType = 'all';
                } else {
                    showSwitcherType = 'deferred';
                }
                $wallSwitcher.show();
                $wallSwitcher.find('a').hide();
                $wallSwitcher.find('a[data-type="' + showSwitcherType + '"]').show();
            }

            var userGroups = data.showUserGroups;
            $userGroupTabs.empty();
            $userGroupTabs.removeClass('hidden');
            $userGroupTabs.append('<div class="tab selected">Все новости</div>');
            if (userGroups) {
                for (var i in userGroups) {
                    var userGroupModel = new UserGroupModel();
                    userGroupModel.id(userGroups[i]['id']);
                    userGroupModel.name(userGroups[i]['name']);
                    userGroupCollection.add(userGroupModel.id(), userGroupModel);
                    $userGroupTabs.append('<div class="tab" data-user-group-id="' + userGroups[i]['id'] + '">' + userGroups[i]['name'] + '</div>');
                }
            }
        } else {
            $userGroupTabs.addClass('hidden');
        }

        if (sourceType == App.FEED_TYPE_ALBUMS) {
            if (data.authorsFilters && (data.authorsFilters.all_my_filter || data.authorsFilters.article_status_filter)) {
                if (data.authorsFilters.all_my_filter) {
                    showSwitcherType = 'all';
                } else {
                    showSwitcherType = 'deferred';
                }

                $wallSwitcher.show();
                $wallSwitcher.find('a').hide();
                $wallSwitcher.find('a[data-type="' + showSwitcherType + '"]').show();
            }

            $leftPanel.addClass('albums');
            t.enableKeyboardDecision();
        } else {
            $leftPanel.removeClass('albums');
            t.disableKeyboardDecision();
        }

        $leftPanelTabs.children('.sourceType').each(function(i, item) {
            item = $(item);
            if ($.inArray(item.data('type'), sourceTypes) == -1) {
                item.hide();
            } else {
                item.show();
            }
        });

        $.cookie('sourceTypes' + targetFeedId, sourceType);
        articlesLoading = true;
        t.updateSlider(targetFeedId, sourceType);
        t.setMultiSelectData(data.sourceFeeds, targetFeedId);
        articlesLoading = false;
        t.loadArticles(true);
    },

    /**
     * Включение горячих клавиш для одобрения
     * и отклонения фоток в альбомах
     * @task 13636
     */
    enableKeyboardDecision: function() {
        var t = this;
        $(window).on('keydown.keyboardDecision', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch (String.fromCharCode(e.which).toLowerCase()) {
                    case 'd':
                        t.declineArticle(t.$leftPanel.find('.post:first').data('id'));
                        e.preventDefault();
                        break;
                    case 'a':
                        t.acceptArticle(t.$leftPanel.find('.post:first').data('id'));
                        e.preventDefault();
                        break;
                }
            }
        });
    },

    /**
     * Оключение горячих клавиш для одобрения
     * и отклонения фоток в альбомах
     * @task 13636
     */
    disableKeyboardDecision: function() {
        $(window).off('keydown.keyboardDecision');
    },

    /**
     * @param {string} url
     * @returns {string|null}
     */
    getPostIdByURL: function(url) {
        var match = url.match(/wall(-?\d+_\d+)/im);
        return match && match[1] ? match[1] : null;
    },

    getPostIdByURL_test: function() {
        var t = this;
        console.log('-3967881_12359' === t.getPostIdByURL('http://vk.com/feed?w=wall-3967881_12359'));
        console.log('-3967881_12359' === t.getPostIdByURL('http://vk.com/feed?w=wall-3967881_12359/all'));
        console.log('-3967881_12359' === t.getPostIdByURL('http://vk.com/wall-3967881_12359'));
        console.log('3967881_12359' === t.getPostIdByURL('http://vk.com/wall3967881_12359'));
    },

    /**
     * @param {{text: string, link: string, photos: Array, articleId: (number=), repostExternalId: number}} params
     * @returns {Deferred}
     */
    savePost: function(params) {
        var $sourceFeedIds = Elements.leftdd();
        var sourceFeedId;
        if ($sourceFeedIds.length != 1) {
            sourceFeedId = null;
        } else {
            sourceFeedId = $sourceFeedIds[0];
        }

        return Control.fire('post', {
            text: params.text,
            link: params.link,
            photos: params.photos,
            articleId: params.articleId,
            repostExternalId: params.repostExternalId,
            sourceFeedId: sourceFeedId,
            targetFeedId: Elements.rightdd(),
            userGroupId: Elements.getUserGroupId()
        });
    }
});
