// Парсинг URL
function getURLParameter(name) {
    return decodeURIComponent((new RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
}

// Кроссбраузерные плейсхолдеры
(function($) {
    $.fn.placeholder = function(para) {
        return this.each(function(parameters) {
            var defaults = {
                el: this,
                color: '#CCC',
                text: false,
                helperClass: 'placeholder'
            };
            var t = this;
            var p = t.p = $.extend(defaults, parameters);
            var $input = $(p.el);
            var placeholderText = p.text || $input.attr('placeholder');
            var $wrapper = $('<div/>');
            var $placeholder = $('<div/>').addClass(p.helperClass).text(placeholderText).css({
                padding: $input.css('padding')
            });

            $input
                .wrap($wrapper)
                .data('placeholder', $placeholder)
                .removeAttr('placeholder')
                .parent().prepend($placeholder)
            ;

            $placeholder.bind('mousedown', function() {
                $placeholder.hide();
                $input.focus();
            });
            $placeholder.bind('mouseup', function() {
                $input.focus();
            });
            $input.bind('blur change', function() {
                if (!$input.val()) {
                    $placeholder.fadeIn(100);
                }
            });
            $input.bind('focus change', function() {
                $placeholder.hide();
            });
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
                        width: $input.width(),
                        minHeight: $input.height(),
                        padding: $input.css('padding'),
                        lineHeight: $input.css('line-height'),
                        font: $input.css('font'),
                        fontSize: $input.css('font-size'),
                        position: 'absolute',
                        wordWrap: 'break-word',
                        top: -100000
                    })
                ;
                $input.bind('keyup focus blur', function(e) {
                    $autoResize.html($input.val().split('\n').join('<br/>.') + '<br/>.');
                    $input.css('height', $autoResize.height());
                });
            }
        });
    };
})(jQuery);

// Композиция картинок
(function($) {
    $.fn.imageComposition = function(hardPosition) {
        return this.each(function() {
            var CLASS_LOADING = 'image-compositing';
            var VER = 'ver', HOR = 'hor';
            hardPosition == 'right' ? VER : (hardPosition == 'bottom' ? HOR : false);

            var position = hardPosition;
            var $wrap = $(this);
            var $images = $wrap.find('img');
            var num = $images.length;
            var imagesPerColumn = 5;
            var wrap = {
                el: $wrap,
                width: 0,
                height: 0,
                maxWidth: $wrap.width(),
                maxHeight: $wrap.height(),
                type: ''
            };

            if ($wrap.data('image-compositing')) return;

            $wrap.data('image-compositing', true);
            $wrap.addClass(CLASS_LOADING);
            $images.each(function(i, image) {
                var $img = $(image);

                var img = new Image();
                img.onload = function() {
                    if (i == num - 1) {
                        return onLoadImages();
                    }
                };
                img.src = $img.attr('src');
            });

            // ======================== //
            function isHor($image) {
                var w = $image.width();
                var h = $image.height();
                return !!(w / h > 1.1);
            }

            function isVer($image) {
                return !isHor($image);
            }

            function relativeResize(size, type, width) {
                var w = (type == 'width') ? 'width' : 'height';
                var h = (type == 'height') ? 'width' : 'height';
                var coef = size[w] / size[h];

                size[w] = width;
                size[h] = size[w] / coef;

                return size;
            }

            function onLoadImages() {
                var $firstImage;
                var columns = [];

                if ((num - 1) % imagesPerColumn == 1) {
                    imagesPerColumn++;
                } else if ((num - 1) % imagesPerColumn == 2) {
                    imagesPerColumn--;
                }
                if (num == 2 && !hardPosition) {
                    position = VER;
                    wrap.width = wrap.maxWidth;
                    $firstImage = $wrap;
                }

                $images.each(function(i) {
                    var $image = $(this);

                    if (i == 0) {
                        $firstImage = $image;
                        if (!position && isHor($firstImage)) {
                            position = HOR;
                            $firstImage.width(Math.min(wrap.maxWidth, $firstImage.width()));
                            wrap.width = $firstImage.width();
                            $wrap.width(wrap.width);
                        } else {
                            position = VER;
                            $firstImage.height(Math.min(wrap.maxHeight, $firstImage.height()));
                            wrap.height = $firstImage.height();
                            $wrap.height(wrap.height);
                        }
                    } else {
                        var columnIndex = Math.floor((i - 1) / (position == HOR ? imagesPerColumn : 99));
                        if (!columns[columnIndex]) columns[columnIndex] = {};

                        var column = columns[columnIndex];
                        if (!column.images) column.images = [];
                        if (!column.columnHeight) column.columnHeight = 99999;
                        if (position == HOR) {
                            column.columnHeight = Math.min(column.columnHeight, $image.height());
                        } else {
                            column.columnHeight = Math.min(column.columnHeight, $image.width());
                        }
                        column.images.push($image);
                    }
                });

                $(columns).each(function(columnIndex, column) {
                    $(column.images).each(function(imageIndex, $image) {
                        if (!column.columnWidth) column.columnWidth = 0;
                        if (position == HOR) {
                            $image.height(column.columnHeight);
                            column.columnWidth += $image.width();
                        } else {
                            $image.width(column.columnHeight);
                            column.columnWidth += $image.height();
                        }
                    });
                });

                var columnsHeight = 0;
                $(columns).each(function(columnIndex, column) {
                    var coef = 0;
                    var columnHeight = 0;

                    if (position == HOR) {
                        coef = wrap.width / column.columnWidth;
                    } else {
                        coef = wrap.height / column.columnWidth;
                    }

                    $(column.images).each(function(imageIndex, $image) {
                        var s = {};
                        if (position == HOR) {
                            s = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'width', ($image.width() * coef));

                            $image.width(s['width']);
                            $image.height(s['height']);
                            columnHeight = s['height'];
                        } else {
                            s = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'height', ($image.height() * coef));

                            $image.width(s['width']);
                            $image.height(s['height']);
                            columnHeight = s['width'];
                        }
                    });
                    columnsHeight += columnHeight;
                });

                var coef;
                if (position == HOR) {
                    wrap.height = $firstImage.height() + columnsHeight;

                    if (wrap.height > wrap.maxHeight) {
                        coef = wrap.maxHeight / wrap.height;

                        $images.each(function(i) {
                            var $image = $(this);
                            var size = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'height', ($image.height() * coef));
                            $image.width(size['width']);
                            $image.height(size['height']);
                        });
                        wrap.width *= coef;
                        wrap.height *= coef;
                    }
                } else {
                    wrap.width = $firstImage.width() + columnsHeight;

                    if (wrap.width > wrap.maxWidth) {
                        coef = wrap.maxWidth / wrap.width;

                        $images.each(function(i) {
                            var $image = $(this);
                            var size = relativeResize(
                                {
                                    'width': $image.width(),
                                    'height': $image.height()
                                },
                                'width', ($image.width() * coef));
                            $image.width(size['width']);
                            $image.height(size['height']);
                        });
                        wrap.width *= coef;
                        wrap.height *= coef;
                    }
                }

                $wrap.width(wrap.width + 2);
                $wrap.height(wrap.height + 2);
                $wrap.removeClass(CLASS_LOADING);
            }
        });
    };
})(jQuery);

