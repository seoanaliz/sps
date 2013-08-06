var KEY = {
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
var TIME = {
    SEC: 1000,
    MIN: 60000,
    HOUR: 3600000,
    DAY: 86400000
};

if (!window.localStorage) {
    window.localStorage = {
        getItem: function(key) {},
        setItem: function(key, value) {}
    };
}

if (!window.console) {
    window.console = {
        /**
         @param {...*} message
         */
        info: function(message) {},
        /**
         @param {...*} message
         */
        warn: function(message) {},
        /**
         @param {...*} message
         */
        error: function(message) {},
        /**
         @param {...*} message
         */
        log: function(message) {},
        /**
         @param {...*} message
         */
        dir: function(message) {},
        group: function() {},
        groupCollapsed: function() {},
        groupEnd: function() {},
        trace: function() {},
        /**
         @param {string} timerName
         */
        time: function(timerName) {},
        /**
         @param {string} timerName
         */
        timeEnd: function(timerName) {}
    };
}

(function() {
    var globalStageKey = 'globalStorage';

    window.globalStorage = {
        _serialize: function(obj) {
            return JSON.stringify(obj);
        },
        _unserialize: function(str) {
            return JSON.parse(str);
        },
        _setItems: function(items) {
            localStorage.setItem(globalStageKey, this._serialize(items));
        },
        _addItems: function(items) {
            for (var item in items) {
                if (items.hasOwnProperty(item)) this._addItem(item, items[item]);
            }
        },
        _addItem: function(key, value) {
            var allItems = this._getItems();
            allItems[key] = value;
            this._setItems(allItems);
        },
        _getItems: function() {
            return this._unserialize(localStorage.getItem(globalStageKey)) || {};
        },
        _getItem: function(key) {
            return this._getItems()[key];
        },
        _removeItems: function() {
            localStorage.setItem(globalStageKey, '');
        },
        _removeItem: function(key) {
            var allItems = this._getItems();
            delete allItems[key];
            this._setItems(allItems);
        },
        items: function() {
            var isSetItem = (typeof arguments[0] == 'string' && arguments[1]);
            var isGetItem = (typeof arguments[0] == 'string' && !arguments[1]);
            var isSetItems = (typeof arguments[0] == 'object');
            var isRemoveItem = (typeof arguments[0] == 'string' && arguments[1] === null);
            var isRemoveItems = (typeof arguments[0] === null);
            if (isRemoveItem) {
                return this._removeItem(arguments[0]);
            } else if (isRemoveItems) {
                return this._removeItems();
            } else if (isSetItem) {
                return this._addItem(arguments[0], arguments[1]);
            } else if (isSetItems) {
                return this._setItems(arguments[0]);
            } else if (isGetItem) {
                return this._getItem(arguments[0]);
            } else {
                return this._getItems();
            }
        }
    };
})();

function intval(value) {
    return (value === true) ? 1 : (parseInt(value) || 0);
}

function strval(value) {
    return value + '';
}

/**
 * @param num
 * @param {string} [separator=" "]
 * @returns {string}
 */
function numberWithSeparator(num, separator) {
    return typeof num == 'string' ? num.replace(/\B(?=(\d{3})+(?!\d))/g, separator || ' ') : num;
}

// Парсинг URL
function getURLParameter(name, search) {
    search = search || location.search;
    return decodeURIComponent((new RegExp(name + '=' + '(.+?)(&|$)').exec(search)||[,null])[1]);
}

function windowOpen(url, windowName) {
    var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
    var screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
    var outerWidth = $(window).width();
    var width = 400;
    var height = 200;
    var top = parseInt(screenY + 280);
    var left = parseInt(screenX + ((outerWidth - width) / 2));
    var params = {
        top: top,
        left: left,
        width: width,
        height: height,
        menubar: 'no',
        toolbar: 'no',
        resizable: 'no',
        scrollbars: 'no',
        directories: 'no',
        location: 'yes',
        status: 'no'
    };
    var windowFeatures = $.param(params).split('&').join(',');
    return window.open(url, windowName, windowFeatures);
}

// Выделение текста в инпутах
(function($) {
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
                $autoResize.css({
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
                });

                $input.on('keyup keydown focus blur', function(e) {
                    $autoResize.css({
                        width: $input.width(),
                        font: $input.css('font'),
                        padding: $input.css('padding'),
                        fontSize: $input.css('font-size')
                    });
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

// Композиция картин
(function($) {
    var PLUGIN_NAME = 'imageComposition';
    var CLASS_LOADING = 'image-compositing';

    var methods = {
        init: function() {
            return this.each(function() {
                var $wrap = $(this);
                var coef = $wrap.width() / 381;
                var margin = 3 * coef;
                var $images = $wrap.find('img');
                var imagesNum = $images.length;

                $wrap.addClass(CLASS_LOADING); 

                var loadedImages = 0;
                $images.each(function() {
                    var $originalImage = $(this);
                    var src = $originalImage.attr('src');
                    var img = new Image();
                    img.onload = function() {
                        loadedImages++;
                        $originalImage.data('sizes', ['', img.width, img.height]);
                        if (loadedImages >= imagesNum) {
                            handleImagesLoad();
                        }
                    };
                    img.src = src;
                });

                function handleImagesLoad() {
                    var attachments = $images.map(function () {
                        return {type: 'photo', photo: {
                              sizes: {
                                  x: $(this).data('sizes')
                              }  
                        }};
                    });
                    var result = processThumbs(381, 254, attachments, {wide: false});
                    $wrap.css({
                        'width': result.width * coef + 'px',
                        'height': result.height * coef + 'px'
                    });
                    $.each(result.thumbs, function (i, thumb) {
                        var crop = cropImage(thumb, thumb.width, thumb.height);
                        $($images[i]).css({
                            'width': crop.width * coef + 'px',
                            'height': crop.height * coef + 'px',
                            'margin-left': crop.marginLeft * coef + 'px',
                            'margin-top': crop.marginTop * coef + 'px'
                        })
                        .closest('.image-wrap').css({
                            'width': thumb.width * coef,
                            'height': thumb.height * coef,
                            'margin-right': thumb.lastColumn ? '0' : margin + 'px',
                            'margin-bottom': thumb.lastRow ? '0' : margin + 'px'
                        });
                    });
                }

                $wrap.removeClass(CLASS_LOADING);
            });
        }
    };

    function processThumbs(maxW, maxH, attachments, opts){
        var oi = function(o) {
            return o === 'n' ? 1 : o === 'q' ? 2 : 0;
        }, sum = function(a) {
            var sum = 0;
            $.each(a, function(k, f) {
                sum += f;
            });
            return sum;
        }, getKeys = function(obj) {
            var keys = [];
            $.each(obj, function(k, v) {
                keys[keys.length] = k;
            });
            return keys;
        }, multiThumbsHeight = function(ratios, width, margin) {
            return (width - (ratios.length - 1) * margin) / sum(ratios);
        };

        var wide = opts.wide;
        var thumbs = [], result = [];
        $.each(attachments, function(k, a) {
            thumbs[thumbs.length] = a['photo'];
        });

        var orients = '', orients_cnt = [0, 0, 0, 0], ratios = [],
                cnt = thumbs.length;

        $.each(thumbs, function(k, t) {
            var ratio = getRatio(t);
            var orient = ratio > 1.2 ? 'w' : ratio < 0.8 ? 'v' : 'q';
            orients += orient;
            orients_cnt[oi(orient)]++;
            ratios[ratios.length] = ratio;
        });

        var avg_ratio = ratios.length > 0 ? sum(ratios) / ratios.length : 1.0;
        var max_w, max_h, margin_w = wide ? 6 : 3, margin_h = margin_w;

        if (opts.force) {
            max_w = maxW;
            max_h = maxH;
        } else {
            if (wide) {
                max_w = 537;
                max_h = 310;
            } else {
                if (maxW >= 381) {
                    max_w = 381;
                    max_h = cnt == 1 ? 361 : 237;
                } else {
                    max_w = 337;
                    max_h = cnt == 1 ? 320 : 210;
                }
            }
            if (maxW < max_w) {
                max_w = maxW;
                max_h = maxH;
            }
        }

        var max_ratio = max_w / max_h;
        var thumbs_width = 0;
        var thumbs_height = 0;

        if (cnt == 1) {
            var opt = {lastColumn: 1, lastRow: 1, single: 1};
            if (thumbs[0].thumb) {
                thumbs_width = 279;
                thumbs_height = 185;
            } else if (ratios[0] >= 1.0 * max_ratio) {
                thumbs_width = max_w;
                thumbs_height = Math.min(thumbs_width / ratios[0], max_h);
            } else {
                thumbs_height = max_h;
                thumbs_width = Math.min(thumbs_height * ratios[0], max_w);
            }
            var t = compute(thumbs[0], thumbs_width, thumbs_height, opt);
            if (!t.unsized && (t.image.width < thumbs_width || t.image.height < thumbs_height)) {
                thumbs_width = t.image.width;
                thumbs_height = t.image.height;
                t = compute(thumbs[0], thumbs_width, thumbs_height, opt);
            }
            result[0] = t;
        }

        else if (cnt == 2)
            switch (orients) {
                case 'ww':
                    if (avg_ratio > 1.4 * max_ratio && (ratios[1] - ratios[0]) < 0.2) {
                        var w = max_w;
                        var h = Math.min(w / ratios[0], w / ratios[1], (max_h - margin_h) / 2.0);
                        result[0] = compute(thumbs[0], w, h, {lastColumn: 1});
                        result[1] = compute(thumbs[1], w, h, {lastColumn: 1, lastRow: 1});

                        thumbs_width = max_w;
                        thumbs_height = 2 * h + margin_h;
                        break;
                    }
                case 'vv':
                case 'qv':
                case 'vq':
                case 'qq':
                    w = (max_w - margin_w) / 2;
                    h = Math.min(w / ratios[0], w / ratios[1], max_h);
                    result[0] = compute(thumbs[0], w, h, {lastRow: 1});
                    result[1] = compute(thumbs[1], w, h, {lastRow: 1, lastColumn: 1});

                    thumbs_width = max_w;
                    thumbs_height = h;
                    break;
                default:
                    var w0 = intval((max_w - margin_w) / ratios[1] / (1 / ratios[0] + 1 / ratios[1]));
                    var w1 = max_w - w0 - margin_w;
                    var h = Math.min(max_h, w0 / ratios[0], w1 / ratios[1]);
                    result[0] = compute(thumbs[0], w0, h, {lastRow: 1});
                    result[1] = compute(thumbs[1], w1, h, {lastColumn: 1, lastRow: 1});

                    thumbs_width = max_w;
                    thumbs_height = h;
            }
        else if (cnt == 3) {
            if ((ratios[0] > 1.2 * max_ratio || avg_ratio > 1.5 * max_ratio) && orients == 'www') {
                var w = max_w;
                var h_cover = Math.min(w / ratios[0], (max_h - margin_h) * 0.66);
                result[0] = compute(thumbs[0], w, h_cover, {lastColumn: 1});
                if (orients === 'www') {
                    var w = intval(max_w - margin_w) / 2;
                    var h = Math.min(max_h - h_cover - margin_h, w / ratios[1], w / ratios[2]);
                    result[1] = compute(thumbs[1], w, h, {lastRow: 1});
                    result[2] = compute(thumbs[2], max_w - w - margin_w, h, {lastColumn: 1, lastRow: 1});
                } else {
                    var w0 = intval(((max_w - margin_w) / ratios[2]) / (1 / ratios[1] + 1 / ratios[2]));
                    var w1 = max_w - w0 - margin_w;
                    var h = Math.min(max_h - h_cover - margin_h, w0 / ratios[2], w1 / ratios[1]);

                    result[1] = compute(thumbs[1], w0, h, {lastRow: 1});
                    result[2] = compute(thumbs[2], w0, h, {lastRow: 1, lastColumn: 1});
                }
                thumbs_width = max_w;
                thumbs_height = h_cover + h + margin_h;
            } else {
                var h = max_h;
                var w_cover = intval(Math.min(h * ratios[0], (max_w - margin_w) * 0.75));
                result[0] = compute(thumbs[0], w_cover, h, {lastRow: 1});

                var h1 = ratios[1] * (max_h - margin_h) / (ratios[2] + ratios[1]);
                var h0 = max_h - h1 - margin_h;
                var w = Math.min(max_w - w_cover - margin_w, intval(h1 * ratios[2]), intval(h0 * ratios[1]));

                result[1] = compute(thumbs[1], w, h0, {lastColumn: 1});
                result[2] = compute(thumbs[2], w, h1, {lastColumn: 1, lastRow: 1});

                var thumbs_width = w_cover + w + margin_w;
                var thumbs_height = max_h;
            }
        } else if (cnt == 4) {
            if ((ratios[0] > 1.2 * max_ratio || avg_ratio > 1.5 * max_ratio) && orients == 'wwww') {
                var w = max_w;
                var h_cover = Math.min(w / ratios[0], (max_h - margin_h) * 0.66);
                result[0] = compute(thumbs[0], w, h_cover, {lastColumn: 1});

                var h = (max_w - 2 * margin_w) / (ratios[1] + ratios[2] + ratios[3]);
                var w0 = intval(h * ratios[1]);
                var w1 = intval(h * ratios[2]);
                var w2 = w - w0 - w1 - (2 * margin_w);
                var h = Math.min(max_h - h_cover - margin_h, h);

                result[1] = compute(thumbs[1], w0, h, {lastRow: 1});
                result[2] = compute(thumbs[2], w1, h, {lastRow: 1});
                result[3] = compute(thumbs[3], w2, h, {lastColumn: 1, lastRow: 1});

                thumbs_width = max_w;
                thumbs_height = h_cover + h + margin_h;
            } else {
                var h = max_h;
                var w_cover = Math.min(h * ratios[0], (max_w - margin_w) * 0.66);
                result[0] = compute(thumbs[0], w_cover, h, {lastRow: 1});

                var w = (max_h - 2 * margin_h) / (1 / ratios[1] + 1 / ratios[2] + 1 / ratios[3]);
                var h0 = intval(w / ratios[1]);
                var h1 = intval(w / ratios[2]);
                var h2 = h - h0 - h1 - (2 * margin_h);
                var w = Math.min(max_w - w_cover - margin_w, w);

                result[1] = compute(thumbs[1], w, h0, {lastColumn: 1});
                result[2] = compute(thumbs[2], w, h1, {lastColumn: 1});
                result[3] = compute(thumbs[3], w, h2, {lastColumn: 1, lastRow: 1});

                thumbs_width = w_cover + w + margin_w;
                thumbs_height = max_h;
            }
        } else {
            var ratios_cropped = [];
            if (avg_ratio > 1.1) {
                $.each(ratios, function(k, ratio) {
                    ratios_cropped[ratios_cropped.length] = Math.max(1.0, ratio);
                })
            } else {
                $.each(ratios, function(k, ratio) {
                    ratios_cropped[ratios_cropped.length] = Math.min(1.0, ratio);
                });
            }

            var tries = {};

            var first_line, second_line, third_line;
            tries[(first_line = cnt) + ''] = [multiThumbsHeight(ratios_cropped, max_w, margin_w)];

            for (first_line = 1; first_line <= cnt - 1; first_line++) {
                tries[first_line + ',' + (secont_line = cnt - first_line)] = [
                    multiThumbsHeight(ratios_cropped.slice(0, first_line), max_w, margin_w),
                    multiThumbsHeight(ratios_cropped.slice(first_line), max_w, margin_w)
                ];
            }

            for (first_line = 1; first_line <= cnt - 2; first_line++) {
                for (second_line = 1; second_line <= cnt - first_line - 1; second_line++) {
                    tries[first_line + ',' + second_line + ',' + (third_line = cnt - first_line - second_line)] = [
                        multiThumbsHeight(ratios_cropped.slice(0, first_line), max_w, margin_w),
                        multiThumbsHeight(ratios_cropped.slice(first_line, first_line + second_line), max_w, margin_w),
                        multiThumbsHeight(ratios_cropped.slice(first_line + second_line), max_w, margin_w)
                    ];
                }
            }

            var opt_conf = null;
            var opt_diff = 0;
            var opt_height = 0;
            var opt_h;
            for (var conf in tries) {
                var heights = tries[conf];
                var conf_h = sum(heights) + margin_h * (heights.length - 1);
                var conf_diff = Math.abs(conf_h - max_h);

                if (conf.indexOf(',') != -1) {
                    var conf_nums = conf.split(',');
                    for (var i = 0; i < conf_nums.length; i++)
                        conf_nums[i] = intval(conf_nums[i]);
                    if (conf_nums[0] > conf_nums[1] || conf_nums[2] && conf_nums[1] > conf_nums[2]) {
                        conf_diff += 50;
                        conf_diff *= 1.5;
                    }
                }
                if (opt_conf == null || conf_diff < opt_diff) {
                    opt_conf = conf;
                    opt_diff = conf_diff;
                    opt_h = conf_h;
                }
            }

            var thumbs_remain = clone(thumbs);
            var ratios_remain = clone(ratios_cropped);
            var chunks = opt_conf.split(',');
            var opt_heights = tries[opt_conf];
            var last_row = chunks.length - 1;

            for (var i = 0; i < chunks.length; i++) {
                var line_chunks_num = parseInt(chunks[i]);
                var line_thumbs = thumbs_remain.splice(0, line_chunks_num);
                var line_height = opt_heights.shift();
                var last_column = line_thumbs.length - 1;
                var opts = {};
                if (last_row == i) {
                    opts.lastRow = true;
                }
                var width_remains = max_w;
                for (var j = 0; j < line_thumbs.length; j++) {
                    var thumb = line_thumbs[j];
                    var thumb_ratio = ratios_remain.shift();
                    var thumb_opts = opts;
                    if (last_column == j) {
                        var thumb_width = Math.ceil(width_remains);
                        thumb_opts.lastColumn = true;
                    } else {
                        thumb_width = intval(thumb_ratio * line_height);
                        width_remains -= thumb_width + margin_w;
                    }
                    result[result.length] = compute(thumb, thumb_width, line_height, thumb_opts);
                }
            }

            thumbs_width = max_w;
            thumbs_height = opt_h;
        }
        return {width: intval(thumbs_width), height: intval(thumbs_height), thumbs: result};
    }

    function getRatio(thumb){
        var t = thumb.sizes['x'];
        var ratio = t[1] == 0 || t[2] == 0 ? 1 : t[1] / t[2];
        return ratio;
    }

    function compute(t, w, h, opt) {
        var res = {
            width: intval(w),
            height: intval(h),
            lastColumn: opt.lastColumn,
            lastRow: opt.lastRow,
            single: opt.single,
            image: getSize(t, w, h, opt.single),
            orig: t
        };

        res.ratio = res.image.width / res.image.height;

        return res;
    }

    function getSize(thumb, width, height, single) {
        if (!thumb)
            return {};

        var isAlbum = !!thumb.thumb;
        var image_sizes = isAlbum ? thumb.thumb.sizes : thumb.sizes;
        var pixel_ratio = window.devicePixelRatio || 1;
        var x_size = image_sizes['x'] || {};
        var ratio = (x_size[1] || 1) / (x_size[2] || 1);
        var min_s = 0;

        if (ratio > width / height) {
            min_s = height;
            if (ratio > 1.0) {
                min_s *= ratio;
            }
        } else {
            min_s = width;
            if (ratio < 1.0) {
                min_s /= ratio;
            }
        }
        height /= pixel_ratio;
        width /= pixel_ratio;

        var photo_type = 'x';

        var size = image_sizes[photo_type];
        return {src: size[0], width: size[1], height: size[2]};
    }

    function cropImage(thumb, width, height) {
        var single = thumb.single;
        var image_size = thumb.image;
        var img_w = width;
        var img_h = height;
        var x = 0;
        var y = 0;

        if (image_size.width && image_size.height) {
            var img_ratio = image_size.width / image_size.height;

            if (img_ratio < width / height) {
                if (single && image_size.width < width) {
                    width = image_size.width;
                    height = Math.min(height, image_size.height);
                }
                img_w = width;
                img_h = img_w / img_ratio;
                if (img_h > height) {
                    y = -intval((img_h - height) / 3);
                }
            } else {
                if (single && image_size.height < height) {
                    height = image_size.height;
                    width = Math.min(width, image_size.width);
                }
                img_h = height;
                img_w = img_h * img_ratio;
                if (img_w > width) {
                    x = -intval((img_w - width) / 3);
                }
            }
        }

        return {width: img_w, height: img_h, marginLeft: x, marginTop: thumb.isAlbum && thumb.single ? 0 : y};
    }

    function clone(obj, req) {
        var newObj = $.isArray(obj) ? [] : {};
        for (var i in obj) {
            if ($.browser.webkit && (i === 'layerX' || i === 'layerY'))
                continue;
            if (req && typeof(obj[i]) === 'object' && i !== 'prototype') {
                newObj[i] = clone(obj[i]);
            } else {
                newObj[i] = obj[i];
            }
        }
        return newObj;
    }

    $.fn[PLUGIN_NAME] = function(method) {
        return methods.init.apply(this, arguments);
    };
})(jQuery);

// Попап
var Box = (function() {
    var $body;
    var $html;
    var $layout;
    var history = [];
    var htmlOverflow;

    return function(options) {
        var params = $.extend({
            title: '',
            html: '',
            closeBtn: true,
            buttons: [],
            width: 400,
            additionalClass: '',
            onshow: function() {},
            onhide: function() {},
            oncreate: function() {}
        }, options);

        if (!$layout) {
            $body = $('body');
            $html = $('html');
            $layout = $(tmpl(BOX_LAYOUT)).appendTo($body);
        } else {
            $layout = $('body > .box-layout');
        }

        var $boxWrap = $(tmpl(BOX_WRAP)).appendTo($body).hide();
        var $box = $boxWrap.find('> .box').width(params.width);

        $box.delegate('.title > .close', 'click', function() {
            box.hide();
        });

        $boxWrap.on('click', function(e) {
            if (e.target == this) {
                box.hide();
            }
        });

        var box = {
            $el: $box,
            $box: $box,
            visible: false,
            show: show,
            hide: hide,
            remove: remove,
            setHTML: setHTML,
            setTitle: setTitle,
            setButtons: setButtons,
            refreshTop: refreshTop,
            addClass: addClass,
            removeClass: removeClass
        };

        box.setTitle(params.title);
        box.setHTML(params.html);
        box.setButtons(params.buttons);
        box.addClass(params.additionalClass);

        function show() {
            var prevBox = history[history.length-1];
            if (prevBox != box) {
                history.push(box);
                if (prevBox) {
                    prevBox.hide();
                } else {
                    htmlOverflow = $html.css('overflow-y');
                    $html.width($body.width()).css('overflow-y', 'hidden');
                    $layout.show();
                }
            }

            $(document).off('keydown.box').on('keydown.box', function(e) {
                if (e.keyCode == KEY.ESC) {
                    box.hide();
                }
            });

            $boxWrap.show();
            box.visible = true;
            box.refreshTop();
            params.onshow.call(box, $box);

            return box;
        }

        function hide() {
            box.visible = false;
            $boxWrap.hide();

            var prevBox = history[history.length-2];
            if (prevBox != box) {
                history.pop();
                if (prevBox) {
                    prevBox.show();
                } else {
                    $html.css('overflow-y', htmlOverflow).width('auto');
                    $layout.hide();
                }
            }

            params.onhide.call(box, $box);
            return box;
        }

        function remove() {
            if (box.visible) {
                box.hide();
            }
            $box.remove();
        }

        function setHTML(html) {
            $box.find('> .body').html(html);
            box.refreshTop();
            return box;
        }

        function setTitle(title) {
            if (!title) {
                $box.find('> .title').remove();
            } else {
                if (!$box.find('> .title').length) {
                    $box.prepend(tmpl(BOX_TITLE));
                    if (params.closeBtn) {
                        $box.find('> .title').append(tmpl(BOX_CLOSE));
                    }
                }
                $box.find('> .title .text').text(title);
            }
            return box;
        }

        function setButtons(buttons) {
            if (!buttons || !buttons.length) {
                $box.find('> .actions-wrap').remove();
            } else {
                if (!$box.find('> .actions-wrap').length) {
                    $box.append(tmpl(BOX_ACTIONS));
                }
                $box.find('> .actions-wrap .actions').empty();
                $.each(buttons, function(i, button) {
                    var $button = $(tmpl(BOX_ACTION, button)).click(function() {
                        button.onclick ? button.onclick.call(box, $button, $box) : box.hide();
                    }).appendTo($box.find('> .actions-wrap .actions'));
                });
            }
            return box;
        }

        function addClass(cssClass) {
            $box.addClass(cssClass);
        }

        function removeClass(cssClass) {
            $box.removeClass(cssClass);
        }

        function refreshTop() {
            var top = ($(window).height() / 2.5) - ($box.height() / 2);
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
    var dropdownId = 0;

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
                    emptyMenuText: '', // Текст, который появляется, когда в меню нет ни одного пункта
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

                $menu.delegate('.' + CLASS_ITEM, 'mouseup', function(e) {
                    if (e.originalEvent && e.button != 0) return;
                    $el.dropdown('select', $(this));
                });
                $menu.on(options.openEvent, function(e) {
                    e.stopPropagation();
                });
                $menu.hide();

                $el.data(DATA_KEY, {
                    id: dropdownId++,
                    $el: $el,
                    $menu: $menu,
                    $target: $target,
                    options: options
                });

                $el.dropdown('setItems', options.data);

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
        select: function($item) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                var itemData = $item.data(options.itemDataKey);
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
                run(options.onchange, $el, itemData);
                run(($item.hasClass(CLASS_ITEM_ACTIVE) ? options.onselect : options.onunselect), $el, itemData);
                $el.trigger(TRIGGER_CHANGE);
            });
        },
        open: function(notTrigger) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                var $target = data.$target;
                var dropdownId = data.id;
                var nameSpace = EVENTS_NAMESPACE + dropdownId;

                if (!options.data.length && !options.emptyMenuText) {
                    return;
                }

                $menu.css({
                    width: options.width || $target.outerWidth() - (intval($target.css('border-left-width')) + intval($target.css('border-right-width')))
                });

                $el.dropdown('refreshPosition');
                $menu.show();

                $(window).on('resize.' + nameSpace + ' scroll.' + nameSpace, function(e) {
                    if (!$el.data(DATA_KEY)) return $(this).off(e.type + '.' + nameSpace);
                    var $menu = $el.dropdown('getMenu');
                    if ($menu.is(':visible')) {
                        $el.dropdown('refreshPosition');
                    }
                });
                $(document).on(options.closeEvent + '.' + nameSpace, function(e) {
                    if (!$el.data(DATA_KEY)) return $(this).off(options.closeEvent + '.' + nameSpace);
                    var $menu = $el.dropdown('getMenu');
                    $el.dropdown('close');
                    run(options.onclose, $el, $menu);
                });
                $(document).on('keydown.' + nameSpace, function(e) {
                    if (!$el.data(DATA_KEY)) return $(this).off(e.type + '.' + nameSpace);
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
                                    $el.dropdown('select', $hoveringItem);
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
            var dropdownId = data.id;
            var nameSpace = EVENTS_NAMESPACE + dropdownId;

            $target.removeClass(CLASS_ACTIVE);
            $menu.hide();

            $(window).off('resize.' + nameSpace);
            $(window).off('scroll.' + nameSpace);
            $(document).off(options.closeEvent + '.' + nameSpace);
            $(document).off('keydown.' + nameSpace);

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
                if (options.position == 'top') {
                    offsetTop -= $menu.outerHeight()
                        + $el.outerHeight()
                        + (parseFloat($menu.css('margin-top')) * 2)
                        - (parseFloat($menu.css('margin-bottom')) * 2);
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
        setItems: function(dataItems) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                options.data = dataItems;
                if (options.data.length || !options.emptyMenuText) {
                    $.each(options.data, function(i, item) {
                        $el.dropdown('appendItem', item);
                    });
                } else {
                    var $emptyItem = $('<div/>')
                        .text(options.emptyMenuText)
                        .addClass(CLASS_EMPTY_MENU)
                    ;
                    $menu.html($emptyItem);
                }
            });
        },
        appendItem: function(item) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $menu = data.$menu;
                var $item = $('<div/>')
                    .text(item.title)
                    .attr('data-id', item.id)
                    .addClass(CLASS_ITEM)
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
                var t = this;
                var $el = $(this);
                var defaults = {
                    openEvent: 'mousedown',
                    notFoundText: 'Ничего не найдено',
                    caseSensitive: false,
                    data: [],
                    getValue: function() {
                        return $el.val();
                    }
                };
                var options = $.extend(defaults, params);
                var searchTimeout;
                options.notFoundText = options.emptyMenuText;
                options.defData = options.data.slice(0);

                $el.dropdown(options);

                if (!$el.data(DATA_KEY)) {
                    $el.on('keyup', function(e) {
                        var options = $el.data(DATA_KEY).options;
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
                            var elVal = options.getValue.apply(t) || '';
                            var defData = options.defData;
                            var data = !elVal ? defData : $.grep(defData, function(n, i) {
                                var str = $.trim(n.title).split('ё').join('е');
                                var searchStr = $.trim(elVal).split('ё').join('е');
                                if (!options.caseSensitive) {
                                    str = str.toLowerCase();
                                    searchStr = searchStr.toLowerCase();
                                }
                                return !!(str.indexOf(searchStr) !== -1);
                            });
                            if (data.length || options.emptyMenuText) {
                                $el.dropdown($.extend(options, {
                                    isShow: $el.is(':focus'),
                                    data: data,
                                    emptyMenuText: options.notFoundText
                                }));
                            } else {
                                $el.dropdown('close');
                            }
                        }, 0);
                    });
                }

                $el.data(DATA_KEY, {
                    $el: $el,
                    options: options
                });
            });
        },

        setItems: function(dataItems) {
            return this.each(function() {
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                options.defData = dataItems;

                $el.dropdown('setItems', dataItems);
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
                    width: $el.outerWidth() - (intval($el.css('border-width')) * 2)
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

                refreshPadding($el);
                run(options.oncreate, $el);
            });
        },
        addTag: function(params) {
            return this.each(function() {
                var id = params.id;
                var $el = $(this);
                var data = $el.data(DATA_KEY);
                var options = data.options;
                var $wrap = data.$wrap;
                var $tag = $wrap.find('.' + CLASS_TAG + '[data-id=' + id + ']');
                var isAlready = !!$tag.length;

                if (isAlready) {
                    $tag.remove();
                }

                $tag = $('<span data-id="' + id + '" class="' + CLASS_TAG + '">' +
                            '<span class="' + CLASS_TAG_TEXT + '">' + params.title + '</span>' +
                            '<span class="' + CLASS_TAG_DELETE + '"></span>' +
                        '</span>');

                $tag.find('.delete').click(function() {
                    $tag.remove();
                    refreshPadding($el);
                    run(options.onremove, $el, id);
                });

                $el.before($tag);
                $el.data(DATA_KEY, data);
                refreshPadding($el);

                if (!isAlready) {
                    run(options.onadd, $el, params);
                }
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
            width: (width - left) < 40 ? width : (width - left - 1)
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

/**
 * Templating
 */
var tmpl = (function($) {
    var cache = {};
    var format = function(str) {
        return str ? str
            .replace(/[\r\t\n]/g, ' ')
            .split('<?').join('\t')
            .split("'").join("\\'")
            .replace(/\t=(.*?)\?>/g, "',$1,'")
            .split('?>').join("p.push('")
            .split('\t').join("');")
            .split('\r').join("\\'") : str;
    };
    var tmpl = function(str, data) {
        try {
            var fn = (/^#[A-Za-z0-9_-]*$/.test(str))
                ? function() {
                return cache[str] || ($(str).length ? tmpl($(str).html()) : str)
            } : (new Function('obj',
                'var p=[],' +
                    'print=function(){p.push.apply(p,arguments)},' +
                    'isset=function(v){return !!obj[v]},' +
                    'each=function(ui,obj){for(var i in obj) { print(tmpl(ui, $.extend(obj[i],{i:i}))) }};' +
                    'count=function(obj){return (obj instanceof Array) ? obj.length : countObj(obj)};' +
                    'countObj=function(obj){var cnt = 0; for(var i in obj) { if (obj.hasOwnProperty(i)) cnt++; } return cnt};' +
                    "with(obj){p.push('" + format(str) + "');} return p.join('');"
            ));
            return (cache[str] = fn(data || {}));
        } catch(e) {
            if (window.console && console.log) {
                console.log(format(str));
            }
            throw e;
        }
    };

    return tmpl;
})(jQuery);

var BOX_LAYOUT =
'<div class="box-layout"></div>';

var BOX_WRAP =
'<div class="box-wrap">' +
    '<div class="box">' +
        '<? if (isset("title")) { ?>' +
            '<div class="title">' +
                '<span class="text"><?=title?></span>' +
                '<? if (isset("closeBtn")) { ?>' +
                    '<div class="close"></div>' +
                '<? } ?>' +
            '</div>' +
        '<? } ?>' +
        '<div class="body clear-fix">' +
            '<? if (isset("body")) { ?>' +
                '<?=body?>' +
            '<? } ?>' +
        '</div>' +
        '<? if (isset("buttons") && buttons.length) { ?>' +
            '<div class="actions-wrap">' +
                '<div class="actions"></div>' +
            '</div>' +
        '<? } ?>' +
    '</div>' +
'</div>';

var BOX_ACTION =
'<button class="action button<?=isset("isWhite") ? " white" : ""?>"><?=label?></button>';

var BOX_LOADING =
'<div class="box-loading" style="<?=isset("height") ? "min-height: " + height + "px" : ""?>"></div>';

var BOX_TITLE =
'<div class="title"><span class="text"></span></div>';

var BOX_ACTIONS =
'<div class="actions-wrap"><div class="actions"></div></div>';

var BOX_CLOSE =
'<div class="close"></div>';

if (typeof console !== 'undefined' && console.log) {
    log = function () {
        if (jQuery) {
            var argsCopy = Array.prototype.slice.call(arguments, 0);
            var argsWithClonedObjects = jQuery.map(argsCopy, function (elem) {
                if (jQuery.isPlainObject(elem)) { // заменим "простые" объекты их клонами, чтобы выводить актуальное состояние на момент вызова log()
                    return jQuery.extend(true, {}, elem);
                } else {
                    return elem;
                }
            });
            console.log.apply(console, argsWithClonedObjects);
        } else {
            console.log.apply(console, arguments);
        }
    }
} else {
    log = function () {}
}