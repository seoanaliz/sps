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
    var PLUGIN_NAME="imageComposition";var CLASS_LOADING="image-compositing";var methods={init:function(){return this.each(function(){var c=$(this);var f=c.width()/380;var e=3*f;var d=c.find("img");var b=d.length;c.addClass(CLASS_LOADING);var a=0;d.each(function(){var i=$(this);var j=i.attr("src");var h=new Image();h.onload=function(){a++;i.data("sizes",["",h.width,h.height]);if(a>=b){g()}};h.src=j});function g(){var i=d.map(function(){return{type:"photo",photo:{sizes:{orig:$(this).data("sizes")}}}});var h=process(i,{wide:false});c.css({width:h.width*f+"px",height:h.height*f+"px"});$.each(h.thumbs,function(k,j){var l=cropImage(j,j.width,j.height);$(d[k]).css({width:l.width*f+"px",height:l.height*f+"px","margin-left":l.marginLeft*f+"px","margin-top":l.marginTop*f+"px"}).closest(".image-wrap").css({width:j.width*f,height:j.height*f,"margin-right":j.lastCol?"0":e+"px","margin-bottom":j.lastRow?"0":e+"px"})});c.removeClass(CLASS_LOADING)}})}};function process(m,A){var r=function(h){var i=0;$.each(h,function(j,t){i+=t});return i},a=function(i){var h=[];$.each(i,function(t,j){h[h.length]=t});return h},X=function(i,h,j){return(h-(i.length-1)*j)/r(i)};var am=A.wide;var K=[],v=[];$.each(m,function(i,h){K[K.length]=h.photo});var c="",o=[],y=K.length;$.each(K,function(h,j){var w=getRatio(j);var i=w>1.2?"-":w<0.8?"|":".";c+=i;o[o.length]=w});var J=o.length>0?r(o)/o.length:1;var ad,an,C=am?6:3,T=C;ad=380;an=237;var e=ad/an;var M=0;var f=0;if(y==1){var s={lastCol:1,lastRow:1};if(K[0].thumb){M=278;f=185}else{if(o[0]>=1*e){M=ad;f=Math.min(M/o[0],an)}else{f=an;M=Math.min(f*o[0],ad)}}var Y=calculate(K[0],M,f,s);if(!Y.unsized&&(Y.image.width<M||Y.image.height<f)){M=Y.image.width;f=Y.image.height;Y=calculate(K[0],M,f,s)}v[0]=Y}else{if(y==2){switch(c){case"--":if(J>1.4*e&&(o[1]-o[0])<0.2){var W=ad;var ag=Math.min(W/o[0],W/o[1],(an-T)/2);v[0]=calculate(K[0],W,ag,{lastCol:1});v[1]=calculate(K[1],W,ag,{lastCol:1,lastRow:1});M=ad;f=2*ag+T;break}case"||":case".|":case"|.":case"..":W=(ad-C)/2;ag=Math.min(W/o[0],W/o[1],an);v[0]=calculate(K[0],W,ag,{lastRow:1});v[1]=calculate(K[1],W,ag,{lastRow:1,lastCol:1});M=ad;f=ag;break;default:var Q=intval((ad-C)/o[1]/(1/o[0]+1/o[1]));var P=ad-Q-C;var ag=Math.min(an,Q/o[0],P/o[1]);v[0]=calculate(K[0],Q,ag,{lastRow:1});v[1]=calculate(K[1],P,ag,{lastCol:1,lastRow:1});M=ad;f=ag}}else{if(y==3){if((o[0]>1.2*e||J>1.5*e)&&c==="---"){var W=ad;var aj=Math.min(W/o[0],(an-T)*0.66);v[0]=calculate(K[0],W,aj,{lastCol:1});if(c==="---"){var W=intval(ad-C)/2;var ag=Math.min(an-aj-T,W/o[1],W/o[2]);v[1]=calculate(K[1],W,ag,{lastRow:1});v[2]=calculate(K[2],ad-W-C,ag,{lastCol:1,lastRow:1})}else{var Q=intval(((ad-C)/o[2])/(1/o[1]+1/o[2]));var P=ad-Q-C;var ag=Math.min(an-aj-T,Q/o[2],P/o[1]);v[1]=calculate(K[1],Q,ag,{lastRow:1});v[2]=calculate(K[2],Q,ag,{lastRow:1,lastCol:1})}M=ad;f=aj+ag+T}else{var ag=an;var al=intval(Math.min(ag*o[0],(ad-C)*0.75));v[0]=calculate(K[0],al,ag,{lastRow:1});var D=o[1]*(an-T)/(o[2]+o[1]);var E=an-D-T;var W=Math.min(ad-al-C,intval(D*o[2]),intval(E*o[1]));v[1]=calculate(K[1],W,E,{lastCol:1});v[2]=calculate(K[2],W,D,{lastCol:1,lastRow:1});var M=al+W+C;var f=an}}else{if(y==4){if((o[0]>1.2*e||J>1.5*e)&&c==="----"){var W=ad;var aj=Math.min(W/o[0],(an-T)*0.66);v[0]=calculate(K[0],W,aj,{lastCol:1});var ag=(ad-2*C)/(o[1]+o[2]+o[3]);var Q=intval(ag*o[1]);var P=intval(ag*o[2]);var O=W-Q-P-(2*C);var ag=Math.min(an-aj-T,ag);v[1]=calculate(K[1],Q,ag,{lastRow:1});v[2]=calculate(K[2],P,ag,{lastRow:1});v[3]=calculate(K[3],O,ag,{lastCol:1,lastRow:1});M=ad;f=aj+ag+T}else{var ag=an;var al=Math.min(ag*o[0],(ad-C)*0.66);v[0]=calculate(K[0],al,ag,{lastRow:1});var W=(an-2*T)/(1/o[1]+1/o[2]+1/o[3]);var E=intval(W/o[1]);var D=intval(W/o[2]);var B=ag-E-D-(2*T);var W=Math.min(ad-al-C,W);v[1]=calculate(K[1],W,E,{lastCol:1});v[2]=calculate(K[2],W,D,{lastCol:1});v[3]=calculate(K[3],W,B,{lastCol:1,lastRow:1});M=al+W+C;f=an}}else{var S=[];if(J>1.1){$.each(o,function(h,i){S[S.length]=Math.max(1,i)})}else{$.each(o,function(h,i){S[S.length]=Math.min(1,i)})}var V={};var l,k,U;V[(l=y)+""]=[X(S,ad,C)];for(l=1;l<=y-1;l++){V[l+","+(k=y-l)]=[X(S.slice(0,l),ad,C),X(S.slice(l),ad,C)]}for(l=1;l<=y-2;l++){for(k=1;k<=y-l-1;k++){V[l+","+k+","+(U=y-l-k)]=[X(S.slice(0,l),ad,C),X(S.slice(l,l+k),ad,C),X(S.slice(l+k),ad,C)]}}var N=null;var q=0;var g=0;var d;for(var ab in V){var ac=V[ab];var ak=r(ac)+T*(ac.length-1);var n=Math.abs(ak-an);if(ab.indexOf(",")!=-1){var H=ab.split(",");for(var af=0;af<H.length;af++){H[af]=intval(H[af])}if(H[0]>H[1]||H[2]&&H[1]>H[2]){n+=50;n*=1.5}}if(N==null||n<q){N=ab;q=n;d=ak}}var p=clone(K);var z=clone(S);var aa=N.split(",");var x=V[N];var L=aa.length-1;for(var af=0;af<aa.length;af++){var I=parseInt(aa[af]);var G=p.splice(0,I);var b=x.shift();var u=G.length-1;var A={};if(L==af){A.lastRow=true}var F=ad;for(var ae=0;ae<G.length;ae++){var Z=G[ae];var R=z.shift();var ai=A;if(u==ae){var ah=Math.ceil(F);ai.lastCol=true}else{ah=intval(R*b);F-=ah+C}v[v.length]=calculate(Z,ah,b,ai)}}M=ad;f=d}}}}return{width:intval(M),height:intval(f),thumbs:v}}function getRatio(a){var b=a.sizes.orig;var c=b[1]==0||b[2]==0?1:b[1]/b[2];return c}function calculate(d,a,e,c){var b={width:intval(a),height:intval(e),lastCol:c.lastCol,lastRow:c.lastRow,image:getSize(d,a,e)};b.ratio=b.image.width/b.image.height;return b}function getSize(a,b,h){if(!a){return{}}var c=!!a.thumb;var g=c?a.thumb.sizes:a.sizes;var i=window.devicePixelRatio||1;var e=g.orig||{};var f=(e[1]||1)/(e[2]||1);var k=0;if(f>b/h){k=h;if(f>1){k*=f}}else{k=b;if(f<1){k/=f}}h/=i;b/=i;var d="orig";var j=g[d];return{src:j[0],width:j[1],height:j[2]}}function cropImage(a,b,j){var i=a.single;var e=a.image;var f=b;var c=j;var h=0;var g=0;if(e.width&&e.height){var d=e.width/e.height;if(d<b/j){if(i&&e.width<b){b=e.width;j=Math.min(j,e.height)}f=b;c=f/d;if(c>j){g=-intval((c-j)/3)}}else{if(i&&e.height<j){j=e.height;b=Math.min(b,e.width)}c=j;f=c*d;if(f>b){h=-intval((f-b)/3)}}}return{width:f,height:c,marginLeft:h,marginTop:a.isAlbum&&a.single?0:g}}function clone(d,c){var a=$.isArray(d)?[]:{};for(var b in d){if($.browser.webkit&&(b==="layerX"||b==="layerY")){continue}if(c&&typeof(d[b])==="object"&&b!=="prototype"){a[b]=clone(d[b])}else{a[b]=d[b]}}return a}$.fn[PLUGIN_NAME]=function(){return methods.init.apply(this,arguments)};var PLUGIN_NAME="imageComposition";var CLASS_LOADING="image-compositing";var methods={init:function(){return this.each(function(){var c=$(this);var f=c.width()/380;var e=3*f;var d=c.find("img");var b=d.length;c.addClass(CLASS_LOADING);var a=0;d.each(function(){var i=$(this);var j=i.attr("src");var h=new Image();h.onload=function(){a++;i.data("sizes",["",h.width,h.height]);if(a>=b){g()}};h.src=j});function g(){var i=d.map(function(){return{type:"photo",photo:{sizes:{orig:$(this).data("sizes")}}}});var h=process(i,{wide:false});c.css({width:h.width*f+"px",height:h.height*f+"px"});$.each(h.thumbs,function(k,j){var l=cropImage(j,j.width,j.height);$(d[k]).css({width:l.width*f+"px",height:l.height*f+"px","margin-left":l.marginLeft*f+"px","margin-top":l.marginTop*f+"px"}).closest(".image-wrap").css({width:j.width*f,height:j.height*f,"margin-right":j.lastCol?"0":e+"px","margin-bottom":j.lastRow?"0":e+"px"})});c.removeClass(CLASS_LOADING)}})}};function process(m,A){var r=function(h){var i=0;$.each(h,function(j,t){i+=t});return i},a=function(i){var h=[];$.each(i,function(t,j){h[h.length]=t});return h},X=function(i,h,j){return(h-(i.length-1)*j)/r(i)};var am=A.wide;var K=[],v=[];$.each(m,function(i,h){K[K.length]=h.photo});var c="",o=[],y=K.length;$.each(K,function(h,j){var w=getRatio(j);var i=w>1.2?"-":w<0.8?"|":".";c+=i;o[o.length]=w});var J=o.length>0?r(o)/o.length:1;var ad,an,C=am?6:3,T=C;ad=380;an=237;var e=ad/an;var M=0;var f=0;if(y==1){var s={lastCol:1,lastRow:1};if(K[0].thumb){M=278;f=185}else{if(o[0]>=1*e){M=ad;f=Math.min(M/o[0],an)}else{f=an;M=Math.min(f*o[0],ad)}}var Y=calculate(K[0],M,f,s);if(!Y.unsized&&(Y.image.width<M||Y.image.height<f)){M=Y.image.width;f=Y.image.height;Y=calculate(K[0],M,f,s)}v[0]=Y}else{if(y==2){switch(c){case"--":if(J>1.4*e&&(o[1]-o[0])<0.2){var W=ad;var ag=Math.min(W/o[0],W/o[1],(an-T)/2);v[0]=calculate(K[0],W,ag,{lastCol:1});v[1]=calculate(K[1],W,ag,{lastCol:1,lastRow:1});M=ad;f=2*ag+T;break}case"||":case".|":case"|.":case"..":W=(ad-C)/2;ag=Math.min(W/o[0],W/o[1],an);v[0]=calculate(K[0],W,ag,{lastRow:1});v[1]=calculate(K[1],W,ag,{lastRow:1,lastCol:1});M=ad;f=ag;break;default:var Q=intval((ad-C)/o[1]/(1/o[0]+1/o[1]));var P=ad-Q-C;var ag=Math.min(an,Q/o[0],P/o[1]);v[0]=calculate(K[0],Q,ag,{lastRow:1});v[1]=calculate(K[1],P,ag,{lastCol:1,lastRow:1});M=ad;f=ag}}else{if(y==3){if((o[0]>1.2*e||J>1.5*e)&&c==="---"){var W=ad;var aj=Math.min(W/o[0],(an-T)*0.66);v[0]=calculate(K[0],W,aj,{lastCol:1});if(c==="---"){var W=intval(ad-C)/2;var ag=Math.min(an-aj-T,W/o[1],W/o[2]);v[1]=calculate(K[1],W,ag,{lastRow:1});v[2]=calculate(K[2],ad-W-C,ag,{lastCol:1,lastRow:1})}else{var Q=intval(((ad-C)/o[2])/(1/o[1]+1/o[2]));var P=ad-Q-C;var ag=Math.min(an-aj-T,Q/o[2],P/o[1]);v[1]=calculate(K[1],Q,ag,{lastRow:1});v[2]=calculate(K[2],Q,ag,{lastRow:1,lastCol:1})}M=ad;f=aj+ag+T}else{var ag=an;var al=intval(Math.min(ag*o[0],(ad-C)*0.75));v[0]=calculate(K[0],al,ag,{lastRow:1});var D=o[1]*(an-T)/(o[2]+o[1]);var E=an-D-T;var W=Math.min(ad-al-C,intval(D*o[2]),intval(E*o[1]));v[1]=calculate(K[1],W,E,{lastCol:1});v[2]=calculate(K[2],W,D,{lastCol:1,lastRow:1});var M=al+W+C;var f=an}}else{if(y==4){if((o[0]>1.2*e||J>1.5*e)&&c==="----"){var W=ad;var aj=Math.min(W/o[0],(an-T)*0.66);v[0]=calculate(K[0],W,aj,{lastCol:1});var ag=(ad-2*C)/(o[1]+o[2]+o[3]);var Q=intval(ag*o[1]);var P=intval(ag*o[2]);var O=W-Q-P-(2*C);var ag=Math.min(an-aj-T,ag);v[1]=calculate(K[1],Q,ag,{lastRow:1});v[2]=calculate(K[2],P,ag,{lastRow:1});v[3]=calculate(K[3],O,ag,{lastCol:1,lastRow:1});M=ad;f=aj+ag+T}else{var ag=an;var al=Math.min(ag*o[0],(ad-C)*0.66);v[0]=calculate(K[0],al,ag,{lastRow:1});var W=(an-2*T)/(1/o[1]+1/o[2]+1/o[3]);var E=intval(W/o[1]);var D=intval(W/o[2]);var B=ag-E-D-(2*T);var W=Math.min(ad-al-C,W);v[1]=calculate(K[1],W,E,{lastCol:1});v[2]=calculate(K[2],W,D,{lastCol:1});v[3]=calculate(K[3],W,B,{lastCol:1,lastRow:1});M=al+W+C;f=an}}else{var S=[];if(J>1.1){$.each(o,function(h,i){S[S.length]=Math.max(1,i)})}else{$.each(o,function(h,i){S[S.length]=Math.min(1,i)})}var V={};var l,k,U;V[(l=y)+""]=[X(S,ad,C)];for(l=1;l<=y-1;l++){V[l+","+(k=y-l)]=[X(S.slice(0,l),ad,C),X(S.slice(l),ad,C)]}for(l=1;l<=y-2;l++){for(k=1;k<=y-l-1;k++){V[l+","+k+","+(U=y-l-k)]=[X(S.slice(0,l),ad,C),X(S.slice(l,l+k),ad,C),X(S.slice(l+k),ad,C)]}}var N=null;var q=0;var g=0;var d;for(var ab in V){var ac=V[ab];var ak=r(ac)+T*(ac.length-1);var n=Math.abs(ak-an);if(ab.indexOf(",")!=-1){var H=ab.split(",");for(var af=0;af<H.length;af++){H[af]=intval(H[af])}if(H[0]>H[1]||H[2]&&H[1]>H[2]){n+=50;n*=1.5}}if(N==null||n<q){N=ab;q=n;d=ak}}var p=clone(K);var z=clone(S);var aa=N.split(",");var x=V[N];var L=aa.length-1;for(var af=0;af<aa.length;af++){var I=parseInt(aa[af]);var G=p.splice(0,I);var b=x.shift();var u=G.length-1;var A={};if(L==af){A.lastRow=true}var F=ad;for(var ae=0;ae<G.length;ae++){var Z=G[ae];var R=z.shift();var ai=A;if(u==ae){var ah=Math.ceil(F);ai.lastCol=true}else{ah=intval(R*b);F-=ah+C}v[v.length]=calculate(Z,ah,b,ai)}}M=ad;f=d}}}}return{width:intval(M),height:intval(f),thumbs:v}}function getRatio(a){var b=a.sizes.orig;var c=b[1]==0||b[2]==0?1:b[1]/b[2];return c}function calculate(d,a,e,c){var b={width:intval(a),height:intval(e),lastCol:c.lastCol,lastRow:c.lastRow,image:getSize(d,a,e)};b.ratio=b.image.width/b.image.height;return b}function getSize(a,b,h){if(!a){return{}}var c=!!a.thumb;var g=c?a.thumb.sizes:a.sizes;var i=window.devicePixelRatio||1;var e=g.orig||{};var f=(e[1]||1)/(e[2]||1);var k=0;if(f>b/h){k=h;if(f>1){k*=f}}else{k=b;if(f<1){k/=f}}h/=i;b/=i;var d="orig";var j=g[d];return{src:j[0],width:j[1],height:j[2]}}function cropImage(a,b,j){var i=a.single;var e=a.image;var f=b;var c=j;var h=0;var g=0;if(e.width&&e.height){var d=e.width/e.height;if(d<b/j){if(i&&e.width<b){b=e.width;j=Math.min(j,e.height)}f=b;c=f/d;if(c>j){g=-intval((c-j)/3)}}else{if(i&&e.height<j){j=e.height;b=Math.min(b,e.width)}c=j;f=c*d;if(f>b){h=-intval((f-b)/3)}}}return{width:f,height:c,marginLeft:h,marginTop:a.isAlbum&&a.single?0:g}}function clone(d,c){var a=$.isArray(d)?[]:{};for(var b in d){if($.browser.webkit&&(b==="layerX"||b==="layerY")){continue}if(c&&typeof(d[b])==="object"&&b!=="prototype"){a[b]=clone(d[b])}else{a[b]=d[b]}}return a}$.fn[PLUGIN_NAME]=function(){return methods.init.apply(this,arguments)};
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
            .replace(/\t=(.*?)\?>/g, "',escape($1),'")
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
                    'escape=function (val) { if ("string" !== typeof val) return val;' +
                        'return val.replace(/script/g, "").replace(/\\\"/g, "\\\'")},' +
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