// Попап
var Box = (function() {
    var $body;
    var $layout;
    var boxesCollection = {};

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
            $layout = $('<div/>')
                .addClass('box-layout')
                .appendTo($body)
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
        })).appendTo($layout);

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
            $layout
                .show()
                .unbind()
                .click(function(e) {
                    if (e.target == e.currentTarget) box.hide();
                })
            ;
            $box.show();
            $body.data('overflow-y', $body.css('overflow-y')).css({overflowY: 'hidden', paddingRight: 17});
            try {
                params.onshow.call(box, $box);
            } catch(e) {
                console.log(e);
            }
            refreshTop();
            return box;
        }
        function hide() {
            $layout.hide();
            $box.hide();
            $body.css({overflowY: $body.data('overflow-y'), paddingRight: 0});
            params.onhide.call(box, $box);
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
    var CLASS_ACTIVE = 'active';
    var CLASS_MENU = 'ui-dropdown-menu';
    var CLASS_ITEM = 'ui-dropdown-menu-item';
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
                    el: $(this),
                    type: 'normal', // normal, checkbox
                    width: '',
                    isShow: false,
                    addClass: '', // Добавить уникальный класс к меню
                    position: 'left', // Выравнивание: left, right
                    iconPosition: 'left',
                    openEvent: 'mousedown', // Собитие элемента, при котором открывается меню. click, mousedown
                    closeEvent: 'mousedown', // Собитие 'html, body' при котором закрывается меню. click, mousedown
                    menuDataKey: 'dropdown', // Ключ записи jQuery.data привязки меню к элементу
                    itemDataKey: 'item',
                    data: [{}], // Список пунктов. Пример: {title: '', icon: '', isActive: true, anyParameter: {}}
                    // На все события можно подписаться по имени события. Пример: $dropdown.bind('change', callback)
                    oncreate: function() {},
                    onupdate: function() {},
                    onchange: function() {},
                    onopen: function() {},
                    onclose: function() {}
                };
                var t = this;
                var p = t.p = $.extend(defaults, parameters);
                var $el = p.el;
                var $menu = $('<div/>').addClass(CLASS_MENU + ' ' + p.addClass).appendTo('body');
                var isUpdate = false;

                if ($el.data(p.menuDataKey)) {
                    $el.data(p.menuDataKey).remove();
                    isUpdate = true;
                } else {
                    $('html, body').bind(p.closeEvent, function(e) {
                        var $menu = $el.data(p.menuDataKey);
                        close($menu);
                        run(p.onclose, $el, $menu);
                    });
                    $el.bind(p.openEvent, function(e) {
                        if (e.originalEvent && e.type == 'mousedown' && e.button != 0) return;
                        e.stopPropagation();
                        var $menu = $el.data(p.menuDataKey);
                        if (!$menu.is(':visible')) {
                            $('html, body').trigger(p.closeEvent);
                            open($menu);
                        } else if (e.button != undefined) {
                            close($menu);
                        }
                    });
                }
                $el.data(p.menuDataKey, $menu);

                if (!$.isArray(p.data)) p.data = [];
                $(p.data).each(function(i, item) {
                    var $item = $('<div/>')
                        .text(item.title)
                        .addClass(CLASS_ITEM)
                        .attr('data-id', item.id)
                        .data(p.itemDataKey, item)
                        .appendTo($menu)
                    ;
                    if (item.icon) {
                        var $icon = $('<div><img src="' + item.icon + '" /></div>');
                        $item.append($icon);
                        if (p.iconPosition == 'left') {
                            $icon.attr({class: CLASS_ICON + ' ' + CLASS_ICON_LEFT});
                            $item.addClass(CLASS_ITEM_WITH_ICON_LEFT);
                        } else {
                            $icon.attr({class: CLASS_ICON + ' ' + CLASS_ICON_RIGHT});
                            $item.addClass(CLASS_ITEM_WITH_ICON_RIGHT);
                        }
                    }
                    if (item.isActive) {
                        $item.addClass(CLASS_ITEM_ACTIVE);
                    }
                });

                $menu.delegate('.' + CLASS_ITEM, 'mouseup', function(e) {
                    if (e.originalEvent && e.button != 0) return;
                    close($menu);
                    select($(this));
                });
                $menu.bind(p.openEvent, function(e) {
                    e.stopPropagation();
                });

                function open($menu, notTrigger) {
                    $el.addClass(CLASS_ACTIVE);
                    $menu.css({
                        width: p.width || $el.outerWidth() - 2
                    });

                    var isFixed = !!($menu.css('position') == 'fixed');
                    var offset = $el.offset();
                    var offsetTop = offset.top;
                    var offsetLeft = offset.left
                        + parseFloat($menu.css('margin-left'))
                        - parseFloat($menu.css('margin-right'));
                    if (p.position == 'right') {
                        offsetLeft += ($el.width() - $menu.width())
                    }
                    if (isFixed) {
                        offsetTop -= $(document).scrollTop();
                        offsetLeft -= $(document).scrollLeft();
                    }

                    $menu.css({
                        top: offsetTop + $el.outerHeight(),
                        left: offsetLeft
                    }).show();

                    if (notTrigger) {
                        run(p.onopen, $el, $menu);
                        $el.trigger(TRIGGER_OPEN);
                    }
                }

                function close($menu, notTrigger) {
                    $el.removeClass(CLASS_ACTIVE);
                    $menu.hide();

                    if (notTrigger) {
                        run(p.onclose, $el, $menu);
                        $el.trigger(TRIGGER_CLOSE);
                    }
                }

                function select($item) {
                    var data = $item.data(p.itemDataKey);
                    if (p.type == 'checkbox') {
                        $menu.find('.' + CLASS_ITEM).removeClass(CLASS_ITEM_ACTIVE);
                        $item.addClass(CLASS_ITEM_ACTIVE);
                    }
                    run(p.onchange, $el, data);
                    $el.trigger(TRIGGER_CHANGE);
                }

                if (isUpdate) {
                    run(p.onupdate, $el);
                    $el.trigger(TRIGGER_UPDATE);
                } else {
                    run(p.oncreate, $el);
                    $el.trigger(TRIGGER_CREATE);
                }

                if (p.isShow) {
                    open($menu, true);
                } else {
                    close($menu, true);
                }
            });

            function run(f, context, argument) {
                if ($.isFunction(f)) {
                    f.call(context, argument);
                }
            }
        }
    };

    $.fn.dropdown = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.dropdown');
        }
    };
})(jQuery);

(function($) {
    var methods = {
        init: function(parameters) {
            return this.each(function() {
                var defaults = {
                    el: $(this),
                    openEvent: 'mousedown',
                    menuDataKey: 'autocomplete'
                };
                var t = this;
                var p = t.p = $.extend(defaults, parameters);
                var $el = p.el;
                var defData = p.data.slice(0);

                if (!$el.data(p.menuDataKey)) {
                    $el.bind('keyup', function(e) {
                        p.isShow = true;
                        var newParams = $.extend(p, {
                            data: $.grep(defData, function(n, i) {
                                return !n.title.toLowerCase().search($el.val().toLowerCase());
                            })
                        });
                        $el.dropdown(newParams);
                    });
                }

                $el.dropdown(p);
            });
        }
    };

    $.fn.autocomplete = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.autocomplete');
        }
    };
})(jQuery);