var articlesLoading = false;
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


function popupSuccess( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#EBF0DA url('  + root +  'shared/images/vt/ui/icon_v.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupError( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FEDADA url('  + root +  'shared/images/vt/ui/icon_x.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

function popupNotice( message ) {
    $.blockUI({
        message: message,
        fadeIn: 600,
        fadeOut: 1000,
        timeout: 2500,
        showOverlay: false,
        centerY: false,
        css: {
            width: 'auto',
            'max-width': '200px',
            top: '15px',
            left: 'auto',
            right: '15px',
            border: 'none',
            padding: '25px 30px 25px 60px',
            'font-size': '13px',
            'text-align': 'left',
            color: '#333',
            'background': '#FBFFBF url('  + root +  'shared/images/vt/ui/icon_i.png) no-repeat 25px 50%',
            'border-radius': '5px',
            opacity: 1,
            'box-shadow': '0 0 6px #000'
        }
    });
}

var App = Event.extend({
    init: function() {
        this.initRightPanel();
        this.initGoToTop();
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

    initRightPanel: function() {
        this.getLeftPanelWidget();
        this.getRightPanelWidget();
    },

    getLeftPanelWidget: function() {
        return this.leftPanelWidget || (this.leftPanelWidget = new LeftPanelWidget());
    },

    getRightPanelWidget: function() {
        return this.rightPanelWidget || (this.rightPanelWidget = new RightPanelWidget());
    },

    initSlider: function(targetFeedId, sourceType) {
        this.getLeftPanelWidget().initSlider(targetFeedId, sourceType);
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

    onLeftPanelDropdownChange: function() {
        this.getLeftPanelWidget().onDropdownChange();
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

$(document).ready(function() {
    window.app = new App();
});
