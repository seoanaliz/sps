var KEY = window.KEY = {
    LEFT: 37,
    UP: 38,
    RIGHT: 39,
    DOWN: 40,
    DEL: 8,
    TAB: 9,
    RETURN: 13,
    ENTER: 13,
    ESC: 27,
    PAGEUP: 33,
    PAGEDOWN: 34,
    SPACE: 32
};

if (!window.localStorage) {
    window.localStorage = {
        getItem: function(key) {},
        setItem: function(key, value) {}
    };
}

function intval(str) {
    return isNaN(parseInt(str)) ? 0 : parseInt(str);
}

// Парсинг URL
function getURLParameter(name, search) {
    search = search || location.search;
    return decodeURIComponent((new RegExp(name + '=' + '(.+?)(&|$)').exec(search)||[,null])[1]);
}

(function($) {
    // Выделение текста в инпутах
    $.fn.selectRange = function(start, end) {
        return this.each(function() {
            if (this.setSelectionRange) {
                this.focus();
                this.setSelectionRange(start, end);
            } else if (this.createTextRange) {
                var range = this.createTextRange();
                range.collapse(true);
                range.moveEnd('character', end);
                range.moveStart('character', start);
                range.select();
            }
        });
    };

    // Добавляет триггер destroyed
    var oldClean = jQuery.cleanData;
    $.cleanData = function(elems) {
        for (var i = 0, elem; (elem = elems[i]) !== undefined; i++) {
            $(elem).triggerHandler('destroyed');
        }
        oldClean(elems);
    };
})(jQuery);

// Кроссбраузерные плейсхолдеры
(function($) {
    $.fn.placeholder = function(parameters) {
        return this.each(function() {
            var defaults = {
                el: this,
                text: false,
                hide: true,
                helperClass: 'placeholder'
            };
            var t = this;
            var settings = $.extend(defaults, parameters);
            var $input = $(settings.el);
            var placeholderText = settings.text || $input.attr('placeholder');
            var $wrapper = $('<div/>');
            var $placeholder = $('<div/>').addClass(settings.helperClass).text(placeholderText).css({
                position: 'absolute',
                cursor: 'text',
                font: $input.css('font'),
                margin: $input.css('border-width'),
                lineHeight: $input.css('line-height'),
                paddingTop: $input.css('padding-top'),
                paddingLeft: $input.css('padding-left'),
                paddingRight: $input.css('padding-right'),
                paddingBottom: $input.css('padding-bottom')
            });

            var placeholderHide = function() {
                if (settings.hide) {
                    $placeholder.hide();
                } else {
                    $placeholder.stop(true).animate({opacity: 0.5}, 200);
                }
            };

            var placeholderShow = function() {
                if (settings.hide) {
                    $placeholder.show();
                } else {
                    $placeholder.stop(true).animate({opacity: 1}, 200);
                }
            };

            $input
                .wrap($wrapper)
                .data('placeholder', $placeholder)
                .removeAttr('placeholder')
                .parent().prepend($placeholder)
            ;
            $placeholder.on('mouseup', function() {
                placeholderHide();
                $input.focus();
            });
            $input.on('blur change', function() {
                if (!$input.val()) {
                    placeholderShow();
                }
            });
            $input.on('focus change', function() {
                placeholderHide();
            });
            $input.on('destroyed', function() {
                $placeholder.remove();
            });
            if (!settings.hide) {
                $input.on('keyup keydown', function() {
                    setTimeout(function() {
                        if ($input.val()) {
                            $placeholder.hide();
                        } else {
                            $placeholder.show();
                        }
                    }, 0);
                });
            }
        });
    };
})(jQuery);

