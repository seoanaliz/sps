var Widget = Event.extend({
    init: function(options) {
        var t = this;

        t._configure(options);
        t._bindEvents();
        t.run(t.runData);

        return this;
    },

    _configure: function(options) {
        var t = this;

        t.options =      options                  || t.options              || $.error('options not found');
        t.el =           options.el               || t.el                   || $.error('el not found');
        t.events =       options.events           || t.events               || {};
        t.template =     options.template         || t.template             || '';
        t.templateData = options.templateData     || t.templateData         || {};
        t.runData =      options.runData          || t.runData              || {};

        t.run = options.run || t.run;
        t.renderTemplate = options.renderTemplate || t.renderTemplate;

        return this;
    },

    _bindEvents: function() {
        var t = this;

        $.each(t.events, function(event, methodName) {
            var eventName = event.split(':')[0];
            var selector = event.split(':')[1];
            var eventMethod = $.proxy(t[methodName], t);
            $(t.el).delegate(selector, eventName + '.widget', eventMethod);
        });

        return this;
    },

    renderTemplate: function(el) {
        var t = this;

        $(el || t.el).html(tmpl(t.template, t.templateData));

        return this;
    },

    run: function(runData) {
        var t = this;

        t.renderTemplate();

        return this;
    },

    destroy: function() {
        var t = this;

        $(t.el).off().empty();

        delete this;
    }
});
