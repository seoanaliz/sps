// Парсинг URL
function getURLParameter(name) {
    return decodeURIComponent((new RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
}

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

// Image composition
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