// Автовысота у textarea
(function($) {
    $.fn.autoResize = function() {
        return this.each(function() {
            var $input = $(this);
            var $autoResize = $('<div/>').appendTo('body');
            if (!$input.data('autoResize')) {
                $input.data('autoResize', $autoResize);
                $autoResize
                    .css({
                        position: 'absolute',
                        width: $input.width(),
                        minHeight: $input.height(),
                        font: $input.css('font'),
                        padding: $input.css('padding'),
                        fontSize: $input.css('font-size'),
                        wordWrap: 'break-word',
                        overflow: $input.css('overflow'),
                        lineHeight: $input.css('line-height'),
                        top: -100000
                    })
                ;

                $input.on('keyup keydown focus blur', function(e) {
                    var minHeight = intval($input.css('min-height'));
                    var maxHeight = intval($input.css('max-height'));
                    var val = $input.val().split('\n').join('<br/>.');
                    if (e.type == 'keydown' && e.keyCode == KEY.ENTER && !e.ctrlKey) {
                        val += '<br/>.';
                    }
                    $autoResize.html(val);
                    $input.css({
                        height: Math.max(
                            minHeight,
                            maxHeight ? Math.min(maxHeight, $autoResize.height()) : $autoResize.height()
                         )
                    });
                });
                $input.on('destroyed', function() {
                    $autoResize.remove();
                });

                $input.keyup();
            }
        });
    };
})(jQuery);

// Композиция картинок
(function($) {
    var PLUGIN_NAME = 'imageComposition';
    var DATA_KEY = PLUGIN_NAME;
    var CLASS_LOADING = 'image-compositing';
    var VER = 'ver';
    var HOR = 'hor';

    var methods = {
        init: function(hardPosition) {
            return this.each(function() {
                var position = (hardPosition == 'right') ? VER : (hardPosition == 'bottom' ? HOR : false);
                var $wrap = $(this);
                var $images = $wrap.find('img');
                var imagesNum = $images.length;
                var imagesSizes = [];
                var imagesPerColumn = 5;
                var $firstImage;
                var firstImageSize;
                var firstImageWidth;
                var firstImageHeight;

                var columns = [];
                var wrap = {
                    width: 0,
                    height: 0,
                    maxWidth: $wrap.width(),
                    maxHeight: $wrap.height()
                };

                if ($wrap.data(DATA_KEY) || !imagesNum) return;

                $wrap.data(DATA_KEY, true);
                $wrap.addClass(CLASS_LOADING);

                (function loadImage(i) {
                    var img = new Image();
                    var src = $($images[i]).attr('src');

                    img.onload = function() {
                        imagesSizes.push([img.width, img.height]);
                        if (i == imagesNum - 1) {
                            return onLoadImages();
                        } else {
                            loadImage(i + 1);
                        }
                    };
                    img.src = src;
                })(0);

                function onLoadImages() {
                    if ((imagesNum - 1) % imagesPerColumn == 1) {
                        imagesPerColumn++;
                    } else if ((imagesNum - 1) % imagesPerColumn == 2) {
                        imagesPerColumn--;
                    }
                    if (imagesNum == 2 && !position) {
                        position = VER;
                    }

                    (function() {
                        var imageIndex = 0;
                        var $image = $($images[imageIndex]);
                        var imageWidth = imagesSizes[imageIndex][0];
                        var imageHeight = imagesSizes[imageIndex][1];

                        $firstImage = $image;
                        firstImageWidth = imageWidth;
                        firstImageHeight = imageHeight;
                        firstImageSize = [firstImageWidth, firstImageHeight];

                        if (!position) {
                            position = isHor([firstImageWidth, firstImageHeight]) ? HOR : VER;
                        }

                        if (position == HOR) {
                            firstImageSize = relativeResize(firstImageSize, 'width', Math.min(imageWidth, wrap.maxWidth));
                        } else {
                            firstImageSize = relativeResize(firstImageSize, 'height', Math.min(imageHeight, wrap.maxHeight));
                        }
                        firstImageWidth = firstImageSize[0];
                        firstImageHeight = firstImageSize[1];

                        wrap.width = firstImageWidth;
                        wrap.height = firstImageHeight;
                    })();

                    if (imagesNum <= 1) {
                        return onComplete();
                    }

                    (function() {
                        for (var imageIndex = 1; imageIndex < imagesNum; imageIndex++) {
                            var $image = $($images[imageIndex]);
                            var imageSize = imagesSizes[imageIndex];
                            var imageWidth = imageSize[0];
                            var imageHeight = imageSize[1];
                            var columnIndex = Math.floor((imageIndex - 1) / (position == HOR ? imagesPerColumn : 99));
                            var column = columns[columnIndex];

                            if (!columns[columnIndex]) {
                                column = columns[columnIndex] = {
                                    images: [],
                                    width: 0,
                                    height: 0
                                };
                            }

                            if (position == HOR) {
                                imageSize = relativeResize(imageSize, 'height', 100);
                                column.width += imageSize[0];
                                column.height = imageSize[1];
                            } else {
                                imageSize = relativeResize(imageSize, 'width', 100);
                                column.width = imageSize[0];
                                column.height += imageSize[1];
                            }
                            imageWidth = imageSize[0];
                            imageHeight = imageSize[1];

                            column.images.push({
                                el: $image,
                                width: imageWidth,
                                height: imageHeight
                            });
                        }
                    })();

                    (function() {
                        for (var columnIndex = 0; columnIndex < columns.length; columnIndex++) {
                            var column = columns[columnIndex];
                            var columnSize = [column.width, column.height];
                            var images = column.images;

                            if (position == HOR) {
                                columnSize = relativeResize(columnSize, 'width', wrap.width);
                                wrap.height += columnSize[1];
                            } else {
                                columnSize = relativeResize(columnSize, 'height', wrap.height);
                                wrap.width += columnSize[0];
                            }
                            column.width = columnSize[0];
                            column.height = columnSize[1];

                            for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
                                var image = images[imageIndex];
                                var imageSize = [image.width, image.height];

                                if (position == HOR) {
                                    imageSize = relativeResize(imageSize, 'height', column.height);
                                } else {
                                    imageSize = relativeResize(imageSize, 'width', column.width);
                                }
                                image.width = imageSize[0];
                                image.height = imageSize[1];
                            }
                        }
                    })();

                    (function() {
                        var coef = 1;

                        if (position == HOR && wrap.height > wrap.maxHeight) {
                            coef = wrap.maxHeight / wrap.height;
                        } else if (wrap.width > wrap.maxWidth) {
                            coef = wrap.maxWidth / wrap.width;
                        }

                        wrap.width *= coef;
                        wrap.height *= coef;
                        firstImageWidth *= coef;
                        firstImageHeight *= coef;

                        for (var columnIndex = 0; columnIndex < columns.length; columnIndex++) {
                            var column = columns[columnIndex];
                            var images = column.images;
                            column.width *= coef;
                            column.height *= coef;

                            for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
                                var image = images[imageIndex];
                                var $image = image.el;
                                image.width *= coef;
                                image.height *= coef;

                                $image.width(image.width);
                                $image.height(image.height);
                            }
                        }

                        onComplete();
                    })();
                }

                function onComplete() {
                    $wrap.width(wrap.width + 2);
                    $wrap.height(wrap.height + 2);
                    $firstImage.width(firstImageWidth);
                    $firstImage.height(firstImageHeight);
                    $wrap.removeClass(CLASS_LOADING);
                }
            });
        }
    };

    function relativeResize(size, type, width) {
        var w = (type == 'width') ? 0 : 1;
        var h = (type == 'height') ? 0 : 1;
        var coef = size[w] / size[h];

        size[w] = width;
        size[h] = size[w] / coef;

        return size;
    }

    function isHor(sizes) {
        return !!(sizes[0] / sizes[1] > 1.1);
    }

    function isVer($image) {
        return !isHor($image);
    }

    $.fn[PLUGIN_NAME] = function(method) {
        return methods.init.apply(this, arguments);
    };
})(jQuery);

