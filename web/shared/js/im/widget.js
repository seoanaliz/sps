var Widget = (function() {
    var widgetId = 0;
    var eventNameSpace = 'widget';
    var eventSplitter = ':';

    return Event.extend({
        init: function(options) {
            var t = this;

            t._configure(options);
            t._bindEvents();
            t.run();

            return this;
        },

        _configure: function(options) {
            var t = this;

            t.options = options;

            if (!t.options) throw new TypeError('Options not found');

            t.id = widgetId++;
            t.model = options.model || t.model;
            t.events = options.events || t.events || {};
            t.template = options.template || t.template;
            t.selector = options.selector || t.selector;
            t.modelClass = options.modelClass || t.modelClass;

            if (!t.template) throw new TypeError('Template not found');
            if (!t.selector) throw new TypeError('Selector not found');
            if (t.modelClass && !t.model) throw new TypeError('Model not found');
            if (t.model && !(t.model instanceof t.modelClass)) throw new TypeError('Model is not correct');

            t.$el = $(t.selector);

            return this;
        },

        _bindEvents: function() {
            var t = this;

            for (var event in t.events) {
                if (!t.events.hasOwnProperty(event)) continue;

                var methodName = t.events[event];
                var eventName = event.split(eventSplitter)[0];
                var selector = event.split(eventSplitter)[1];
                var eventMethod = $.proxy(t[methodName], t);
                t.$el.delegate(selector, eventName + '.' + eventNameSpace + t.id, eventMethod);
            }

            return this;
        },

        renderTemplate: function() {
            var t = this;

            t.$el.html(t.tmpl(t.template, (t.model && t.model.data())));

            return this;
        },

        run: function(runData) {
            var t = this;

            t.renderTemplate();

            return this;
        },

        destroy: function() {
            var t = this;

            t.$el.undelegate('.' + eventNameSpace + t.id).empty();

            delete this;
        },

        tmpl: tmpl
    });
})();