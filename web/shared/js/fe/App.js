var App = (function() {
    var App = Event.extend({
        init: function() {
            var t = this;
            t.getLeftPanelWidget();
            t.getRightPanelWidget();
            t.initGoToTop();

            t.getRightPanelWidget().on('updateDropdown', function(data) {
                t.getLeftPanelWidget().dropdownChangeLeftPanel(data);
            });
        },

        // Кнопка "наверх"
        initGoToTop: function() {
            var $elem = $('#go-to-top');
            var $window = $(window);
            $elem.click(function() {
                $window.scrollTop(0);
            });
            $window.on('scroll', function(e) {
                if (e.currentTarget.scrollY <= 0) {
                    $elem.hide();
                } else if (!$elem.is(':visible')) {
                    $elem.show();
                }
            });
        },

        getLeftPanelWidget: function() {
            return this.leftPanelWidget || (this.leftPanelWidget = new LeftPanelWidget());
        },

        getRightPanelWidget: function() {
            return this.rightPanelWidget || (this.rightPanelWidget = new RightPanelWidget());
        },

        updateSlider: function(targetFeedId, sourceType) {
            this.getLeftPanelWidget().updateSlider(targetFeedId, sourceType);
        },

        changeSliderRange: function() {
            this.getLeftPanelWidget().changeSliderRange();
        },

        reloadArticle: function(id) {
            this.getLeftPanelWidget().reloadArticle(id);
        },

        loadArticles: function(clean) {
            return this.getLeftPanelWidget().loadArticles(clean);
        },

        updateRightPanelDropdown: function() {
            return this.getRightPanelWidget().updateDropdown();
        },

        updateQueue: function(timestamp) {
            return this.getRightPanelWidget().updateQueue(timestamp);
        },

        updateQueuePage: function($page) {
            return this.getRightPanelWidget().updateQueuePage($page);
        },

        imageUploader: function(options) {
            var $element = options.$element;
            var $listElement = options.$listElement;

            if (!($element instanceof jQuery)) {
                throw new TypeError('$element must be instance of jQuery');
            }

            if (!($listElement instanceof jQuery)) {
                throw new TypeError('$listElement must be instance of jQuery');
            }

            var element = $element[0];
            var listElement = $listElement ? $listElement[0] : undefined;
            var onComplete = function(id, fileName, response) {
                var $attachment = $($listElement.find('> .attachment:not(.attachment-handmade)')[id]);
                $attachment.data('data', response);
                $attachment.html('<img src="' + response.image + '" /><div class="delete-attachment" title="Удалить"></div>');
            };
            var getPhotos = function() {
                var photos = [];
                $listElement.find('> .attachment').each(function(){
                    photos.push($(this).data('data'));
                });
                return photos;
            };
            var addPhoto = function(image, data) {
                var $attachment = $('<div class="attachment photo attachment-handmade">' +
                '<img src="' + image + '" /><div class="delete-attachment" title="Удалить"></div>' +
                '</div>');
                $attachment.data('data', data);
                $listElement.append($attachment);
            };

            new qq.FileUploader($.extend({
                element: element,
                listElement: listElement,
                action: root + 'int/controls/image-upload/',
                template: '<div class="qq-uploader">' +
                '<div class="qq-upload-drop-area">+</div>' +
                '<a class="qq-upload-button">Прикрепить</a>' +
                '</div>',
                fileTemplate: '<div class="attachment">' +
                '<span class="qq-upload-file"></span>' +
                '<span class="qq-upload-spinner"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<a class="qq-upload-cancel">Отмена</a>' +
                '<span class="qq-upload-failed-text">Ошибка</span>' +
                '</div>',
                onComplete: onComplete
            }, options));

            $listElement.delegate('.delete-attachment', 'click', function() {
                $(this).closest('.attachment').remove();
            });

            return {
                getPhotos: function() {
                    return getPhotos.apply(this, arguments);
                },
                addPhoto: function(photoURL, filename) {
                    return addPhoto.apply(this, arguments);
                }
            }
        }
    });

    App.ARTICLE_STATUS_REVIEWING = 1;
    App.ARTICLE_STATUS_APPROVED = 2;
    App.ARTICLE_STATUS_REJECTED = 3;

    App.FEED_TYPE_MY = 'my';
    App.FEED_TYPE_ADS = 'ads';
    App.FEED_TYPE_SOURCE = 'source';
    App.FEED_TYPE_ALBUMS = 'albums';
    App.FEED_TYPE_AUTHORS = 'authors';
    App.FEED_TYPE_TOPFACE = 'topface';
    App.FEED_TYPE_AUTHORS_LIST = 'authors-list';

    return App;
})();