// Попап
var Box = (function() {
    var $body;
    var $layout;
    var boxesCollection = {};
    var boxesHistory = [];

    return function(options) {
        if (typeof options != 'object') {
            if (boxesCollection[options]) {
                return boxesCollection[options];
            } else {
                return false;
            }
        }

        var box = {};
        var params = $.extend({
            id: false,
            title: '',
            html: '',
            closeBtn: true,
            buttons: [],
            onshow: function() {},
            onhide: function() {},
            oncreate: function() {}
        }, options);

        if (!$layout) {
            $body = $('body');
            $body.data('overflow-y', $body.css('overflow-y'));
            $layout = $('<div/>')
                .addClass('box-layout')
                .appendTo($body)
                .click(function(e) {
                    if (e.target == e.currentTarget) {
                        boxesHistory[boxesHistory.length-1].hide();
                        if (!boxesHistory.length) {
                            $(this).hide();
                            //$body.css({overflowY: $body.data('overflow-y'), paddingRight: 0});
                        }
                    }
                })
            ;
            $(document).keydown(function(e) {
                if (e.keyCode == 27) {
                    $layout.click();
                }
            });
        } else {
            $layout = $('body > .box-layout');
        }

        if (params.id) {
            if (boxesCollection[params.id]) {
                return boxesCollection[params.id];
            } else {
                boxesCollection[params.id] = box;
            }
        }

        var $box = $(tmpl(BOX_WRAP, {
            title: params.title,
            body: '',
            closeBtn: params.closeBtn
        })).appendTo($layout).hide();

        if (params.closeBtn) {
            $box.find('> .title').click(function() {
                box.hide();
            });
        }

        setHTML(params.html);
        setButtons(params.buttons);

        box.$box = $box;
        box.show = show;
        box.hide = hide;
        box.setHTML = setHTML;
        box.setTitle = setTitle;
        box.setButtons = setButtons;
        box.refreshTop = refreshTop;

        function show() {
            if (boxesHistory.length) {
                boxesHistory[boxesHistory.length-1].$box.hide();
            }

            $box.show();
            $layout.show();
            //$body.css({overflowY: 'hidden', paddingRight: 17});
            refreshTop();

            try {
                params.onshow.call(box, $box);
            } catch(e) {
                //console.log(e);
            }

            boxesHistory.push(box);
            return box;
        }
        function hide() {
            $box.hide();

            try {
                params.onhide.call(box, $box);
            } catch(e) {
                //console.log(e);
            }

            boxesHistory.pop();
            if (boxesHistory.length) {
                boxesHistory[boxesHistory.length-1].$box.show();
            } else {
                $layout.hide();
            }

            return box;
        }
        function setHTML(html) {
            $box.find('> .body').html(html);
            refreshTop();
            return box;
        }
        function setTitle(title) {
            $box.find('> .title .text').text(title);
            return box;
        }
        function setButtons(buttons) {
            if (!buttons || !buttons.length) {
                $box.find('> .actions-wrap').remove();
            } else {
                if (!$box.find('> .actions-wrap').length) {
                    $box.append('<div class="actions-wrap"><div class="actions"></div></div>');
                }
                $box.find('> .actions-wrap .actions').empty();
                $.each(buttons, function(i, button) {
                    var $button = $(tmpl(BOX_ACTION, button))
                        .appendTo($box.find('> .actions-wrap .actions'))
                        .click(function() {
                            button.onclick ? button.onclick.call(box, $button, $box) : box.hide();
                        });
                });
            }
            return box;
        }
        function refreshTop() {
            var top = ($(window).height() / 3) - ($box.height() / 2);
            $box.css({
                marginTop: top < 20 ? 20 : top
            });
            return box;
        }

        params.oncreate.call(box, $box);

        return box;
    };
})();

