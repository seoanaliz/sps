var EndlessPage = Page.extend({
    _templateItem: '',
    _itemsLimit: 20,
    _itemsSelector: '',
    _loaderSelector: '',
    _pageLoaded: null,
    _isTop: false,
    _isPreload: true,
    _isEnded: false,

    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t.pageId() != pageId)) {
            t.pageLoaded(0);
            t.ended(false);
            if (t.model()) {
                t.model().list([]);
                t.model().preloadData({});
            }
        }
        this._super.apply(this, arguments);
    },
    loadData: function() {
        var t = this;
        var pageId = t.pageId();
        var deferred = this._super.apply(this, arguments);

        t.onShow();
        deferred.success(function(data) {
            if (pageId == t.pageId()) {
                t.onLoad(data);
                t.renderTemplate();
                t.makeList(t.el().find(t._itemsSelector));
                t.onRender();
                t.preload(1);
            }
        });

        return deferred;
    },
    showMore: function() {
        var t = this;
        var currentPage = t.pageLoaded();
        var nextPage = currentPage + 1;
        var pageId = t.pageId();

        if (t.ended()) {
            return;
        }

        if (!t.isLock()) {
            t.lock();
            if (t.model().preloadData()[nextPage]) {
                setData(t.model().preloadData()[nextPage]);
            } else {
                var $loader;
                var deferred = Control.fire(t.serviceName(), $.extend(t.serviceParams(), {
                    offset: nextPage * t.itemsLimit()
                }));
                if (t._loaderSelector) {
                    $loader = t.el().find(t._itemsSelector + ' ' + t._loaderSelector);
                    $loader.show();
                }
                deferred.success(function(data) {
                    setData(data);
                    if (t._loaderSelector) {
                        $loader.hide();
                    }
                });
            }
        }

        function setData(data) {
            t.unlock();
            if (pageId == t.pageId()) {
                t.onLoad(data);
                var $list = t.el().find(t._itemsSelector);
                var $block;
                var bottom = $(document).height() - $(window).scrollTop();
                var html = '';
                $.each(data.list, function(i, obj) {
                    html += t.tmpl()(t._templateItem, obj);
                });

                $block = $(html);
                if (t.isTop()) {
                    $list.prepend($block);
                    $(window).scrollTop($(document).height() - bottom);
                } else {
                    $list.append($block);
                }
                t.makeList($block);
                t.pageLoaded(nextPage);
                t.preload(nextPage + 1);
            }
        }
    },
    preload: function(pageNumber) {
        var t = this;
        if (!t._isPreload || t.ended()) {
            return;
        }
        var pageId = t.pageId();

        if (!t.model().preloadData()[pageNumber] && !t.isLock()) {
            var deferred = Control.fire(t.serviceName(), $.extend(t.serviceParams(), {
                offset: pageNumber * t.itemsLimit()
            }));
            deferred.success(function(data) {
                if (pageId == t.pageId()) {
                    t.model().preloadData()[pageNumber] = data;
                }
            });
        }
    },
    onScroll: function() {
        var t = this;
        if (!t.isVisible()) return;
        if (t.checkAtTop() && t.isTop() || t.checkAtBottom() && !t.isTop()) {
            t.showMore();
        }
    },
    onShow: function() {},
    onLoad: function(data) {},
    onRender: function() {},
    makeList: function($list) {},

    // Getters & setters
    checkAtTop: function() {
        var t = this;
        return !!($(window).scrollTop() < 300);
    },
    checkAtBottom: function() {
        var t = this;
        return !!($(window).scrollTop() >= $(document).height() - $(window).height() - 300);
    },
    pageLoaded: function(pageLoaded) {
        if (arguments.length) {
            this._pageLoaded = pageLoaded;
            return this;
        } else {
            return intval(this._pageLoaded);
        }
    },
    itemsLimit: function(itemsLimit) {
        if (arguments.length) {
            this._itemsLimit = itemsLimit;
            return this;
        } else {
            return intval(this._itemsLimit);
        }
    },
    ended: function(ended) {
        if (arguments.length) {
            this._isEnded = ended;
            return this;
        } else {
            return !!this._isEnded;
        }
    },
    isTop: function(isTop) {
        if (arguments.length) {
            this._isTop = isTop;
            return this;
        } else {
            return !!this._isTop;
        }
    },
    serviceParams: function() {
        var t = this;
        if (arguments.length) {
            return this._super.apply(this, arguments);
        } else {
            return {
                pageId: t.pageId() == Configs.commonDialogsList ? undefined : t.pageId(),
                limit: t.itemsLimit(),
                offset: t.pageLoaded() * t.itemsLimit()
            }
        }
    }
});
