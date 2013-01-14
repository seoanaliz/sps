var Page = Widget.extend({
    _isVisible: false,
    _templateLoading: '',
    _pageId: null,
    _cache: null,
    _service: 'get_dialogs',
    _params: null,
    _isLock: false,
    _isCache: true,
    _scroll: null,
    _isBottom: false,

    run: function() {
        var t = this;
        if (t.pageId()) {
            t.changePage(t.pageId(), true);
        }
    },
    changePage: function(pageId, force) {
        var t = this;
        if (force || (t._isCache && t.pageId() != pageId)) {
            t.pageId(pageId);
            t.renderTemplateLoading();
            t.scrollTop();
            t.loadData();
        }
    },
    renderTemplateLoading: function() {
        var t = this;
        t.el().html(t.tmpl()(t._templateLoading, t.model()));
        return this;
    },
    loadData: function() {
        var t = this;
        var deferred = Control.fire(t.serviceName(), t.serviceParams());

        t.lock();
        deferred.success(function() {
            t.unlock();
        });

        return deferred;
    },
    scrollTop: function() {
        $(window).scrollTop(0);
    },
    scrollBottom: function() {
        $(window).scrollTop($(document).height() - $(window).height());
    },
    show: function() {
        var t = this;
        t.visible(true);
        t.el().show();
        if (t._isBottom) {
            t.scrollBottom();
        } else {
            $(window).scrollTop(t.scroll());
        }
        return this;
    },
    hide: function() {
        var t = this;
        t.visible(false);
        t.scroll($(window).scrollTop());
        t._isBottom = $(window).scrollTop() + $(window).height() == $(document).height();
        t.el().hide();
        return this;
    },
    isLock: function() {
        return !!this._isLock;
    },
    lock: function() {
        this._isLock = true;
    },
    unlock: function() {
        this._isLock = false;
    },
    isVisible: function() {
        return this.visible();
    },
    visible: function(visible) {
        if (arguments.length) {
            this._isVisible = visible;
            return this;
        } else {
            return !!this._isVisible;
        }
    },
    pageId: function(pageId) {
        if (arguments.length) {
            this._pageId = pageId;
            return this;
        } else {
            return this._pageId;
        }
    },
    scroll: function(scroll) {
        if (arguments.length) {
            this._scroll = scroll;
            return this;
        } else {
            return intval(this._scroll);
        }
    },
    serviceName: function(serviceName) {
        if (arguments.length) {
            this._service = serviceName + '';
            return this;
        } else {
            return this._service;
        }
    },
    serviceParams: function(serviceParams) {
        if (arguments.length) {
            this._params = serviceParams;
            return this;
        } else {
            return this._params;
        }
    }
});