// Дропдауны
(function($) {
    var PLUGIN_NAME = 'dropdown';
    var DATA_KEY = PLUGIN_NAME;
    var EVENTS_NAMESPACE = PLUGIN_NAME;
    var TYPE_NORMAL = 'normal';
    var TYPE_RADIO = 'radio';
    var TYPE_CHECKBOX = 'checkbox';
    var CLASS_ACTIVE = 'active';
    var CLASS_MENU = 'ui-dropdown-menu';
    var CLASS_EMPTY_MENU = 'ui-dropdown-menu-empty';
    var CLASS_ITEM = 'ui-dropdown-menu-item';
    var CLASS_ITEM_HOVER = 'hover';
    var CLASS_ITEM_ACTIVE = 'active';
    var CLASS_ITEM_WITH_ICON_LEFT = 'icon-left';
    var CLASS_ITEM_WITH_ICON_RIGHT = 'icon-right';
    var CLASS_ICON = 'icon';
    var CLASS_ICON_LEFT = 'icon-left';
    var CLASS_ICON_RIGHT = 'icon-right';
    var TRIGGER_OPEN = 'open';
    var TRIGGER_CLOSE = 'close';
    var TRIGGER_CHANGE = 'change';
    var TRIGGER_CREATE = 'create';
    var TRIGGER_UPDATE = 'update';

    var methods = {
        init: function(parameters) {
            return this.each(function() {
                var defaults = {
                    target: $(this), // На какой элемент навесить меню
                    type: 'normal', // normal, checkbox, radio
                    width: '', // Ширина меню
                    isShow: false, // Показать при создании
                    addClass: '', // Добавить уникальный класс к меню
                    position: 'left', // Выравнивание: left, right
                    iconPosition: 'left', // Сторона расположения иконки
                    openEvent: 'mousedown', // Собитие элемента, при котором открывается меню. click, mousedown
                    closeEvent: 'mousedown', // Собитие document при котором закрывается меню. click, mousedown
                    itemDataKey: 'item', // Ключ привязки данных к пункту меню
                    emptyMenuText: '', // Текст, когда в меню нет ни одного пункта
                    data: [{}], // Список пунктов. Пример: {title: '', icon: '', isActive: true, anyParameter: {}}
                    // На все события можно подписаться по имени события. Пример: $dropdown.on('change', callback)
                    oncreate: function() {},
                    onupdate: function() {},
                    onchange: function() {},
                    onselect: function() {},
                    onunselect: function() {},
                    onopen: function() {},
                    onclose: function() {}
                };
                var options = $.extend(defaults, parameters);
                var $el = $(this);
                var $menu = $('<div/>').addClass(CLASS_MENU + ' ' + options.addClass).appendTo('body');
                var $target = options.target;
                var isUpdate = false;

                if ($el.data(DATA_KEY)) {
                    $el.dropdown('getMenu').remove();
                    isUpdate = true;
                } else {
                    $(window).on('resize.' + EVENTS_NAMESPACE, function(e) {
                        if (!$el.data(DATA_KEY)) return $(this).off(e.type + '.' + EVENTS_NAMESPACE);
                        var $menu = $el.dropdown('getMenu');
                        if ($menu.is(':visible')) {
                            $el.dropdown('refreshPosition');
                        }
                    });
                    $(document).on(options.closeEvent + '.' + EVENTS_NAMESPACE, function(e) {
                        if (!$el.data(DATA_KEY)) return $(this).off(options.closeEvent + '.' + EVENTS_NAMESPACE);
                        var $menu = $el.dropdown('getMenu');
                        $el.dropdown('close');
                        run(options.onclose, $el, $menu);
                    });
                    $(document).on('keydown.' + EVENTS_NAMESPACE, function(e) {
                        if (!$el.data(DATA_KEY)) return $(this).off(e.type + '.' + EVENTS_NAMESPACE);
                        var $menu = $el.dropdown('getMenu');
                        if ($menu.is(':visible')) {
                            var $hoveringItem = $menu.find('.' + CLASS_ITEM + '.' + CLASS_ITEM_HOVER);

                            switch(e.keyCode) {
                                case KEY.UP:
                                case KEY.DOWN:
                                    var $hoverItem;
                                    if (e.keyCode == KEY.UP) {
                                        $hoverItem = $hoveringItem.prev('.' + CLASS_ITEM);
                                    } else if (e.keyCode == KEY.DOWN) {
                                        $hoverItem = $hoveringItem.next('.' + CLASS_ITEM);
                                    }
                                    if (!$hoveringItem.length || !$hoverItem.length) {
                                        if (e.keyCode == KEY.UP) {
                                            $hoverItem = $menu.find('.' + CLASS_ITEM + ':last');
                                        } else if (e.keyCode == KEY.DOWN) {
                                            $hoverItem = $menu.find('.' + CLASS_ITEM + ':first');
                                        }
                                    }

                                    if ($hoverItem.length) {
                                        $hoveringItem.removeClass(CLASS_ITEM_HOVER);
                                        $hoverItem.addClass(CLASS_ITEM_HOVER);
                                        var positionTop = $hoverItem.position().top;
                                        var scrollTop = $menu.scrollTop() + positionTop;
                                        if (positionTop + $hoverItem.height() > $menu.height()) {
                                            $menu.scrollTop(scrollTop);
                                        } else if (positionTop < 0) {
                                            $menu.scrollTop(scrollTop - $menu.outerHeight() + $hoverItem.outerHeight());
                                        }
                                        return false;
                                    }
                                break;
                                case KEY.TAB:
                                    $el.dropdown('close');
                                    return true;
                                break;
                                case KEY.ENTER:
                                    if ($hoveringItem.length) {
                                        select($hoveringItem);
                                    }
                                    return false;
                                break;
                                case KEY.ESC:
                                    $el.dropdown('close');
                                    return false;
                                break;
                            }
                        }
                    });
                    $el.on(options.openEvent, function(e) {
                        if (e.originalEvent && e.type == 'mousedown' && e.button != 0) return;
                        e.stopPropagation();
                        var $menu = $el.dropdown('getMenu');
                        if (!$menu.is(':visible')) {
                            $(document).trigger(options.closeEvent);
                            $el.dropdown('open');
                        } else if (e.button != undefined) {
                            $el.dropdown('close');
                        }
                    });
                    $el.on('destroyed', function() {
                        $el.dropdown('getMenu').remove();
                    });
                }

                if (!$.isArray(options.data)) options.data = [];
                if (options.data.length || !options.emptyMenuText) {
                    $(options.data).each(function(i, item) {
                        var $item = $('<div/>')
                            .text(item.title)
                            .addClass(CLASS_ITEM)
                            .attr('data-id', item.id)
                            .data(options.itemDataKey, item)
                            .appendTo($menu)
                        ;
                        if (item.icon) {
                            var $icon = $('<div><img src="' + item.icon + '" /></div>');
                            $item.append($icon);
                            if (options.iconPosition == 'left') {
                                $icon.attr({'class': CLASS_ICON + ' ' + CLASS_ICON_LEFT});
                                $item.addClass(CLASS_ITEM_WITH_ICON_LEFT);
                            } else {
                                $icon.attr({'class': CLASS_ICON + ' ' + CLASS_ICON_RIGHT});
                                $item.addClass(CLASS_ITEM_WITH_ICON_RIGHT);
                            }
                        }
                        if (item.isActive) {
                            $item.addClass(CLASS_ITEM_ACTIVE);
                        }
                        $item.hover(function() {
                            var $activeItem = $menu.find('.' + CLASS_ITEM + '.' + CLASS_ITEM_HOVER);
                            $activeItem.removeClass(CLASS_ITEM_HOVER);
                            $(this).addClass(CLASS_ITEM_HOVER);
                        }, function() {
                            var $activeItem = $menu.find('.' + CLASS_ITEM + '.' + CLASS_ITEM_HOVER);
                            $activeItem.removeClass(CLASS_ITEM_HOVER);
                            $(this).removeClass(CLASS_ITEM_HOVER);
                        });
                    });
                } else {
                    $('<div/>')
                        .text(options.emptyMenuText)
                        .addClass(CLASS_EMPTY_MENU)
                        .appendTo($menu)
                    ;
                }

                $menu.delegate('.' + CLASS_ITEM, 'mouseup', function(e) {
                    if (e.originalEvent && e.button != 0) return;
                    select($(this));
                });
                $menu.on(options.openEvent, function(e) {
                    e.stopPropagation();
                });
                $menu.hide();

                function select($item) {
                    var data = $item.data(options.itemDataKey);
                    switch(options.type) {
                        case TYPE_RADIO:
                            $menu.find('.' + CLASS_ITEM).removeClass(CLASS_ITEM_ACTIVE);
                            $item.addClass(CLASS_ITEM_ACTIVE);
                        break;
                        case TYPE_CHECKBOX:
                            $item.toggleClass(CLASS_ITEM_ACTIVE);
                        break;
                    }
                    $el.dropdown('close');
                    run(options.onchange, $el, data);
                    run(($item.hasClass(CLASS_ITEM_ACTIVE) ? options.onselect : options.onunselect), $el, data);
                    $el.trigger(TRIGGER_CHANGE);
                }

                $el.data(DATA_KEY, {
                    $el: $el,
                    $menu: $menu,
                    $target: $target,
                    options: options
                });

                if (options.isShow && $el.is(':visible')) {
                    $el.dropdown('open');
                }
                if (isUpdate) {
                    run(options.onupdate, $el);
                    $el.trigger(TRIGGER_UPDATE);
                } else {
                    run(options.oncreate, $el);
                    $el.trigger(TRIGGER_CREATE);
                }
            });
        },
        open: function(notTrigger) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                var $target = data.$target;

                $menu.css({
                    width: options.width || $target.outerWidth() - 2
                });

                $el.dropdown('refreshPosition');
                $menu.show();

                if (!notTrigger) {
                    run(options.onopen, $el, $menu);
                    $el.trigger(TRIGGER_OPEN);
                }
            });
        },
        close: function(notTrigger) {
            var $el = $(this);
            var data = $el.data(DATA_KEY);
            var options = data.options;
            var $menu = data.$menu;
            var $target = data.$target;

            $target.removeClass(CLASS_ACTIVE);
            $menu.hide();

            if (!notTrigger) {
                run(options.onclose, $el, $menu);
                $el.trigger(TRIGGER_CLOSE);
            }
        },
        refreshPosition: function() {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                var $target = data.$target;
                var isFixed = !!($menu.css('position') == 'fixed');
                var offset = $target.offset();
                var offsetTop = offset.top;
                var offsetLeft = offset.left
                    + parseFloat($menu.css('margin-left'))
                    - parseFloat($menu.css('margin-right'));
                if (options.position == 'right') {
                    offsetLeft += ($target.width() - $menu.width())
                }
                if (isFixed) {
                    offsetTop -= $(document).scrollTop();
                    offsetLeft -= $(document).scrollLeft();
                }

                $menu.css({
                    top: offsetTop + $target.outerHeight(),
                    left: offsetLeft
                });
            });
        },
        getMenu: function() {
            return this.data(DATA_KEY).$menu;
        },
        getTarget: function() {
            return this.data(DATA_KEY).$target;
        },
        getItem: function(id) {
            return this.data(DATA_KEY).$menu.find('.' + CLASS_ITEM + '[data-id="' + id + '"]');
        },
        appendItem: function() {

        }
    };

    function run(f, context, argument) {
        if ($.isFunction(f)) {
            f.call(context, argument);
        }
    }

    $.fn[PLUGIN_NAME] = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.' + PLUGIN_NAME);
        }
    };
})(jQuery);

