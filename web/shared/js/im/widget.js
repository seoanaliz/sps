var Widget = (function() {
    var widgetId = 0;
    var eventNameSpace = 'widget';
    var eventSplitter = ':';
    var template = tmpl;

    return Event.extend({
        init: function(options) {
            var t = this;

            t._configure(options);
            t._bindEvents();
            t.run(options);

            return this;
        },

        _configure: function(options) {
            var t = this;

            t.options = options || $.error('Options not found.');

            t.id = widgetId++;
            t.el = options.el || t.el || $.error('El not found.');
            t.model = options.model || t.model || {};
            t.events = options.events || t.events || {};
            t.template = options.template || t.template || '';
            t.templateData = options.templateData || t.templateData || {};

            t.$el = $(t.el);

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

            t.$el.html(template(t.template, t.templateData));

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
        }
    });
})();