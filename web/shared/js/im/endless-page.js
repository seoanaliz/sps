var EndlessPage = Page.extend({
    _templateItem: '',
    _itemsLimit: 20,
    _itemsSelector: '',
    _pageLoaded: null,
    _isTop: false,
    _isPreload: true,
    _preloadData: null,
    _isEnded: false,

    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t.pageId() != pageId)) {
            t.pageLoaded(0);
            t._preloadData = {};
            t.ended(false);
            if (t.model() && t.model().list) {
                t.model().list([]);
            }
        }
        this._super.apply(this, arguments);
    },
    getData: function() {
        var t = this;
        var pageId = t.pageId();
        var deferred = Control.fire(t.serviceName(), t.serviceParams());

        t.lock();
        t.onShow();
        deferred.success(function(data) {
            t.unlock();
            if (pageId == t.pageId()) {
                t.onLoad(data);
                t.renderTemplate();
                t.makeList(t.el().find(t._itemsSelector));
                t.onRender();
                t.preloadData(1);
            }
        });

        return deferred;
    },
    preloadData: function(pageNumber) {
        var t = this;
        if (!t._isPreload || t.ended()) {
            return;
        }
        var limit = t.itemsLimit();
        var offset = pageNumber * limit;
        var pageId = t.pageId();
        var preloadData = t._preloadData || {};

        if (!preloadData[pageNumber] && !t.isLock()) {
            Events.fire(t.serviceName(), pageId, offset, limit, function(data) {
                if (pageId == t.pageId()) {
                    preloadData[pageNumber] = data;
                }
            });
        }
    },
    onShow: function() {},
    onLoad: function(data) {},
    onRender: function() {},
    makeList: function($list) {},
    showMore: function() {
        var t = this;
        var currentPage = t.pageLoaded();
        var nextPage = currentPage + 1;
        var limit = t.itemsLimit();
        var offset = nextPage * limit;
        var pageId = t.pageId();
        var preloadData = t._preloadData || {};

        if (t.ended()) {
            return;
        }

        if (!t.isLock()) {
            t.lock();
            if (preloadData[nextPage]) {
                setData(preloadData[nextPage]);
            } else {
                Events.fire(t.serviceName(), pageId, offset, limit, function(data) {
                    setData(data);
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
                if (t._isTop) {
                    $list.prepend($block);
                    $(window).scrollTop($(document).height() - bottom);
                } else {
                    $list.append($block);
                }
                t.makeList($block);
                t.pageLoaded(nextPage);
                t.preloadData(nextPage + 1);
            }
        }
    },
    onScroll: function() {
        var t = this;
        if (!t.isVisible()) return;
        if (t.checkAtTop() && t._isTop || t.checkAtBottom() && !t._isTop) {
            t.showMore();
        }
    },

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