(function($) {
    var PLUGIN_NAME = 'autocomplete';
    var DATA_KEY = PLUGIN_NAME;

    var methods = {
        init: function(params) {
            return this.each(function() {
                var defaults = {
                    openEvent: 'mousedown',
                    emptyMenuText: 'Ничего не найдено'
                };
                var options = $.extend(defaults, params);
                var $el = $(this);
                var defData = options.data.slice(0);
                var searchTimeout;

                $el.dropdown(options);

                if (!$el.data(DATA_KEY)) {
                    $el.on('keyup', function(e) {
                        switch(e.keyCode) {
                            case KEY.UP:
                            case KEY.DOWN:
                                if ($el.dropdown('getMenu').is(':visible')) {
                                    return true;
                                }
                            break;
                            case KEY.ESC:
                            case KEY.TAB:
                            case KEY.SHIFT:
                            case KEY.ENTER:
                                return true;
                            break;
                        }
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            $el.dropdown($.extend(options, {
                                isShow: $el.is(':focus'),
                                data: !$el.val() ? defData : $.grep(defData, function(n, i) {
                                    var str = $.trim(n.title).toLowerCase().split('ё').join('е');
                                    var searchStr = $.trim($el.val()).toLowerCase().split('ё').join('е');
                                    return !!(str.indexOf(searchStr) !== -1);
                                })
                            }));
                        }, 0);
                    });

                    $el.data(DATA_KEY, {
                        $el: $el,
                        options: options
                    });
                }
            });
        }
    };

    $.fn[PLUGIN_NAME] = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.' + PLUGIN_NAME);
        }
    };
})(jQuery);

