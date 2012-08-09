var Widget = Event.extend({
    init: function(options) {
        var t = this;

        t.configure(options);
        t.bindEvents();
        t.run();
    },

    configure: function(options) {
        var t = this;

        t.options =      options          || t.options      || $.error('options not found');
        t.el =           options.el       || t.el           || $.error('el not found');
        t.$el =          options.$el      || t.$el          || $(t.el);
        t.events =       options.events   || t.events       || {};
        t.template =     options.template || t.template     || '';
        t.templateData = options.data     || t.data         || {};
    },

    bindEvents: function() {
        var t = this;

        //todo: переделать без jQuery
        $.each(t.events, function(event, methodName) {
            var eventName = event.split(':')[0];
            var selector = event.split(':')[1];
            var eventMethod = $.proxy(t[methodName], t);
            t.$el.delegate(selector, eventName, eventMethod);
        });
    },

    renderTemplate: function() {
        var t = this;

        t.$el.html(tmpl(t.template, t.templateData));
    },

    run: function() {
        var t = this;

        t.renderTemplate();
    }
});