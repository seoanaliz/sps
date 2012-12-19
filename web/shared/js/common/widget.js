var Widget = (function() {
    var widgetId = 0;
    var eventNameSpace = 'widget';
    var eventSplitter = ':';

    return Event.extend({
        _options: null,
        _el: null,
        _id: null,
        _tmpl: null,
        _model: null,
        _events: null,
        _template: null,
        _selector: null,
        _modelClass: null,

        init: function(options) {
            var t = this;

            t.options(options || {});
            t._bindEvents();
            t.run();

            return this;
        },

        _bindEvents: function() {
            var t = this;
            var events = t.events();
            for (var event in events) {
                if (!events.hasOwnProperty(event)) continue;
                var methodName = events[event];
                var eventName = event.split(eventSplitter)[0];
                var selector = event.split(eventSplitter)[1];
                var eventMethod = $.proxy(t[methodName], t);
                t.el().delegate(selector, eventName + '.' + eventNameSpace + t.id(), eventMethod);
            }
            return this;
        },

        run: function() {
            var t = this;
            t.renderTemplate();
            return this;
        },

        renderTemplate: function() {
            var t = this;
            t.el().html(t.tmpl()(t.template(), t.model()));
            return this;
        },

        destroy: function() {
            var t = this;
            t.el().undelegate('.' + eventNameSpace + t.id).empty();
            return this;
        },

        el: function(el) {
            var t = this;
            if (!arguments.length) {
                return t._el;
            } else {
                t._el = el;
                return t;
            }
        },

        id: function(id) {
            var t = this;
            if (!arguments.length) {
                return t._id;
            } else {
                t._id = id;
                return t;
            }
        },

        options: function(options) {
            var t = this;
            if (!arguments.length) {
                return t._options;
            } else {
                var throughParams = ['template', 'model', 'modelClass', 'events'];

                function tmplWrapper(template, data) {
                    var tmplData = $.extend(true, {}, data);
                    if (data instanceof Model) {
                        tmplData = tmplData.data();
                    }
                    (function getModelData(data) {
                        if (typeof data == 'object') {
                            for (var i in data) {
                                if (!data.hasOwnProperty(i)) continue;
                                if (data[i] instanceof Model) {
                                    data[i] = data[i].data();
                                } else {
                                    getModelData(data[i])
                                }
                            }
                        }
                    })(tmplData);
                    return tmpl(template, tmplData);
                }

                for (var i in throughParams) {
                    if (!throughParams.hasOwnProperty(i)) {
                        continue;
                    }
                    var optionKey = throughParams[i];
                    var paramKey = '_' + throughParams[i];
                    var param = t[paramKey] || t.constructor.prototype[paramKey];
                    if (param) {
                        options[optionKey] = param;
                    }
                }

                if (!options.template) {
                    throw new TypeError('Template not found');
                }
                if (!options.selector) {
                    throw new TypeError('Selector not found');
                }
                if (!options.model && options.modelClass) {
                    throw new TypeError('Model not found');
                }
                t.id(options.id || (widgetId = widgetId + 1));
                t.tmpl(options.tmpl || tmplWrapper);
                t.modelClass(options.modelClass);
                t.events(options.events);
                t.template(options.template);
                t.selector(options.selector);
                if (t.modelClass()) {
                    t.model(options.model);
                }
                t._options = options;
                return t;
            }
        },

        events: function(events) {
            var t = this;
            if (!arguments.length) {
                return t._events;
            } else {
                t._events = events;
                return t;
            }
        },

        tmpl: function(tmpl) {
            var t = this;
            if (!arguments.length) {
                return t._tmpl;
            } else {
                t._tmpl = tmpl;
                return t;
            }
        },

        model: function(model) {
            var t = this;
            if (!arguments.length) {
                return t._model;
            } else {
                if (!(model instanceof t.modelClass())) {
                    throw new TypeError('Model is not correct');
                }
                t._model = model;
                return t;
            }
        },

        modelClass: function(modelClass) {
            var t = this;
            if (!arguments.length) {
                return t._modelClass;
            } else {
                t._modelClass = modelClass;
                return t;
            }
        },

        selector: function(selector) {
            var t = this;
            if (!arguments.length) {
                return t._selector;
            } else {
                t.el($(selector));
                t._selector = selector;
                return t;
            }
        },

        template: function(template) {
            var t = this;
            if (!arguments.length) {
                return t._template;
            } else {
                t._template = template;
                return t;
            }
        }
    });
})();