(function($) {
    var PLUGIN_NAME = 'tags';
    var DATA_KEY = PLUGIN_NAME;
    var CLASS_WRAP = 'ui-tags';
    var CLASS_TAG = 'tag';
    var CLASS_TAG_TEXT = 'text';
    var CLASS_TAG_DELETE = 'delete';

    var methods = {
        init: function(params) {
            return this.each(function() {
                var defaults = {
                    oncreate: function() {},
                    onadd: function() {},
                    onremove: function() {}
                };
                var $el = $(this);
                var options = $.extend(defaults, params);

                $el.wrap($('<div/>').addClass(CLASS_WRAP).css({
                    border: $el.css('border'),
                    margin: $el.css('margin'),
                    background: $el.css('background'),
                    width: $el.outerWidth() - (parseInt($el.css('border-width')) * 2)
                }));

                $el.css({
                    border: '0',
                    margin: '0',
                    background: 'none',
                    backgroundColor: 'transparent'
                });

                $el.data(DATA_KEY, {
                    $el: $el,
                    $wrap: $el.closest('.' + CLASS_WRAP),
                    options: options,
                    tags: {}
                });

                run(options.oncreate, $el);
            });
        },
        addTag: function(id, params) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $wrap = data.$wrap;
                var $tag = $wrap.find('.' + CLASS_TAG + '[data-id=' + id + ']');

                if ($tag.length) {
                    $tag.remove();
                }

                $tag = $(
                    '<span data-id="' + id + '" class="' + CLASS_TAG + '">' +
                        '<span class="' + CLASS_TAG_TEXT + '">' + params.title + '</span>' +
                        '<span class="' + CLASS_TAG_DELETE + '"></span>' +
                    '</span>')
                ;

                $tag.find('.delete').click(function() {
                        $tag.remove();
                        refreshPadding($el);

                        run(options.onremove, $el, id);
                    })
                ;

                $el.before($tag);
                $el.data(DATA_KEY, data);
                refreshPadding($el);

                run(options.onadd, $el, params);
            });
        },
        removeTag: function(id) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $wrap = data.$wrap;

                $wrap.find('.' + CLASS_TAG + '[data-id=' + id + ']').remove();
                refreshPadding($el);

                run(options.onremove, $el, id);
            });
        },
        removeLastTag: function() {
            var $el = $(this);
            var data = $el.data(DATA_KEY);
            var options = data.options;
            var $wrap = data.$wrap;
            var $tag = $wrap.find('.' + CLASS_TAG + ':last');

            $tag.remove();
            refreshPadding($el);

            run(options.onremove, $el, $tag.data('id'));
        }
    };

    function refreshPadding($el) {
        var data = $el.data(DATA_KEY);
        var $wrap = data.$wrap;
        var $lastTag = $wrap.find('.' + CLASS_TAG + ':last');
        var position = $lastTag.position();
        var left = position ? (position.left + $lastTag.outerWidth(true)) : 0;
        var width = $wrap.width() - parseInt($el.css('padding')) * 2;

        $el.css({
            width: (width - left) < 40 ? width : (width - left)
        });
    }

    function run(f, context, argument) {
        if ($.isFunction(f)) {
            f.call(context, argument);
        }
    }

    $.fn[PLUGIN_NAME] = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.' + PLUGIN_NAME);
        }
    };
})(jQuery);

