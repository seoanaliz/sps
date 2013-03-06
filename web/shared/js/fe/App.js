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
            this.getLeftPanelWidget().loadArticles(clean);
        },

        updateRightPanelDropdown: function() {
            this.getRightPanelWidget().updateDropdown();
        },

        loadQueue: function() {
            this.getRightPanelWidget().loadQueue();
        },

        imageUploader: function(options) {
            if (!(options.$element instanceof jQuery)) {
                throw new TypeError('$element must be instance of jQuery');
            }

            if (!(options.$listElement instanceof jQuery)) {
                throw new TypeError('$listElement must be instance of jQuery');
            }

            var element = options.$element[0];
            var listElement = options.$listElement[0];

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
                onComplete: function(id, fileName, response) {
                    var $attachmentNode = $(options.$listElement.find('> .attachment')[id]);
                    $attachmentNode.data('filename', response.filename);
                    $attachmentNode.data('image', response.image);
                    $attachmentNode.html('<img src="' + response.image + '" /><div class="delete-attach" title="Удалить"></div>');
                }
            }, options));

            options.$listElement.delegate('.delete-attach', 'click', function() {
                $(this).closest('.attachment').remove();
            });

            return {
                getFiles: function() {
                    var photos = [];
                    options.$listElement.find('> .attachment').each(function(){
                        photos.push({ filename: $(this).data('filename') });
                    });
                    return photos;
                }
            }
        }
    });

    App.ARTICLE_STATUS_REVIEWING = 1;
    App.ARTICLE_STATUS_APPROVED = 2;
    App.ARTICLE_STATUS_REJECTED = 3;

    return App;
})();