// Счетчики
(function($) {
    var PLUGIN_NAME = 'counter';
    var DATA_KEY = PLUGIN_NAME;

    var methods = {
        init: function(params) {
            return this.each(function() {
                var defaults = {
                    nonNegative: true,
                    prefix: ''
                };
                var options = $.extend(defaults, params);
                var $el = $(this);

                $el.data(DATA_KEY, {
                    $el: $el,
                    counter: intval($el.html()),
                    options: options
                });
            });
        },
        getCounter: function() {
            return this.data(DATA_KEY).counter;
        },
        setCounter: function(num) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                num = intval(num);
                if (options.nonNegative && num < 0) {
                    num = 0;
                }

                $el.html(options.prefix + num);

                if (num) {
                    $el.show();
                } else {
                    $el.hide();
                }

                $el.data(DATA_KEY, $.extend(data, {
                    counter: num
                }));
            });
        },
        increment: function(num) {
            return this.each(function() {
                var $el = $(this);
                num = num || 1;
                $el.counter('setCounter', $el.counter('getCounter') + num);
            });
        },
        decrement: function(num) {
            return this.each(function() {
                var $el = $(this);
                num = num || 1;
                $el.counter('setCounter', $el.counter('getCounter') - num);
            });
        }
    };

    $.fn[PLUGIN_NAME] = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.' + PLUGIN_NAME);
        }
    };
})(jQuery);

// Запоминает введенный текст и вставляет при обновлении страницы
(function($) {
    var PLUGIN_NAME = 'inputMemory';

    var methods = {
        init: function(memoryKey) {
            return this.each(function() {
                if (!window.localStorage) return;

                var $input = $(this);
                var inputValue = $input.val();
                var storageValue = localStorage.getItem(memoryKey);

                if (storageValue) {
                    $input.val(storageValue);
                    $input.selectRange(storageValue.length, storageValue.length);
                } else {
                    localStorage.setItem(memoryKey, inputValue);
                }

                $input.on('keydown keyup keypress change blur', function() {
                    if (inputValue != $input.val()) {
                        inputValue = $input.val();
                        localStorage.setItem(memoryKey, inputValue);
                    }
                });
            });
        }
    };

    $.fn[PLUGIN_NAME] = function(method) {
        return methods.init.apply(this, arguments);
    };
})(jQuery);