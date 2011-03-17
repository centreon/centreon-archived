/*!
 *  script.aculo.us version 2.0.0_b1
 *  (c) 2005-2010 Thomas Fuchs
 *
 *  script.aculo.us is freely distributable under the terms of an MIT-style license.
 *----------------------------------------------------------------------------------*/



var S2 = {
  Version: '2.0.0_b1',

  Extensions: {}
};


Function.prototype.optionize = function(){
  var self = this, argumentNames = self.argumentNames(), optionIndex = this.length - 1;

  var method = function() {
    var args = $A(arguments);

    var options = (typeof args.last() === 'object') ? args.pop() : {};
    var prefilledArgs = [];
    if (optionIndex > 0) {
      prefilledArgs = ((args.length > 0 ? args : [null]).inGroupsOf(
       optionIndex).flatten()).concat(options);
    }

    return self.apply(this, prefilledArgs);
  };
  method.argumentNames = function() { return argumentNames; };
  return method;
};

Function.ABSTRACT = function() {
  throw "Abstract method. Implement in subclass.";
};

Object.extend(Number.prototype, {
  constrain: function(n1, n2) {
    var min = (n1 < n2) ? n1 : n2;
    var max = (n1 < n2) ? n2 : n1;

    var num = Number(this);

    if (num < min) num = min;
    if (num > max) num = max;

    return num;
  },

  nearer: function(n1, n2) {
    var num = Number(this);

    var diff1 = Math.abs(num - n1);
    var diff2 = Math.abs(num - n2);

    return (diff1 < diff2) ? n1 : n2;
  },

  tween: function(target, position) {
    return this + (target-this) * position;
  }
});


Object.propertize = function(property, object){
  return Object.isString(property) ? object[property] : property;
};

S2.CSS = {
  PROPERTY_MAP: {
    backgroundColor: 'color',
    borderBottomColor: 'color',
    borderBottomWidth: 'length',
    borderLeftColor: 'color',
    borderLeftWidth: 'length',
    borderRightColor: 'color',
    borderRightWidth: 'length',
    borderSpacing: 'length',
    borderTopColor: 'color',
    borderTopWidth: 'length',
    bottom: 'length',
    color: 'color',
    fontSize: 'length',
    fontWeight: 'integer',
    height: 'length',
    left: 'length',
    letterSpacing: 'length',
    lineHeight: 'length',
    marginBottom: 'length',
    marginLeft: 'length',
    marginRight: 'length',
    marginTop: 'length',
    maxHeight: 'length',
    maxWidth: 'length',
    minHeight: 'length',
    minWidth: 'length',
    opacity: 'number',
    outlineColor: 'color',
    outlineOffset: 'length',
    outlineWidth: 'length',
    paddingBottom: 'length',
    paddingLeft: 'length',
    paddingRight: 'length',
    paddingTop: 'length',
    right: 'length',
    textIndent: 'length',
    top: 'length',
    width: 'length',
    wordSpacing: 'length',
    zIndex: 'integer',
    zoom: 'number'
  },

  VENDOR: {
    PREFIX: null,

    LOOKUP_PREFIXES: ['webkit', 'Moz', 'O'],
    LOOKUP_PROPERTIES: $w('BorderRadius BoxShadow Transform Transition ' +
     'TransitionDuration TransitionTimingFunction TransitionProperty ' +
     'TransitionDelay ' +
     'BorderTopLeftRadius BorderTopRightRadius BorderBottomLeftRadius ' +
     'BorderBottomRightRadius'
    ),
    LOOKUP_EDGE_CASES: {
      'BorderTopLeftRadius': 'BorderRadiusTopleft',
      'BorderTopRightRadius': 'BorderRadiusTopright',
      'BorderBottomLeftRadius': 'BorderRadiusBottomleft',
      'BorderBottomRightRadius': 'BorderRadiusBottomright'
    },

    PROPERTY_MAP: {}
  },

  LENGTH: /^(([\+\-]?[0-9\.]+)(em|ex|px|in|cm|mm|pt|pc|\%))|0$/,

  NUMBER: /([\+-]*\d+\.?\d*)/,

  __parseStyleElement: document.createElement('div'),

  parseStyle: function(styleString) {
    S2.CSS.__parseStyleElement.innerHTML = '<div style="' + styleString + '"></div>';
    var style = S2.CSS.__parseStyleElement.childNodes[0].style, styleRules = {};

    S2.CSS.NUMERIC_PROPERTIES.each( function(property){
      if (style[property]) styleRules[property] = style[property];
    });

    S2.CSS.COLOR_PROPERTIES.each( function(property){
      if (style[property]) styleRules[property] = S2.CSS.colorFromString(style[property]);
    });

    if (Prototype.Browser.IE && styleString.include('opacity'))
      styleRules.opacity = styleString.match(/opacity:\s*((?:0|1)?(?:\.\d*)?)/)[1];

    return styleRules;
  },

  parse: function(styleString) {
    return S2.CSS.parseStyle(styleString);
  },

  normalize: function(element, style) {
    if (Object.isHash(style)) style = style.toObject();
    if (typeof style === 'string') style = S2.CSS.parseStyle(style);

    var result = {}, current;

    for (var property in style) {
      current = element.getStyle(property);
      if (style[property] !== current) {
        result[property] = style[property];
      }
    }

    return result;
  },

  serialize: function(object) {
    if (Object.isHash(object)) object = object.toObject();
    var output = "", value;
    for (var property in object) {
      value = object[property];
      property = this.vendorizeProperty(property);
      output += (property + ": " + value + ';');
    }
    return output;
  },

  vendorizeProperty: function(property) {
    property = property.underscore().dasherize();

    if (property in S2.CSS.VENDOR.PROPERTY_MAP) {
      property = S2.CSS.VENDOR.PROPERTY_MAP[property];
    }

    return property;
  },

  normalizeColor: function(color) {
    if (!color || color == 'rgba(0, 0, 0, 0)' || color == 'transparent') color = '#ffffff';
    color = S2.CSS.colorFromString(color);
    return [
      parseInt(color.slice(1,3),16), parseInt(color.slice(3,5),16), parseInt(color.slice(5,7),16)
    ];
  },

  colorFromString: function(color) {
    var value = '#', cols, i;
    if (color.slice(0,4) == 'rgb(') {
      cols = color.slice(4,color.length-1).split(',');
      i=3; while(i--) value += parseInt(cols[2-i]).toColorPart();
    } else if (color.slice(0,1) == '#') {
        if (color.length==4) for(i=1;i<4;i++) value += (color.charAt(i) + color.charAt(i)).toLowerCase();
        if (color.length==7) value = color.toLowerCase();
    } else { value = color; }
    return (value.length==7 ? value : (arguments[1] || value));
  },

  interpolateColor: function(from, to, position){
    from = S2.CSS.normalizeColor(from);
    to = S2.CSS.normalizeColor(to);

    return '#' + [0,1,2].map(function(index){
      return Math.max(Math.min(from[index].tween(to[index], position).round(), 255), 0).toColorPart();
    }).join('');
  },

  interpolateNumber: function(from, to, position){
    return 1*((from||0).tween(to, position).toFixed(3));
  },

  interpolateLength: function(from, to, position){
    if (!from || parseFloat(from) === 0) {
      from = '0' + to.gsub(S2.CSS.NUMBER,'');
    }
    to.scan(S2.CSS.NUMBER, function(match){ to = 1*(match[1]); });
    return from.gsub(S2.CSS.NUMBER, function(match){
      return (1*(parseFloat(match[1]).tween(to, position).toFixed(3))).toString();
    });
  },

  interpolateInteger: function(from, to, position){
    return parseInt(from).tween(to, position).round();
  },

  interpolate: function(property, from, to, position){
    return S2.CSS['interpolate'+S2.CSS.PROPERTY_MAP[property.camelize()].capitalize()](from, to, position);
  },

  ElementMethods: {
    getStyles: function(element) {
      var css = document.defaultView.getComputedStyle($(element), null);
      return S2.CSS.PROPERTIES.inject({ }, function(styles, property) {
        styles[property] = css[property];
        return styles;
      });
    }
  }
};

S2.CSS.PROPERTIES = [];
for (var property in S2.CSS.PROPERTY_MAP) S2.CSS.PROPERTIES.push(property);

S2.CSS.NUMERIC_PROPERTIES = S2.CSS.PROPERTIES.findAll(function(property){ return !property.endsWith('olor') });
S2.CSS.COLOR_PROPERTIES   = S2.CSS.PROPERTIES.findAll(function(property){ return property.endsWith('olor') });

if (!(document.defaultView && document.defaultView.getComputedStyle)) {
  S2.CSS.ElementMethods.getStyles = function(element) {
    element = $(element);
    var css = element.currentStyle, styles;
    styles = S2.CSS.PROPERTIES.inject({ }, function(hash, property) {
      hash[property] = css[property];
      return hash;
    });
    if (!styles.opacity) styles.opacity = element.getOpacity();
    return styles;
  };
};

Element.addMethods(S2.CSS.ElementMethods);

(function() {
  var div = document.createElement('div');
  var style = div.style, prefix = null;
  var edgeCases = S2.CSS.VENDOR.LOOKUP_EDGE_CASES;
  var uncamelize = function(prop, prefix) {
    if (prefix) {
      prop = '-' + prefix.toLowerCase() + '-' + uncamelize(prop);
    }
    return prop.underscore().dasherize();
  }

  S2.CSS.VENDOR.LOOKUP_PROPERTIES.each(function(prop) {
    if (!prefix) { // We attempt to detect a prefix
      prefix = S2.CSS.VENDOR.LOOKUP_PREFIXES.detect( function(p) {
        return !Object.isUndefined(style[p + prop]);
      });
    }
    if (prefix) { // If we detected a prefix
      if ((prefix + prop) in style) {
        S2.CSS.VENDOR.PROPERTY_MAP[uncamelize(prop)] = uncamelize(prop, prefix);
      } else if (prop in edgeCases && (prefix + edgeCases[prop]) in style) {
        S2.CSS.VENDOR.PROPERTY_MAP[uncamelize(prop)] = uncamelize(edgeCases[prop], prefix);
      }
    }
  });

  S2.CSS.VENDOR.PREFIX = prefix;

  div = null;
})();

S2.FX = (function(){
  var queues = [], globalQueue,
    heartbeat, activeEffects = 0;

  function beatOnDemand(dir) {
    activeEffects += dir;
    if (activeEffects > 0) heartbeat.start();
    else heartbeat.stop();
  }

  function renderQueues() {
    var timestamp = heartbeat.getTimestamp();
    for (var i = 0, queue; queue = queues[i]; i++) {
      queue.render(timestamp);
    }
  }

  function initialize(initialHeartbeat){
    if (globalQueue) return;
    queues.push(globalQueue = new S2.FX.Queue());
    S2.FX.DefaultOptions.queue = globalQueue;
    heartbeat = initialHeartbeat || new S2.FX.Heartbeat();

    document
      .observe('effect:heartbeat', renderQueues)
      .observe('effect:queued',    beatOnDemand.curry(1))
      .observe('effect:dequeued',  beatOnDemand.curry(-1));
  }

  function formatTimestamp(timestamp) {
    if (!timestamp) timestamp = (new Date()).valueOf();
    var d = new Date(timestamp);
    return d.getSeconds() + '.' + d.getMilliseconds() + 's';
  }

  return {
    initialize:   initialize,
    getQueues:    function() { return queues; },
    addQueue:     function(queue) { queues.push(queue); },
    getHeartbeat: function() { return heartbeat; },
    setHeartbeat: function(newHeartbeat) { heartbeat = newHeartbeat; },
    formatTimestamp: formatTimestamp
  }
})();

Object.extend(S2.FX, {
  DefaultOptions: {
    transition: 'sinusoidal',
    position:   'parallel',
    fps:        60,
    duration:   .2
  },

  elementDoesNotExistError: {
    name: 'ElementDoesNotExistError',
    message: 'The specified DOM element does not exist, but is required for this effect to operate'
  },

  parseOptions: function(options) {
    if (Object.isNumber(options))
      options = { duration: options };
    else if (Object.isFunction(options))
      options = { after: options };
    else if (Object.isString(options))
      options = { duration: options == 'slow' ? 1 : options == 'fast' ? .1 : .2 };

    return options || {};
  },

  ready: function(element) {
    if (!element) return;
    var table = this._ready;
    var uid = element._prototypeUID;
    if (!uid) return true;

    bool = table[uid];
    return Object.isUndefined(bool) ? true : bool;
  },

  setReady: function(element, bool) {
    if (!element) return;
    var table = this._ready, uid = element._prototypeUID;
    if (!uid) {
      element.getStorage();
      uid = element._prototypeUID;
    }

    table[uid] = bool;
  },

  _ready: {}
});

S2.FX.Base = Class.create({
  initialize: function(options) {
    S2.FX.initialize();
    this.updateWithoutWrappers = this.update;

    if(options && options.queue && !S2.FX.getQueues().include(options.queue))
      S2.FX.addQueue(options.queue);

    this.setOptions(options);
    this.duration = this.options.duration*1000;
    this.state = 'idle';

    ['after','before'].each(function(method) {
      this[method] = function(method) {
        method(this);
        return this;
      }
    }, this);
  },

  setOptions: function(options) {
    options = S2.FX.parseOptions(options);

    this.options = Object.extend(this.options || Object.extend({}, S2.FX.DefaultOptions), options);

    if (options.tween) this.options.transition = options.tween;

    if (this.options.beforeUpdate || this.options.afterUpdate) {
      this.update = this.updateWithoutWrappers.wrap( function(proceed,position){
        if (this.options.beforeUpdate) this.options.beforeUpdate(this, position);
        proceed(position);
        if (this.options.afterUpdate) this.options.afterUpdate(this, position);
      }.bind(this));
    }

    if (this.options.transition === false)
      this.options.transition = S2.FX.Transitions.linear;

    this.options.transition = Object.propertize(this.options.transition, S2.FX.Transitions);
  },

  play: function(options) {
    this.setOptions(options);
    this.frameCount = 0;
    this.state = 'idle';
    this.options.queue.add(this);
    this.maxFrames = this.options.fps * this.duration / 1000;
    return this;
  },

  render: function(timestamp) {
    if (this.options.debug) {
    }
    if (timestamp >= this.startsAt) {
      if (this.state == 'idle' && (!this.element || S2.FX.ready(this.element))) {
        this.debug('starting the effect at ' + S2.FX.formatTimestamp(timestamp));
        this.endsAt = this.startsAt + this.duration;
        this.start();
        this.frameCount++;
        return this;
      }

      if (timestamp >= this.endsAt && this.state !== 'finished') {
        this.debug('stopping the effect at ' + S2.FX.formatTimestamp(timestamp));
        this.finish();
        return this;
      }

      if (this.state === 'running') {
        var position = 1 - (this.endsAt - timestamp) / this.duration;
        if ((this.maxFrames * position).floor() > this.frameCount) {
          this.update(this.options.transition(position));
          this.frameCount++;
        }
      }
    }
    return this;
  },

  start: function() {
    if (this.options.before) this.options.before(this);
    if (this.setup) this.setup();
    this.state = 'running';
    this.update(this.options.transition(0));
  },

  cancel: function(after) {
    if (this.state !== 'running') return;
    if (this.teardown) this.teardown();
    if (after && this.options.after) this.options.after(this);
    this.state = 'finished';
  },

  finish: function() {
    if (this.state !== 'running') return;
    this.update(this.options.transition(1));
    this.cancel(true);
  },

  inspect: function() {
    return '#<S2.FX:' + [this.state, this.startsAt, this.endsAt].inspect() + '>';
  },

  update: Prototype.emptyFunction,

  debug: function(message) {
    if (!this.options.debug) return;
    if (window.console && console.log) {
      console.log(message);
    }
  }
});

S2.FX.Element = Class.create(S2.FX.Base, {
  initialize: function($super, element, options) {
    if(!(this.element = $(element)))
      throw(S2.FX.elementDoesNotExistError);
    this.operators = [];
    return $super(options);
  },

  animate: function() {
    var args = $A(arguments), operator =  args.shift();
    operator = operator.charAt(0).toUpperCase() + operator.substring(1);
    this.operators.push(new S2.FX.Operators[operator](this, args[0], args[1] || {}));
  },

  play: function($super, element, options) {
    if (element) this.element = $(element);
    this.operators = [];
    return $super(options);
  },

  update: function(position) {
    for (var i = 0, operator; operator = this.operators[i]; i++) {
      operator.render(position);
    }
  }
});
S2.FX.Heartbeat = Class.create({
  initialize: function(options) {
    this.options = Object.extend({
      framerate: Prototype.Browser.MobileSafari ? 20 : 60
    }, options);
    this.beat = this.beat.bind(this);
  },

  start: function() {
    if (this.heartbeatInterval) return;
    this.heartbeatInterval =
      setInterval(this.beat, 1000/this.options.framerate);
    this.updateTimestamp();
  },

  stop: function() {
    if (!this.heartbeatInterval) return;
    clearInterval(this.heartbeatInterval);
    this.heartbeatInterval = null;
    this.timestamp = null;
  },

  beat: function() {
    this.updateTimestamp();
    document.fire('effect:heartbeat');
  },

  getTimestamp: function() {
    return this.timestamp || this.generateTimestamp();
  },

  generateTimestamp: function() {
    return new Date().getTime();
  },

  updateTimestamp: function() {
    this.timestamp = this.generateTimestamp();
  }
});
S2.FX.Queue = (function(){
  return function() {
    var instance = this;
    var effects = [];

    function getEffects() {
      return effects;
    }

    function active() {
      return effects.length > 0;
    }

    function add(effect) {
      calculateTiming(effect);
      effects.push(effect);
      document.fire('effect:queued', instance);
      return instance;
    }

    function remove(effect) {
      effects = effects.without(effect);
      document.fire('effect:dequeued', instance);
      delete effect;
      return instance;
    }

    function render(timestamp) {
      for (var i = 0, effect; effect = effects[i]; i++) {
        effect.render(timestamp);
        if (effect.state === 'finished') remove(effect);
      }

      return instance;
    }

    function calculateTiming(effect) {
      var position = effect.options.position || 'parallel',
        now = S2.FX.getHeartbeat().getTimestamp();

      var startsAt;
      if (position === 'end') {
        startsAt = effects.without(effect).pluck('endsAt').max() || now;
        if (startsAt < now) startsAt = now;
      } else {
        startsAt = now;
      }

      var delay = (effect.options.delay || 0) * 1000;
      effect.startsAt = startsAt + delay;

      var duration = (effect.options.duration || 1) * 1000;
      effect.endsAt = effect.startsAt + duration;
    }

    Object.extend(instance, {
      getEffects: getEffects,
      active: active,
      add: add,
      remove: remove,
      render: render
    });
  }
})();

S2.FX.Attribute = Class.create(S2.FX.Base, {
  initialize: function($super, object, from, to, options, method) {
    object = Object.isString(object) ? $(object) : object;

    this.method = Object.isFunction(method) ? method.bind(object) :
      Object.isFunction(object[method]) ? object[method].bind(object) :
      function(value) { object[method] = value };

    this.to = to;
    this.from = from;

    return $super(options);
  },

  update: function(position) {
    this.method(this.from.tween(this.to, position));
  }
});
S2.FX.Style = Class.create(S2.FX.Element, {
  setup: function() {
    this.animate('style', this.element, { style: this.options.style });
  }
});
S2.FX.Operators = { };

S2.FX.Operators.Base = Class.create({
  initialize: function(effect, object, options) {
    this.effect = effect;
    this.object = object;
    this.options = Object.extend({
      transition: Prototype.K
    }, options);
  },

  inspect: function() {
    return "#<S2.FX.Operators.Base:" + this.lastValue + ">";
  },

  setup: function() {
  },

  valueAt: function(position) {
  },

  applyValue: function(value) {
  },

  render: function(position) {
    var value = this.valueAt(this.options.transition(position));
    this.applyValue(value);
    this.lastValue = value;
  }
});

S2.FX.Operators.Style = Class.create(S2.FX.Operators.Base, {
  initialize: function($super, effect, object, options) {
    $super(effect, object, options);
    this.element = $(this.object);

    this.style = Object.isString(this.options.style) ?
      S2.CSS.parseStyle(this.options.style) : this.options.style;

    var translations = this.options.propertyTransitions || {};

    this.tweens = [];

    for (var item in this.style) {
      var property = item.underscore().dasherize(),
       from = this.element.getStyle(property), to = this.style[item];

      if (from != to) {
        this.tweens.push([
          property,
          S2.CSS.interpolate.curry(property, from, to),
          item in translations ?
           Object.propertize(translations[item], S2.FX.Transitions) :
           Prototype.K
        ]);
      }
    }
  },

  valueAt: function(position) {
    return this.tweens.map( function(tween){
      return tween[0] + ':' + tween[1](tween[2](position));
    }).join(';');
  },

  applyValue: function(value) {
    if (this.currentStyle == value) return;
    this.element.setStyle(value);
    this.currentStyle = value;
  }
});

S2.FX.Morph = Class.create(S2.FX.Element, {
  setup: function() {
    if (this.options.change)
      this.setupWrappers();
    else if (this.options.style)
      this.animate('style', this.destinationElement || this.element, {
        style: this.options.style,
        propertyTransitions: this.options.propertyTransitions || { }
      });
  },

  teardown: function() {
    if (this.options.change)
      this.teardownWrappers();
  },

  setupWrappers: function() {
    var elementFloat = this.element.getStyle("float"),
      sourceHeight, sourceWidth,
      destinationHeight, destinationWidth,
      maxHeight;

    this.transitionElement = new Element('div').setStyle({ position: "relative", overflow: "hidden", 'float': elementFloat });
    this.element.setStyle({ 'float': "none" }).insert({ before: this.transitionElement });

    this.sourceElementWrapper = this.element.cloneWithoutIDs().wrap('div');
    this.destinationElementWrapper = this.element.wrap('div');

    this.transitionElement.insert(this.sourceElementWrapper).insert(this.destinationElementWrapper);

    sourceHeight = this.sourceElementWrapper.getHeight();
    sourceWidth = this.sourceElementWrapper.getWidth();

    this.options.change();

    destinationHeight = this.destinationElementWrapper.getHeight();
    destinationWidth  = this.destinationElementWrapper.getWidth();

    this.outerWrapper = new Element("div");
    this.transitionElement.insert({ before: this.outerWrapper });
    this.outerWrapper.setStyle({
      overflow: "hidden", height: sourceHeight + "px", width: sourceWidth + "px"
    }).appendChild(this.transitionElement);

    maxHeight = Math.max(destinationHeight, sourceHeight), maxWidth = Math.max(destinationWidth, sourceWidth);

    this.transitionElement.setStyle({ height: sourceHeight + "px", width: sourceWidth + "px" });
    this.sourceElementWrapper.setStyle({ position: "absolute", height: maxHeight + "px", width: maxWidth + "px", top: 0, left: 0 });
    this.destinationElementWrapper.setStyle({ position: "absolute", height: maxHeight + "px", width: maxWidth + "px", top: 0, left: 0, opacity: 0, zIndex: 2000 });

    this.outerWrapper.insert({ before: this.transitionElement }).remove();

    this.animate('style', this.transitionElement, { style: 'height:' + destinationHeight + 'px; width:' + destinationWidth + 'px' });
    this.animate('style', this.destinationElementWrapper, { style: 'opacity: 1.0' });
  },

  teardownWrappers: function() {
    var destinationElement = this.destinationElementWrapper.down();

    if (destinationElement)
      this.transitionElement.insert({ before: destinationElement });

    this.transitionElement.remove();
  }
});
S2.FX.Parallel = Class.create(S2.FX.Base, {
  initialize: function($super, effects, options) {
    this.effects = effects || [];
    return $super(options || {});
  },

  setup: function() {
    this.effects.invoke('setup');
  },

  update: function(position) {
    this.effects.invoke('update', position);
  }
});

S2.FX.Operators.Scroll = Class.create(S2.FX.Operators.Base, {
  initialize: function($super, effect, object, options) {
    $super(effect, object, options);
    this.start = object.scrollTop;
    this.end = this.options.scrollTo;
  },

  valueAt: function(position) {
    return this.start + ((this.end - this.start)*position);
  },

  applyValue: function(value){
    this.object.scrollTop = value.round();
  }
});

S2.FX.Scroll = Class.create(S2.FX.Element, {
  setup: function() {
    this.animate('scroll', this.element, { scrollTo: this.options.to });
  }
});

S2.FX.SlideDown = Class.create(S2.FX.Element, {
  setup: function() {
    var element = this.destinationElement || this.element;
    var layout = element.getLayout();

    var style = {
      height:        layout.get('height') + 'px',
      paddingTop:    layout.get('padding-top') + 'px',
      paddingBottom: layout.get('padding-bottom') + 'px'
    };

    element.setStyle({
      height:         '0',
      paddingTop:     '0',
      paddingBottom:  '0',
      overflow:       'hidden'
    }).show();

    this.animate('style', element, {
      style: style,
      propertyTransitions: {}
    });
  },

  teardown: function() {
    var element = this.destinationElement || this.element;
    element.setStyle({
      height:         '',
      paddingTop:     '',
      paddingBottom:  '',
      overflow:       'visible'
    });
  }
});

S2.FX.SlideUp = Class.create(S2.FX.Morph, {
  setup: function() {
    var element = this.destinationElement || this.element;
    var layout = element.getLayout();

    var style = {
      height:        '0px',
      paddingTop:    '0px',
      paddingBottom: '0px'
    };

    element.setStyle({ overflow: 'hidden' });

    this.animate('style', element, {
      style: style,
      propertyTransitions: {}
    });
  },

  teardown: function() {
    var element = this.destinationElement || this.element;
    element.setStyle({
      height:         '',
      paddingTop:     '',
      paddingBottom:  '',
      overflow:       'visible'
    }).hide();
  }
});


S2.FX.Transitions = {

  linear: Prototype.K,

  sinusoidal: function(pos) {
    return (-Math.cos(pos*Math.PI)/2) + 0.5;
  },

  reverse: function(pos) {
    return 1 - pos;
  },

  mirror: function(pos, transition) {
    transition = transition || S2.FX.Transitions.sinusoidal;
    if(pos<0.5)
      return transition(pos*2);
    else
      return transition(1-(pos-0.5)*2);
  },

  flicker: function(pos) {
    var pos = pos + (Math.random()-0.5)/5;
    return S2.FX.Transitions.sinusoidal(pos < 0 ? 0 : pos > 1 ? 1 : pos);
  },

  wobble: function(pos) {
    return (-Math.cos(pos*Math.PI*(9*pos))/2) + 0.5;
  },

  pulse: function(pos, pulses) {
    return (-Math.cos((pos*((pulses||5)-.5)*2)*Math.PI)/2) + .5;
  },

  blink: function(pos, blinks) {
    return Math.round(pos*(blinks||5)) % 2;
  },

  spring: function(pos) {
    return 1 - (Math.cos(pos * 4.5 * Math.PI) * Math.exp(-pos * 6));
  },

  none: Prototype.K.curry(0),

  full: Prototype.K.curry(1)
};

/*!
 *  Copyright (c) 2006 Apple Computer, Inc. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 *
 *  1. Redistributions of source code must retain the above copyright notice,
 *  this list of conditions and the following disclaimer.
 *
 *  2. Redistributions in binary form must reproduce the above copyright notice,
 *  this list of conditions and the following disclaimer in the documentation
 *  and/or other materials provided with the distribution.
 *
 *  3. Neither the name of the copyright holder(s) nor the names of any
 *  contributors may be used to endorse or promote products derived from
 *  this software without specific prior written permission.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 *  THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 *  ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 *  FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

(function(){
  function CubicBezierAtTime(t,p1x,p1y,p2x,p2y,duration) {
    var ax=0,bx=0,cx=0,ay=0,by=0,cy=0;
    function sampleCurveX(t) {return ((ax*t+bx)*t+cx)*t;};
    function sampleCurveY(t) {return ((ay*t+by)*t+cy)*t;};
    function sampleCurveDerivativeX(t) {return (3.0*ax*t+2.0*bx)*t+cx;};
    function solveEpsilon(duration) {return 1.0/(200.0*duration);};
    function solve(x,epsilon) {return sampleCurveY(solveCurveX(x,epsilon));};
    function fabs(n) {if(n>=0) {return n;}else {return 0-n;}};
    function solveCurveX(x,epsilon) {
      var t0,t1,t2,x2,d2,i;
      for(t2=x, i=0; i<8; i++) {x2=sampleCurveX(t2)-x; if(fabs(x2)<epsilon) {return t2;} d2=sampleCurveDerivativeX(t2); if(fabs(d2)<1e-6) {break;} t2=t2-x2/d2;}
      t0=0.0; t1=1.0; t2=x; if(t2<t0) {return t0;} if(t2>t1) {return t1;}
      while(t0<t1) {x2=sampleCurveX(t2); if(fabs(x2-x)<epsilon) {return t2;} if(x>x2) {t0=t2;}else {t1=t2;} t2=(t1-t0)*.5+t0;}
      return t2; // Failure.
    };
    cx=3.0*p1x; bx=3.0*(p2x-p1x)-cx; ax=1.0-cx-bx; cy=3.0*p1y; by=3.0*(p2y-p1y)-cy; ay=1.0-cy-by;
    return solve(t, solveEpsilon(duration));
  }
  S2.FX.cubicBezierTransition = function(x1, y1, x2, y2){
    return (function(pos){
      return CubicBezierAtTime(pos,x1,y1,x2,y2,1);
    });
  }
})();

S2.FX.Transitions.webkitCubic =
  S2.FX.cubicBezierTransition(0.25,0.1,0.25,1);
S2.FX.Transitions.webkitEaseInOut =
  S2.FX.cubicBezierTransition(0.42,0.0,0.58,1.0);

/*!
 *  TERMS OF USE - EASING EQUATIONS
 *  Open source under the BSD License.
 *  Easing Equations (c) 2003 Robert Penner, all rights reserved.
 */

Object.extend(S2.FX.Transitions, {
  easeInQuad: function(pos){
     return Math.pow(pos, 2);
  },

  easeOutQuad: function(pos){
    return -(Math.pow((pos-1), 2) -1);
  },

  easeInOutQuad: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,2);
    return -0.5 * ((pos-=2)*pos - 2);
  },

  easeInCubic: function(pos){
    return Math.pow(pos, 3);
  },

  easeOutCubic: function(pos){
    return (Math.pow((pos-1), 3) +1);
  },

  easeInOutCubic: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,3);
    return 0.5 * (Math.pow((pos-2),3) + 2);
  },

  easeInQuart: function(pos){
    return Math.pow(pos, 4);
  },

  easeOutQuart: function(pos){
    return -(Math.pow((pos-1), 4) -1)
  },

  easeInOutQuart: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,4);
    return -0.5 * ((pos-=2)*Math.pow(pos,3) - 2);
  },

  easeInQuint: function(pos){
    return Math.pow(pos, 5);
  },

  easeOutQuint: function(pos){
    return (Math.pow((pos-1), 5) +1);
  },

  easeInOutQuint: function(pos){
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,5);
    return 0.5 * (Math.pow((pos-2),5) + 2);
  },

  easeInSine: function(pos){
    return -Math.cos(pos * (Math.PI/2)) + 1;
  },

  easeOutSine: function(pos){
    return Math.sin(pos * (Math.PI/2));
  },

  easeInOutSine: function(pos){
    return (-.5 * (Math.cos(Math.PI*pos) -1));
  },

  easeInExpo: function(pos){
    return (pos==0) ? 0 : Math.pow(2, 10 * (pos - 1));
  },

  easeOutExpo: function(pos){
    return (pos==1) ? 1 : -Math.pow(2, -10 * pos) + 1;
  },

  easeInOutExpo: function(pos){
    if(pos==0) return 0;
    if(pos==1) return 1;
    if((pos/=0.5) < 1) return 0.5 * Math.pow(2,10 * (pos-1));
    return 0.5 * (-Math.pow(2, -10 * --pos) + 2);
  },

  easeInCirc: function(pos){
    return -(Math.sqrt(1 - (pos*pos)) - 1);
  },

  easeOutCirc: function(pos){
    return Math.sqrt(1 - Math.pow((pos-1), 2))
  },

  easeInOutCirc: function(pos){
    if((pos/=0.5) < 1) return -0.5 * (Math.sqrt(1 - pos*pos) - 1);
    return 0.5 * (Math.sqrt(1 - (pos-=2)*pos) + 1);
  },

  easeOutBounce: function(pos){
    if ((pos) < (1/2.75)) {
      return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
      return (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
      return (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
      return (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  easeInBack: function(pos){
    var s = 1.70158;
    return (pos)*pos*((s+1)*pos - s);
  },

  easeOutBack: function(pos){
    var s = 1.70158;
    return (pos=pos-1)*pos*((s+1)*pos + s) + 1;
  },

  easeInOutBack: function(pos){
    var s = 1.70158;
    if((pos/=0.5) < 1) return 0.5*(pos*pos*(((s*=(1.525))+1)*pos -s));
    return 0.5*((pos-=2)*pos*(((s*=(1.525))+1)*pos +s) +2);
  },

  elastic: function(pos) {
    return -1 * Math.pow(4,-8*pos) * Math.sin((pos*6-1)*(2*Math.PI)/2) + 1;
  },

  swingFromTo: function(pos) {
    var s = 1.70158;
    return ((pos/=0.5) < 1) ? 0.5*(pos*pos*(((s*=(1.525))+1)*pos - s)) :
      0.5*((pos-=2)*pos*(((s*=(1.525))+1)*pos + s) + 2);
  },

  swingFrom: function(pos) {
    var s = 1.70158;
    return pos*pos*((s+1)*pos - s);
  },

  swingTo: function(pos) {
    var s = 1.70158;
    return (pos-=1)*pos*((s+1)*pos + s) + 1;
  },

  bounce: function(pos) {
    if (pos < (1/2.75)) {
        return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
        return (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
        return (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
        return (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  bouncePast: function(pos) {
    if (pos < (1/2.75)) {
        return (7.5625*pos*pos);
    } else if (pos < (2/2.75)) {
        return 2 - (7.5625*(pos-=(1.5/2.75))*pos + .75);
    } else if (pos < (2.5/2.75)) {
        return 2 - (7.5625*(pos-=(2.25/2.75))*pos + .9375);
    } else {
        return 2 - (7.5625*(pos-=(2.625/2.75))*pos + .984375);
    }
  },

  easeFromTo: function(pos) {
    if ((pos/=0.5) < 1) return 0.5*Math.pow(pos,4);
    return -0.5 * ((pos-=2)*Math.pow(pos,3) - 2);
  },

  easeFrom: function(pos) {
    return Math.pow(pos,4);
  },

  easeTo: function(pos) {
    return Math.pow(pos,0.25);
  }
});
(function(FX) {

  Hash.addMethods({
    hasKey: function(key) {
      return (key in this._object);
    }
  });

  var supported = false;
  var hardwareAccelerationSupported = false;
  var transitionEndEventName = null;

  function isHWAcceleratedSafari() {
    var ua = navigator.userAgent, av = navigator.appVersion;
    return (!ua.include('Chrome') && av.include('10_6')) ||
     Prototype.Browser.MobileSafari;
  }

  (function() {
    var eventNames = {
      'WebKitTransitionEvent': 'webkitTransitionEnd',
      'TransitionEvent': 'transitionend',
      'OTransitionEvent': 'oTransitionEnd'
    };
    if (S2.CSS.VENDOR.PREFIX) {
      var p = S2.CSS.VENDOR.PREFIX;
      eventNames[p + 'TransitionEvent'] = p + 'TransitionEnd';
    }

    for (var e in eventNames) {
      try {
        document.createEvent(e);
        transitionEndEventName = eventNames[e];
        supported = true;
        if (e == 'WebKitTransitionEvent') {
          hardwareAccelerationSupported = isHWAcceleratedSafari();
        }
        return;
      } catch (ex) { }
    }
  })();

  if (!supported) return;


  Prototype.BrowserFeatures.CSSTransitions = true;
  S2.Extensions.CSSTransitions = true;
  S2.Extensions.HardwareAcceleratedCSSTransitions = hardwareAccelerationSupported;

  if (hardwareAccelerationSupported) {
    Object.extend(FX.DefaultOptions, { accelerate: true });
  }

  function v(prop) {
    return S2.CSS.vendorizeProperty(prop);
  }

  var CSS_TRANSITIONS_PROPERTIES = $w(
   'border-top-left-radius border-top-right-radius ' +
   'border-bottom-left-radius border-bottom-right-radius ' +
   'background-size transform'
  ).map( function(prop) {
    return v(prop).camelize();
  });

  CSS_TRANSITIONS_PROPERTIES.each(function(property) {
    S2.CSS.PROPERTIES.push(property);
  });

  S2.CSS.NUMERIC_PROPERTIES = S2.CSS.PROPERTIES.findAll(function(property) {
    return !property.endsWith('olor')
  });

  CSS_TRANSITIONS_HARDWARE_ACCELERATED_PROPERTIES =
   S2.Extensions.HardwareAcceleratedCSSTransitions ?
    $w('top left bottom right opacity') : [];

  var TRANSLATE_TEMPLATE = new Template("translate(#{0}px, #{1}px)");


  var Operators = FX.Operators;

  Operators.CssTransition = Class.create(Operators.Base, {
    initialize: function($super, effect, object, options) {
      $super(effect, object, options);
      this.element = $(this.object);

      var style = this.options.style;

      this.style = Object.isString(style) ?
       S2.CSS.normalize(this.element, style) : style;

      this.style = $H(this.style);

      this.targetStyle = S2.CSS.serialize(this.style);
      this.running = false;
    },

    _canHardwareAccelerate: function() {
      if (this.effect.options.accelerate === false) return false;

      var style = this.style.toObject(), keys = this.style.keys();
      var element = this.element;

      if (keys.length === 0) return false;

      var otherPropertyExists = keys.any( function(key) {
        return !CSS_TRANSITIONS_HARDWARE_ACCELERATED_PROPERTIES.include(key);
      });

      if (otherPropertyExists) return false;

      var currentStyles = {
        left:   element.getStyle('left'),
        right:  element.getStyle('right'),
        top:    element.getStyle('top'),
        bottom: element.getStyle('bottom')
      };

      function hasTwoPropertiesOnSameAxis(obj) {
        if (obj.top && obj.bottom) return true;
        if (obj.left && obj.right) return true;
        return false;
      }

      if (hasTwoPropertiesOnSameAxis(currentStyles)) return false;
      if (hasTwoPropertiesOnSameAxis(style))         return false;

      return true;
    },

    _adjustForHardwareAcceleration: function(style) {
      var dx = 0, dy = 0;

      $w('top bottom left right').each( function(prop) {
        if (!style.hasKey(prop)) return;
        var current = window.parseInt(this.element.getStyle(prop), 10);
        var target  = window.parseInt(style.get(prop), 10);

        if (prop === 'top') {
          dy += (target - current);
        } else if (prop === 'bottom') {
          dy += (current - target);
        } else if (prop === 'left') {
          dx += (target - current);
        } else if (prop === 'right') {
          dx += (current - target);
        }

        style.unset(prop);
      }, this);

      var transformProperty = v('transform');

      if (dx !== 0 || dy !== 0) {
        var translation = TRANSLATE_TEMPLATE.evaluate([dx, dy]);
        style.set(transformProperty, translation);
      }

      this.targetStyle += transformProperty + ': translate(0px, 0px);';
      return style;
    },

    render: function() {
      if (this.running === true) return;
      var style = this.style.clone(), effect = this.effect;
      if (this._canHardwareAccelerate()) {
        effect.accelerated = true;
        style = this._adjustForHardwareAcceleration(style);
      } else {
        effect.accelerated = false;
      }

      var s = this.element.style;

      var original = {};
      $w('transition-property transition-duration transition-timing-function').each( function(prop) {
        prop = v(prop).camelize();
        original[prop] = s[prop];
      });

      this.element.store('s2.targetStyle', this.targetStyle);

      this.element.store('s2.originalTransitionStyle', original);

      s[v('transition-property').camelize()] = style.keys().join(',');
      s[v('transition-duration').camelize()] = (effect.duration / 1000).toFixed(3) + 's';
      s[v('transition-timing-function').camelize()] = timingFunctionForTransition(effect.options.transition);

      if (Prototype.Browser.Opera) {
        this._setStyle.bind(this).defer(style.toObject());
      } else this._setStyle(style.toObject());
      this.running = true;

      this.render = Prototype.emptyFunction;
    },

    _setStyle: function(style) {
      this.element.setStyle(style);
    }
  });

  Operators.CssTransition.TIMING_MAP = {
    linear:     'linear',
    sinusoidal: 'ease-in-out'
  };

  function timingFunctionForTransition(transition) {
    var timing = null, MAP = FX.Operators.CssTransition.TIMING_MAP;

    for (var t in MAP) {
      if (FX.Transitions[t] === transition) {
        timing = MAP[t];
        break;
      }
    }
    return timing;
  }

  function isCSSTransitionCompatible(effect) {
    if (!S2.Extensions.CSSTransitions) return false;
    var opt = effect.options;

    if ((opt.engine || '') === 'javascript') return false;

    if (!timingFunctionForTransition(opt.transition)) return false;

    if (opt.propertyTransitions) return false;

    return true;
  };

  FX.Morph = Class.create(FX.Morph, {
    setup: function() {
      if (this.options.change) {
        this.setupWrappers();
      } else if (this.options.style) {
        this.engine  = 'javascript';
        var operator = 'style';

        var style = Object.isString(this.options.style) ?
         S2.CSS.parseStyle(this.options.style) : style;

        var normalizedStyle = S2.CSS.normalize(
         this.destinationElement || this.element, style);

        var changesStyle = Object.keys(normalizedStyle).length > 0;

        if (changesStyle && isCSSTransitionCompatible(this)) {
          this.engine = 'css-transition';
          operator    = 'CssTransition';

          this.update = function(position) {
            this.element.store('s2.effect', this);

            S2.FX.setReady(this.element, false);

            for (var i = 0, operator; operator = this.operators[i]; i++) {
              operator.render(position);
            }

            this.render = Prototype.emptyFunction;
          }
        }

        this.animate(operator, this.destinationElement || this.element, {
          style: this.options.style,
          propertyTransitions: this.options.propertyTransitions || { }
        });
      }
    }
  });

  document.observe(transitionEndEventName, function(event) {
    var element = event.element();
    if (!element) return;

    var effect = element.retrieve('s2.effect');

    if (!effect || effect.state === 'finished') return;

    function adjust(element, effect){
      var targetStyle = element.retrieve('s2.targetStyle');
      if (!targetStyle) return;

      element.setStyle(targetStyle);

      var originalTransitionStyle = element.retrieve('s2.originalTransitionStyle');

      var storage = element.getStorage();
      storage.unset('s2.targetStyle');
      storage.unset('s2.originalTransitionStyle');
      storage.unset('s2.effect');

      if (originalTransitionStyle) {
        element.setStyle(originalTransitionStyle);
      }
      S2.FX.setReady(element);
    }

    var durationProperty = v('transition-duration').camelize();
    element.style[durationProperty] = '';
    adjust(element, effect);

    effect.state = 'finished';

    var after = effect.options.after;
    if (after) after(effect);
  });
})(S2.FX);

Element.__scrollTo = Element.scrollTo;
Element.addMethods({
  scrollTo: function(element, to, options){
    if(arguments.length == 1) return Element.__scrollTo(element);
    new S2.FX.Scroll(element, Object.extend(options || {}, { to: to })).play();
    return element;
  }
});

Element.addMethods({
  effect: function(element, effect, options){
    if (Object.isFunction(effect))
      effect = new effect(element, options);
    else if (Object.isString(effect))
      effect = new S2.FX[effect.charAt(0).toUpperCase() + effect.substring(1)](element, options);
    effect.play(element, options);
    return element;
  },

  morph: function(element, style, options) {
    options = S2.FX.parseOptions(options);
    if (!options.queue) {
      options.queue = element.retrieve('S2.FX.Queue');
      if (!options.queue) {
        element.store('S2.FX.Queue', options.queue = new S2.FX.Queue());
      }
    }
    if (!options.position) options.position = 'end';
    return element.effect('morph', Object.extend(options, { style: style }));
  }.optionize(),

  appear: function(element, options){
    return element.setStyle('opacity: 0;').show().morph('opacity: 1', options);
  },

  fade: function(element, options){
    options = Object.extend({
      after: Element.hide.curry(element)
    }, options || {});
    return element.morph('opacity: 0', options);
  },

  cloneWithoutIDs: function(element) {
    element = $(element);
    var clone = element.cloneNode(true);
    clone.id = '';
    $(clone).select('*[id]').each(function(e) { e.id = ''; });
    return clone;
  }
});

(function(){
  var transform;

  if(window.CSSMatrix) transform = function(element, transform){
    element.style.transform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(window.WebKitCSSMatrix) transform = function(element, transform){
    element.style.webkitTransform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(Prototype.Browser.Gecko) transform = function(element, transform){
    element.style.MozTransform = 'scale('+(transform.scale||1)+') rotate('+(transform.rotation||0)+'rad)';
    return element;
  };
  else if(Prototype.Browser.IE) transform = function(element, transform){
    if(!element._oDims)
      element._oDims = [element.offsetWidth, element.offsetHeight];
    var c = Math.cos(transform.rotation||0) * 1, s = Math.sin(transform.rotation||0) * 1,
        filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand',M11="+c+",M12="+(-s)+",M21="+s+",M22="+c+")";
    element.style.filter = filter;
    element.style.marginLeft = (element._oDims[0]-element.offsetWidth)/2+'px';
    element.style.marginTop = (element._oDims[1]-element.offsetHeight)/2+'px';
    return element;
  };
  else transform = function(element){ return element; }

  Element.addMethods({ transform: transform });
})();

S2.viewportOverlay = function(){
  var viewport = document.viewport.getDimensions(),
    offsets = document.viewport.getScrollOffsets();
  return new Element('div').setStyle({
    position: 'absolute',
    left: offsets.left + 'px', top: offsets.top + 'px',
    width: viewport.width + 'px', height: viewport.height + 'px'
  });
};
S2.FX.Helpers = {
  fitIntoRectangle: function(w, h, rw, rh){
    var f = w/h, rf = rw/rh; return f < rf ?
      [(rw - (w*(rh/h)))/2, 0, w*(rh/h), rh] :
      [0, (rh - (h*(rw/w)))/2, rw, h*(rw/w)];
  }
};

S2.FX.Operators.Zoom = Class.create(S2.FX.Operators.Style, {
  initialize: function($super, effect, object, options) {
    var viewport = document.viewport.getDimensions(),
      offsets = document.viewport.getScrollOffsets(),
      dims = object.getDimensions(),
      f = S2.FX.Helpers.fitIntoRectangle(dims.width, dims.height,
      viewport.width - (options.borderWidth || 0)*2,
      viewport.height - (options.borderWidth || 0)*2);

    Object.extend(options, { style: {
      left: f[0] + (options.borderWidth || 0) + offsets.left + 'px',
      top: f[1] + (options.borderWidth || 0) + offsets.top + 'px',
      width: f[2] + 'px', height: f[3] + 'px'
    }});
    $super(effect, object, options);
  }
});

S2.FX.Zoom = Class.create(S2.FX.Element, {
  setup: function() {
    this.clone = this.element.cloneWithoutIDs();
    this.element.insert({before:this.clone});
    this.clone.absolutize().setStyle({zIndex:9999});

    this.overlay = S2.viewportOverlay();
    if (this.options.overlayClassName)
      this.overlay.addClassName(this.options.overlayClassName)
    else
      this.overlay.setStyle({backgroundColor: '#000', opacity: '0.9'});
    $$('body')[0].insert(this.overlay);

    this.animate('zoom', this.clone, {
      borderWidth: this.options.borderWidth,
      propertyTransitions: this.options.propertyTransitions || { }
    });
  },
  teardown: function() {
    this.clone.observe('click', function() {
      this.overlay.remove();
      this.clone.morph('opacity:0', {
        duration: 0.2,
        after: function() {
          this.clone.remove();
        }.bind(this)
      })
    }.bind(this));
  }
});


S2.UI = {};

S2.UI.Mixin = {};


Object.deepExtend = function(destination, source) {
  for (var property in source) {
    if (source[property] && source[property].constructor &&
     source[property].constructor === Object) {
      destination[property] = destination[property] || {};
      arguments.callee(destination[property], source[property]);
    } else {
      destination[property] = source[property];
    }
  }
  return destination;
};

S2.UI.Mixin.Configurable = {
  setOptions: function(options) {
    if (!this.options) {
      this.options = {};
      var constructor = this.constructor;
      if (constructor.superclass) {
        var chain = [], klass = constructor;
        while (klass = klass.superclass)
          chain.push(klass);
        chain = chain.reverse();

        for (var i = 0, l = chain.length; i < l; i++)
          Object.deepExtend(this.options, chain[i].DEFAULT_OPTIONS || {});
      }

      Object.deepExtend(this.options, constructor.DEFAULT_OPTIONS || {});
    }
    return Object.deepExtend(this.options, options || {});
  }
};

S2.UI.Mixin.Trackable = {
  register: function() {
    var klass = this.constructor;
    if (!klass.instances) {
      klass.instances = [];
    }
    if (!klass.instances.include(this)) {
      klass.instances.push(this);
    }

    if (Object.isFunction(klass.onRegister)) {
      klass.onRegister.call(klass, this);
    }
  },

  unregister: function() {
    var klass = this.constructor;
    klass.instances = klass.instances.without(this);
    if (Object.isFunction(klass.onRegister)) {
      klass.onUnregister.call(klass, this);
    }
  }
};

(function() {
  var METHODS = $w('observe stopObserving show hide ' +
   'addClassName removeClassName hasClassName setStyle getStyle' +
   'writeAttribute readAttribute fire');

  var E = {};

  METHODS.each( function(name) {
    E[name] = function() {
      var element = this.toElement();
      return element[name].apply(element, arguments);
    };
  });

  E.on = function() {
    if (!this.__observers) this.__observers = [];
    var element = this.toElement();
    var result = element.on.apply(element, arguments);
    this.__observers.push(result);
  };

  window.S2.UI.Mixin.Element = E;
})();


S2.UI.Mixin.Shim = {
  __SHIM_TEMPLATE: new Template(
    "<iframe frameborder='0' tabindex='-1' src='javascript:false;' " +
      "style='display:block;position:absolute;z-index:-1;overflow:hidden; " +
      "filter:Alpha(Opacity=\"0\");" +
      "top:expression(((parseInt(this.parentNode.currentStyle.borderTopWidth)||0)*-1)+\'px\');" +
       "left:expression(((parseInt(this.parentNode.currentStyle.borderLeftWidth)||0)*-1)+\'px\');" +
       "width:expression(this.parentNode.offsetWidth+\'px\');" +
       "height:expression(this.parentNode.offsetHeight+\'px\');" +
    "' id='#{0}'></iframe>"
  ),

  createShim: function(element) {
    this.__shim_isie6 = (Prototype.Browser.IE &&
     (/6.0/).test(navigator.userAgent));
    if (!this.__shim_isie6) return;

    element = $(element || this.element);
    if (!element) return;

    this.__shimmed = element;

    var id = element.identify() + '_iframeshim', shim = $(id);

    if (shim) shim.remove();

    element.insert({
      top: this.__SHIM_TEMPLATE.evaluate([id])
    });

    this.__shim_id = id;
  },

  adjustShim: function() {
    if (!this.__shim_isie6) return;
    var shim = this.__shimmed.down('iframe#' + this.__shim_id);
    var element = this.__shimmed;
    if (!shim) return;

    shim.setStyle({
      width:  element.offsetWidth  + 'px',
      height: element.offsetHeight + 'px'
    });
  },

  destroyShim: function() {
    if (!this.__shim_isie6) return;
    var shim = this.__shimmed.down('iframe#' + this.__shim_id);
    if (shim) {
      shim.remove();
    }

    this.__shimmed = null;
  }
};

Object.extend(S2.UI, {
  addClassNames: function(elements, classNames) {
    if (Object.isElement(elements)) {
      elements = [elements];
    }

    if (Object.isString(classNames)) {
      classNames = classNames.split(' ');
    }

    var j, className;
    for (var i = 0, element; element = elements[i]; i++) {
      for (j = 0; className = classNames[j]; j++) {
        Element.addClassName(element, className);
      }
    }

    return elements;
  },

  removeClassNames: function(elements, classNames) {
    if (Object.isElement(elements)) {
      elements = [elements];
    }

    if (Object.isString(classNames)) {
      classNames = classNames.split(' ');
    }

    var j, className;
    for (var i = 0, element; element = elements[i]; i++) {
      for (j = 0; className = classNames[j]; j++) {
        Element.removeClassName(element, className);
      }
    }
  },

  FOCUSABLE_ELEMENTS: $w('input select textarea button object'),

  isFocusable: function(element) {
    var name = element.nodeName.toLowerCase(),
     tabIndex = element.readAttribute('tabIndex'),
     isFocusable = false;

    if (S2.UI.FOCUSABLE_ELEMENTS.include(name)) {
      isFocusable = !element.disabled;
    } else if (name === 'a' || name === 'area') {
      isFocusable = element.href || (tabIndex && !isNaN(tabIndex));
    } else {
      isFocusable = tabIndex && !isNaN(tabIndex);
    }
    return !!isFocusable && S2.UI.isVisible(element);
  },


  makeFocusable: function(elements, shouldBeFocusable) {
    if (Object.isElement(elements)) {
      elements = [elements];
    }

    var value = shouldBeFocusable ? '0' : '-1';
    for (var i = 0, element; element = elements[i]; i++) {
      $(element).writeAttribute('tabIndex', value);
    }
  },

  findFocusables: function(element) {
    return $(element).descendants().select(S2.UI.isFocusable);
  },

  isVisible: function(element) {
    element = $(element);
    var originalElement = element;

    while (element && element.parentNode) {
      var display = element.getStyle('display'),
       visibility = element.getStyle('visibility');

      if (display === 'none' || visibility === 'hidden') {
        return false;
      }
      element = $(element.parentNode);
    }
    return true;
  },

  makeVisible: function(elements, shouldBeVisible) {
    if (Object.isElement(elements)) {
      elements = [elements];
    }

    var newValue = shouldBeVisible ? "visible": "hidden";
    for (var i = 0, element; element = elements[i]; i++) {
      element.setStyle({ 'visibility': newValue });
    }

    return elements;
  },

  modifierUsed: function(event) {
    return event.metaKey || event.ctrlKey || event.altKey;
  },

  getText: function(element) {
    element = $(element);
    return element.innerText && !window.opera ? element.innerText :
     element.innerHTML.stripScripts().unescapeHTML().replace(/[\n\r\s]+/g, ' ');
  }
});

(function() {
  var IGNORED_ELEMENTS = [];
  function _textSelectionHandler(event) {
    var element = Event.element(event);
    if (!element) return;
    for (var i = 0, node; node = IGNORED_ELEMENTS[i]; i++) {
      if (element === node || element.descendantOf(node)) {
        Event.stop(event);
        break;
      }
    }
  }

  if (document.attachEvent) {
    document.onselectstart = _textSelectionHandler.bindAsEventListener(window);
  } else {
  }

  Object.extend(S2.UI, {
    enableTextSelection: function(element) {
      element.setStyle({
        '-moz-user-select': '',
        '-webkit-user-select': '',
        'user-select': ''
      });
      IGNORED_ELEMENTS = IGNORED_ELEMENTS.without(element);
      return element;
    },

    disableTextSelection: function(element) {
      element.setStyle({
        '-moz-user-select': 'none',
        '-webkit-user-select': 'none',
        'user-select': ''
      });
      if (!IGNORED_ELEMENTS.include(element)) {
        IGNORED_ELEMENTS.push(element);
      }
      return element;
    }
  });
})();
S2.UI.Behavior = Class.create(S2.UI.Mixin.Configurable, {
  initialize: function(element, options) {
    this.element = element;
    this.setOptions(options);

    Object.extend(this, options);

    this._observers = {};

    function isEventHandler(eventName) {
      return eventName.startsWith('on') || eventName.include('/on');
    }

    var parts, element, name, handler;
    for (var eventName in this) {
      if (!isEventHandler(eventName)) continue;

      parts = eventName.split('/');
      if (parts.length === 2) {
        element = this[parts.first()] || this.element;
      } else {
        element = this.element;
      }
      name = parts.last();

      handler = this._observers[name] = this[eventName].bind(this);
      element.observe(name.substring(2), handler);
    }
  },

  destroy: function() {
    var element = this.options.proxy || this.element;
    var handler;
    for (var eventName in this._observers) {
      handler = this._observers[eventName];
      element.stopObserving(eventName.substring(2), handler);
    }
  }
});


Object.extend(S2.UI, {
  addBehavior: function(element, behaviorClass, options) {
    var self = arguments.callee;
    if (Object.isArray(element)) {
      element.each( function(el) { self(el, behaviorClass, options); });
      return;
    }

    if (Object.isArray(behaviorClass)) {
      behaviorClass.each( function(klass) { self(element, klass, options ); });
      return;
    }

    var instance = new behaviorClass(element, options || {});
    var behaviors = $(element).retrieve('ui.behaviors', []);
    behaviors.push(instance);
  },

  removeBehavior: function(element, behaviorClass) {
    var self = arguments.callee;
    if (Object.isArray(element)) {
      element.each( function(el) { self(el, behaviorClass); });
      return;
    }

    if (Object.isArray(behaviorClass)) {
      behaviorClass.each( function(klass) { self(element, klass); });
      return;
    }

    var behaviors = $(element).retrieve('ui.behaviors', []);
    var shouldBeRemoved = [];
    for (var i = 0, behavior; behavior = behaviors[i]; i++) {
      if (!behavior instanceof behaviorClass) continue;
      behavior.destroy();
      shouldBeRemoved.push(behavior);
    }
    $(element).store('ui.behaviors', behaviors.without(shouldBeRemoved));
  },


  getBehavior: function(element, behaviorClass) {
    element = $(element);

    var behaviors = element.retrieve('ui.behaviors', []);
    for (var i = 0, l = behaviors.length, b; i < l; i++) {
      b = behaviors[i];
      if (b.constructor === behaviorClass) return b;
    }

    return null;
  }
});

S2.UI.Behavior.Drag = Class.create(S2.UI.Behavior, {
  initialize: function($super, element, options) {
    this.__onmousemove = this._onmousemove.bind(this);
    $super(element, options);
    this.element.addClassName('ui-draggable');
  },

  destroy: function($super) {
    this.element.removeClassName('ui-draggable');
    $super();
  },

  "handle/onmousedown": function(event) {
    var element = this.element;
    this._startPointer  = event.pointer();
    this._startPosition = {
      left: window.parseInt(element.getStyle('left'), 10),
      top:  window.parseInt(element.getStyle('top'),  10)
    };
    document.observe('mousemove', this.__onmousemove);
  },

  "handle/onmouseup": function(event) {
    this._startPointer  = null;
    this._startPosition = null;
    document.stopObserving('mousemove', this.__onmousemove);
  },

  _onmousemove: function(event) {
    var pointer = event.pointer();

    if (!this._startPointer) return;

    var delta = {
      x: pointer.x - this._startPointer.x,
      y: pointer.y - this._startPointer.y
    };

    var newPosition = {
      left: (this._startPosition.left + delta.x) + 'px',
      top:  (this._startPosition.top  + delta.y) + 'px'
    };

    this.element.setStyle(newPosition);
  }
});
S2.UI.Behavior.Focus = Class.create(S2.UI.Behavior, {
  onfocus: function(event) {
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.addClassName('ui-state-focus');
  },

  onblur: function(event) {
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.removeClassName('ui-state-focus');
  }
});
S2.UI.Behavior.Hover = Class.create(S2.UI.Behavior, {
  onmouseenter: function(event) {
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.addClassName('ui-state-hover');
  },

  onmouseleave: function(event) {
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.removeClassName('ui-state-hover');
  }
});
S2.UI.Behavior.Resize = Class.create(S2.UI.Behavior, {
  initialize: function(element, options) {
  }
});
S2.UI.Behavior.Down = Class.create(S2.UI.Behavior, {
  _isRelevantKey: function(event) {
    var code = event.keyCode;
    return (code === Event.KEY_RETURN || code === Event.KEY_SPACE);
  },

  onkeydown: function(event) {
    if (!this._isRelevantKey(event)) return;
    this.onmousedown(event);
  },

  onkeyup: function(event) {
    if (!this._isRelevantKey(event)) return;
    this.onmouseup(event);
  },

  onmousedown: function(event) {
    this._down = true;
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.addClassName('ui-state-down');
  },

  onmouseup: function(event) {
    this._down = false;
    if (this.element.hasClassName('ui-state-disabled')) return;
    this.element.removeClassName('ui-state-down');
  },

  onmouseleave: function(event) {
    return this.onmouseup(event);
  },

  onmouseenter: function(event) {
    if (this._down) {
      return this.onmousedown(event);
    }
  }
});



(function(UI) {

  UI.Base = Class.create(UI.Mixin.Configurable, {
    NAME: "S2.UI.Base",

    initialize:      Function.ABSTRACT,

    addObservers:    Function.ABSTRACT,

    removeObservers: function() {
      if (this.__observers) {
        this.__observers.invoke('stop');
        this.__observers = null;
      }
    },

    destroy: function() {
      this.removeObservers();
    },

    toElement: function() {
      return this.element || null;
    },

    inspect: function() {
      return "#<#{NAME}>".interpolate(this);
    }
  });

})(S2.UI);

Object.extend(Event, {
  KEY_SPACE: 32
});

(function(UI) {

  UI.Accordion = Class.create(UI.Base, {
    NAME: "S2.UI.Accordion",

    initialize: function(element, options) {
      this.element = $(element);
      var opt = this.setOptions(options);

      UI.addClassNames(this.element, 'ui-accordion ui-widget ui-helper-reset');

      if (this.element.nodeName.toUpperCase() === "UL") {
        var lis = this.element.childElements().grep(new Selector('li'));
        UI.addClassNames(lis, 'ui-accordion-li-fix');
      }

      this.headers = this.element.select(opt.headerSelector);
      if (!this.headers || this.headers.length === 0) return;

      UI.addClassNames(this.headers, 'ui-accordion-header ui-helper-reset ' +
       'ui-state-default ui-corner-all');
      UI.addBehavior(this.headers, [UI.Behavior.Hover, UI.Behavior.Focus]);

      this.content = this.headers.map( function(h) { return h.next(); });

      UI.addClassNames(this.content, 'ui-accordion-content ui-helper-reset ' +
       'ui-widget-content ui-corner-bottom');

      this.headers.each( function(header) {
        var icon = new Element('span');
        UI.addClassNames(icon, 'ui-icon ' + opt.icons.header);
        header.insert({ top: icon });
      });

      this._markActive(opt.active || this.headers.first(), false);

      this.element.writeAttribute({
        'role': 'tablist',
        'aria-multiselectable': opt.multiple.toString()
      });
      this.headers.invoke('writeAttribute', 'role', 'tab');
      this.content.invoke('writeAttribute', 'role', 'tabpanel');

      var links = this.headers.map( function(h) { return h.down('a'); });
      links.invoke('observe', 'click', function(event) {
        event.preventDefault();
      });

      this.observers = {
        click: this.click.bind(this),
        keypress: this.keypress.bind(this)
      };

      this.addObservers();
    },

    addObservers: function() {
      this.headers.invoke('observe', 'click', this.observers.click);
      if (Prototype.Browser.WebKit) {
        this.headers.invoke('observe', 'keydown', this.observers.keypress);
      } else {
        this.headers.invoke('observe', 'keypress', this.observers.keypress);
      }
    },

    click: function(event) {
      var header = event.findElement(this.options.headerSelector);
      if (!header || !this.headers.include(header)) return;
      this._toggleActive(header);
    },

    keypress: function(event) {
      if (event.shiftKey || event.metaKey || event.altKey || event.ctrlKey) {
        return;
      }
      var header = event.findElement(this.options.headerSelector);
      var keyCode = (event.keyCode === 0) ? event.charCode : event.keyCode;
      switch (keyCode) {
      case Event.KEY_SPACE:
        this._toggleActive(header);
        event.stop();
        return;
      case Event.KEY_DOWN: // fallthrough
      case Event.KEY_RIGHT:
        this._focusHeader(header, 1);
        event.stop();
        return;
      case Event.KEY_UP: // fallthrough
      case Event.KEY_LEFT:
        this._focusHeader(header, -1);
        event.stop();
        return;
      case Event.KEY_HOME:
        this._focusHeader(this.headers.first());
        event.stop();
        return;
      case Event.KEY_END:
        this._focusHeader(this.headers.last());
        event.stop();
        return;
      }
    },

    _focusHeader: function(header, delta) {
      if (Object.isNumber(delta)) {
        var index = this.headers.indexOf(header);
        index = index + delta;
        if (index > (this.headers.length - 1)) {
          index = this.headers.length - 1;
        } else if (index < 0) {
          index = 0;
        }
        header = this.headers[index];
      }
      (function() { header.down('a').focus(); }).defer();
    },

    _toggleActive: function(header) {
      if (header.hasClassName('ui-state-active')) {
        if (!this.options.multiple) return;
        this._removeActive(header);
        this._activatePanel(null, header.next(), true);
      } else {
        this._markActive(header);
      }
    },

    _removeActive: function(active) {
      var opt = this.options;
      UI.removeClassNames(active, 'ui-state-active ui-corner-top');
      UI.addClassNames(active, 'ui-state-default ui-corner-all');
      active.writeAttribute('aria-expanded', 'false');

      var icon = active.down('.ui-icon');
      icon.removeClassName(opt.icons.headerSelected);
      icon.addClassName(opt.icons.header);
    },

    _markActive: function(active, shouldAnimate) {
      if (Object.isUndefined(shouldAnimate)) {
        shouldAnimate = true;
      }
      var opt = this.options;

      var activePanel = null;
      if (!opt.multiple) {
        activePanel = this.element.down('.ui-accordion-content-active');
        this.headers.each(this._removeActive.bind(this));
      }

      if (!active) return;

      UI.removeClassNames(active, 'ui-state-default ui-corner-all');
      UI.addClassNames(active, 'ui-state-active ui-corner-top');

      active.writeAttribute('aria-expanded', 'true');

      this._activatePanel(active.next(), activePanel, shouldAnimate);

      var icon = active.down('.ui-icon');
      icon.removeClassName(opt.icons.header);
      icon.addClassName(opt.icons.headerSelected);

      return active;
    },

    _activatePanel: function(panel, previousPanel, shouldAnimate) {
      if (shouldAnimate) {
        this.options.transition(panel, previousPanel);
      } else {
        if (previousPanel) {
          previousPanel.removeClassName('ui-accordion-content-active');
        }
        if (panel) {
          panel.addClassName('ui-accordion-content-active');
        }
      }
    }
  });

  Object.extend(UI.Accordion, {
    DEFAULT_OPTIONS: {
      multiple: false,  /* whether more than one pane can be open at once */
      headerSelector: 'h3',

      icons: {
        header:         'ui-icon-triangle-1-e',
        headerSelected: 'ui-icon-triangle-1-s'
      },

      transition: function(panel, previousPanel) {
        var effects = [], effect;

        if (previousPanel) {
          effect = new S2.FX.SlideUp(previousPanel, {
            duration: 0.2,
            after: function() {
              previousPanel.removeClassName('ui-accordion-content-active');
            }
          });
          effects.push(effect);
        }

        if (panel) {
          effect = new S2.FX.SlideDown(panel, {
            duration: 0.2,
            before: function() {
              panel.addClassName('ui-accordion-content-active');
            }
          });
          effects.push(effect);
        }

        effects.invoke('play');
      }
    }
  });

})(S2.UI);



(function(UI) {

  UI.Button = Class.create(UI.Base, UI.Mixin.Element, {
    NAME: "S2.UI.Button",


    initialize: function(element, options) {
      this.element = $(element);
      this.type = this._getType();
      this.setOptions(options);

      this.element.store('ui.button', this);
      this.toElement().store('ui.button', this);

      this.observers = {
        click:   this._click.bind(this),
        keyup:   this._keyup.bind(this)
      };

      this.addObservers();

      this._initialized = true;
    },

    setOptions: function($super, options) {
      var opt = $super(options);
      this._applyOptions();
      return opt;
    },

    addObservers: function() {
      for (var eventName in this.observers)
        this.observe(eventName, this.observers[eventName]);
    },

    removeObservers: function() {
      for (var eventName in this.observers)
        this.stopObserving(eventName, this.observers[eventName]);
    },

    isActive: function() {
      if (!this._isToggleButton()) return false;
      return this.hasClassName('ui-state-active');
    },

    toggle: function(bool) {
      if (!this._isToggleButton()) return this;

      var isActive = this.isActive();
      if (Object.isUndefined(bool)) bool = !isActive;

      if (bool !== isActive) {
        var result = this.fire('ui:button:toggle', { button: this });
        if (result.stopped) return this;
      }

      this[bool ? 'addClassName' : 'removeClassName']('ui-state-active');
      return this;
    },

    setEnabled: function(shouldBeEnabled) {
      var element = this.toElement();

      if (this.enabled === shouldBeEnabled) return;
      this.enabled = shouldBeEnabled;

      if (shouldBeEnabled) element.removeClassName('ui-state-disabled');
      else element.addClassName('ui-state-disabled');

      this.element.disabled = !shouldBeEnabled;
    },

    toElement: function() {
      return this._buttonElement || this.element;
    },



    _getType: function() {
      var name = this.element.nodeName.toUpperCase(), type;

      if (name === 'INPUT') {
        type = this.element.type;
        if (type === 'checkbox' || type === 'radio') {
          return type;
        } else if (type === 'button') {
          return 'input';
        }
      } else {
        return 'button';
      }
    },


    _hasText: function() {
      var text = this.options.text;
      return !!text && text !== '&nbsp;' && (/\S/).test(text);
    },

    _isContainer: function() {
      return this.type !== 'input';
    },

    _isToggleButton: function() {
      return this.type === 'checkbox' || this.type === 'radio' ||
       this.options.toggle;
    },

    _applyOptions: function() {
      if (this.type === 'checkbox' || this.type === 'radio') {
        this._adaptFormWidget();
      } else {
        this._makeButtonElement(this.element);
      }

      this.enabled = true;
      var disabled = (this.element.disabled === true) ||
       this.element.hasClassName('ui-state-disabled');

      this.setEnabled(!disabled);
    },

    _makeButtonElement: function(element) {
      var opt = this.options, B = UI.Behavior;
      this._buttonElement = element;

      UI.addClassNames(element, 'ui-button ui-widget ui-state-default ' +
       'ui-corner-all');
      UI.addBehavior(element, [B.Hover, B.Focus, B.Down]);

      if (opt.primary)
        element.addClassName('ui-priority-primary');

      if (opt.text === null || opt.text === true)
        opt.text = element.value || UI.getText(element);

      element.writeAttribute('role', 'button');

      if (this._isContainer()) {
        var buttonClassName, primaryIcon, secondaryIcon;
        var icons = opt.icons;

        var hasIcon     = !!icons.primary || !!icons.secondary;
        var hasTwoIcons = !!icons.primary && !!icons.secondary;
        var hasText     = this._hasText();

        if (icons.primary)
          primaryIcon = this._makeIconElement(icons.primary, 'primary');

        if (icons.secondary)
          secondaryIcon = this._makeIconElement(icons.secondary, 'secondary');

        if (hasIcon) {
          if (hasText) {
            buttonClassName = hasTwoIcons ? 'ui-button-text-icons' :
             'ui-button-text-icon';
          } else {
            buttonClassName = hasTwoIcons ? 'ui-button-icons-only' :
             'ui-button-icon-only';
          }
        } else {
          buttonClassName = 'ui-button-text-only';
        }

        element.update('');
        element.addClassName(buttonClassName);

        if (primaryIcon)   element.insert(primaryIcon);
        element.insert(this._makeTextElement(opt.text));
        if (secondaryIcon) element.insert(secondaryIcon);
      }

    },

    _makeIconElement: function(name, type) {
      var classNames = 'ui-button-icon-' + type + ' ui-icon ' + name;
      return new Element('span', { 'class': classNames });
    },

    _makeTextElement: function(text) {
      var span = new Element('span', { 'class': 'ui-button-text' });
      span.update(text || "&nbsp;");
      return span;
    },

    _adaptFormWidget: function() {
      var opt = this.options;
      var id = this.element.id, label;

      if (id) {
        label = $$('label[for="' + id + '"]')[0];
      }

      if (!label) label = this.element.up('label');

      if (!label) {
        throw "Can't find a LABEL element for this button.";
      }

      this.label = label;

      this.element.addClassName('ui-helper-hidden-accessible');
      this._makeButtonElement(label);
      UI.makeFocusable(label, true);
    },


    _click: function(event) {
      this.toggle(!this.isActive());
      this.element.checked = this.isActive();
    },

    _keyup: function(event) {
      var code = event.keyCode;
      if (code !== Event.KEY_SPACE && code !== Event.KEY_RETURN)
        return;

      var isActive = this.isActive();
      (function() {
        if (isActive !== this.isActive()) return;
        this.toggle(isActive);
        this.element.checked = this.isActive();
      }).bind(this).defer();
    }
  });


  Object.extend(UI.Button, {
    DEFAULT_OPTIONS: {
      primary: false,
      text:    true,
      toggle:  false,
      icons: {
        primary:   null,
        secondary: null
      }
    }
  });

})(S2.UI);
(function(UI) {

  UI.ButtonSet = Class.create(UI.Base, {
    NAME: "S2.UI.ButtonSet",

    initialize: function(element, options) {
      this.element = $(element);
      this.element.store('ui.buttonset', this);
      var opt = this.setOptions(options);

      this.element.addClassName('ui-buttonset');
      this.refresh();

      this.observers = {
        toggle: this._toggle.bind(this)
      };
      this.addObservers();
    },

    addObservers: function() {
      this.element.observe('ui:button:toggle', this.observers.toggle);
    },

    removeObservers: function() {
      this.element.stopObserving('ui:button:toggle', this.observers.toggle);
    },

    destroy: function() {
      this.removeObservers();
      this.element.removeClassName('ui-buttonset');
      UI.removeClassNames(this.buttons, 'ui-corner-left ui-corner-right');

      this.element.getStorage().unset('ui.buttonset');
    },

    _destroyButton: function(el) {
      var instance = el.retrieve('ui.button');
      if (instance) instance.destroy();
    },

    _toggle: function(event) {
      var element = event.element(), instance = element.retrieve('ui.button');
      if (!instance || instance.type !== 'radio') return;

      if (instance.isActive()) {
        event.stop();
        return;
      }

      this.instances.each( function(b) {
        b._setActive(false);
      });
    },

    refresh: function() {
      this.element.cleanWhitespace();

      this.buttons = [];

      this.element.descendants().each( function(el) {
        var isButton = false;
        if ($w('SELECT BUTTON A').include(el.nodeName.toUpperCase()))
          isButton = true;
        if (el.match('input') && el.type !== 'hidden') isButton = true;
        if (el.hasClassName('ui-button')) isButton = true;

        if (isButton) this.buttons.push(el);
      }, this);

      this.instances = this.buttons.map( function(el) {
        var instance = el.retrieve('ui.button');
        if (!instance) instance = new UI.Button(el);
        return instance;
      });

      this.instances.each( function(b) {
        UI.removeClassNames(b.toElement(),
         'ui-corner-all ui-corner-left ui-corner-right');
      });

      if (this.instances.length > 0) {
        this.instances.first().toElement().addClassName('ui-corner-left');
        this.instances.last().toElement().addClassName('ui-corner-right');
      }
    }
  });

})(S2.UI);
(function(UI) {

  UI.ButtonWithMenu = Class.create(UI.Button, {
    initialize: function($super, element, options) {
      $super(element, options);
      var opt = this.setOptions(options);

      this.menu = new UI.Menu();
      this.element.insert({ after: this.menu });

      if (opt.choices) {
        opt.choices.each(this.menu.addChoice, this.menu);
      }

      (function() {
        var iLayout = this.element.getLayout();

        this.menu.setStyle({
          left: iLayout.get('left') + 'px',
          top:  (iLayout.get('top') + iLayout.get('margin-box-height')) + 'px'
        });
      }).bind(this).defer();

      Object.extend(this.observers, {
        clickForMenu:  this._clickForMenu.bind(this),
        onMenuOpen:    this._onMenuOpen.bind(this),
        onMenuClose:   this._onMenuClose.bind(this),
        onMenuSelect:  this._onMenuSelect.bind(this)
      });

      this.observe('mousedown', this.observers.clickForMenu);

      this.menu.observe('ui:menu:after:open',  this.observers.onMenuOpen );
      this.menu.observe('ui:menu:after:close', this.observers.onMenuClose);
      this.menu.observe('ui:menu:selected',    this.observers.onMenuSelect);
    },

    _clickForMenu: function(event) {
      if (this.menu.isOpen()) {
        this.menu.close();
      } else {
        this.menu.open();
      }
    },

    _onMenuOpen: function() {
      var menuElement = this.menu.element, buttonElement = this.toElement();
      if (!this._clickOutsideMenuObserver) {
        this._clickOutsideMenuObserver = $(document.body).on('click', function(event) {
          var el = event.element();
          if (el !== menuElement && !el.descendantOf(menuElement) &&
           el !== buttonElement && !el.descendantOf(buttonElement)) {
            this.menu.close();
          }
        }.bind(this));
      } else {
        this._clickOutsideMenuObserver.start();
      }
    },

    _onMenuClose: function() {
      if (this._clickOutsideMenuObserver)
        this._clickOutsideMenuObserver.stop();
    },

    _onMenuSelect: function(event) {
      var element = event.element();
      this.fire('ui:button:menu:selected', {
        instance: this,
        element:  event.memo.element
      });
    }
  });


  Object.extend(UI.ButtonWithMenu, {
    DEFAULT_OPTIONS: {
      icons: { primary: 'ui-icon-bullet', secondary: 'ui-icon-triangle-1-s' }
    }
  });

})(S2.UI);

(function(UI) {
  UI.Tabs = Class.create(UI.Base, {
    NAME: "S2.UI.Tabs",

    initialize: function(element, options) {
      this.element = $(element);
      UI.addClassNames(this.element,
       'ui-widget ui-widget-content ui-tabs ui-corner-all');

      this.setOptions(options);
      var opt = this.options;

      this.anchors = [];
      this.panels  = [];

      this.list = this.element.down('ul');
      this.list.cleanWhitespace();
      this.nav  = this.list;
      UI.addClassNames(this.nav,
       'ui-tabs-nav ui-helper-reset ui-helper-clearfix ' +
       'ui-widget-header ui-corner-all');

      this.tabs = this.list.select('li');
      UI.addClassNames(this.tabs, 'ui-state-default ui-corner-top');
      UI.addBehavior(this.tabs, [UI.Behavior.Hover, UI.Behavior.Down]);

      this.element.writeAttribute({
        'role': 'tablist',
        'aria-multiselectable': 'false'
      });

      this.tabs.each( function(li) {
        var anchor = li.down('a');
        if (!anchor) return;

        var href = anchor.readAttribute('href');
        if (!href.include('#')) return;

        var hash = href.split('#').last(), panel = $(hash);

        li.writeAttribute('tabIndex', '0');

        if (panel) {
          panel.store('ui.tab', li);
          li.store('ui.panel', panel);

          this.anchors.push(anchor);
          this.panels.push(panel);
        }
      }, this);

      this.anchors.invoke('writeAttribute', 'role', 'tab');
      this.panels.invoke('writeAttribute', 'role', 'tabpanel');

      this.tabs.first().addClassName('ui-position-first');
      this.tabs.last().addClassName('ui-position-last');

      UI.addClassNames(this.panels,
       'ui-tabs-panel ui-tabs-hide ui-widget-content ui-corner-bottom');

      this.observers = {
        onKeyPress: this.onKeyPress.bind(this),
        onTabClick: this.onTabClick.bind(this)
      };
      this.addObservers();

      var selectedTab;

      var urlHash = window.location.hash.substring(1), activePanel;
      if (!urlHash.empty()) {
        activePanel = $(urlHash);
      }
      if (activePanel && this.panels.include(activePanel)) {
        selectedTab = activePanel.retrieve('ui.tab');
      } else {
        selectedTab = opt.selectedTab ? $(opt.selectedTab) :
         this.tabs.first();
      }

      this.setSelectedTab(selectedTab);
    },

    addObservers: function() {
      this.anchors.invoke('observe', 'click', this.observers.onTabClick);
      this.tabs.invoke('observe', 'keypress', this.observers.onKeyPress);
    },

    _setSelected: function(id) {
      var panel = $(id), tab = panel.retrieve('ui.tab');

      var oldTab = this.list.down('.ui-tabs-selected');
      var oldPanel = oldTab ? oldTab.retrieve('ui.panel') : null;

      UI.removeClassNames(this.tabs, 'ui-tabs-selected ui-state-active');
      UI.addClassNames(this.tabs, 'ui-state-default');

      tab.removeClassName('ui-state-default').addClassName(
       'ui-tabs-selected ui-state-active');

      this.element.fire('ui:tabs:change', {
        from: { tab: oldTab, panel: oldPanel },
        to:   { tab: tab,    panel: panel    }
      });
      this.options.panel.transition(oldPanel, panel);
    },

    setSelectedTab: function(tab) {
      this.setSelectedPanel(tab.retrieve('ui.panel'));
    },

    setSelectedPanel: function(panel) {
      this._setSelected(panel.readAttribute('id'));
    },

    onTabClick: function(event) {
      event.stop();
      var anchor = event.findElement('a'), tab = event.findElement('li');

      this.setSelectedTab(tab);
    },

    onKeyPress: function(event) {
      if (UI.modifierUsed(event)) return;
      var tab = event.findElement('li');
      var keyCode = event.keyCode || event.charCode;

      switch (keyCode) {
      case Event.KEY_SPACE:  // fallthrough
      case Event.KEY_RETURN:
        this.setSelectedTab(tab);
        event.stop();
        return;
      case Event.KEY_UP: // fallthrough
      case Event.KEY_LEFT:
        this._focusTab(tab, -1);
        return;
      case Event.KEY_DOWN: // fallthrough
      case Event.KEY_RIGHT:
        this._focusTab(tab, 1);
        return;
      }
    },

    _focusTab: function(tab, delta) {
      if (Object.isNumber(delta)) {
        var index = this.tabs.indexOf(tab);
        index = index + delta;
        if (index > (this.tabs.length - 1)) {
          index = this.tabs.length - 1;
        } else if (index < 0) {
          index = 0;
        }
        tab = this.tabs[index];
      }
      (function() { tab.focus(); }).defer();
    }
  });

  Object.extend(UI.Tabs, {
    DEFAULT_OPTIONS: {
      panel: {
        transition: function(from, to) {
          if (from) {
            from.addClassName('ui-tabs-hide');
          }
          to.removeClassName('ui-tabs-hide');
        }
      }
    }
  });
})(S2.UI);
(function(UI) {


  UI.Overlay = Class.create(
   UI.Base,
   UI.Mixin.Trackable,
   UI.Mixin.Shim, {
    NAME: "S2.UI.Overlay",

    initialize: function(options) {
      this.setOptions(options);
      this.element = new Element('div', {
        'class': 'ui-widget-overlay'
      });

      this.register();
      this.createShim();
      this.adjustShim();
      this.constructor.onResize();
    },

    destroy: function() {
      this.element.remove();
      this.unregister();
    },

    toElement: function() {
      return this.element;
    }
  });

  Object.extend(UI.Overlay, {
    onRegister: function() {
      if (this.instances.length !== 1) return;

      this._resizeObserver = this._resizeObserver || this.onResize.bind(this);
      Event.observe(window, 'resize', this._resizeObserver);
      Event.observe(window, 'scroll', this._resizeObserver);
    },

    onUnregister: function() {
      if (this.instances.length !== 0) return;
      Event.stopObserving(window, 'resize', this._resizeObserver);
      Event.stopObserving(window, 'scroll', this._resizeObserver);
    },

    onResize: function() {
      var vSize   = document.viewport.getDimensions();
      var offsets = document.viewport.getScrollOffsets();
      this.instances.each( function(instance) {
        var element = instance.element;
        element.setStyle({
          width:  vSize.width  + 'px',
          height: vSize.height + 'px',
          left:   offsets.left + 'px',
          top:    offsets.top  + 'px'
        });
      });
      (function() {
        this.instances.invoke('adjustShim');
      }).bind(this).defer();
    }
  });

})(S2.UI);
(function(UI) {

  UI.Dialog = Class.create(UI.Base, UI.Mixin.Element, {
    NAME: "S2.UI.Dialog",

    initialize: function(element, options) {
      if (!Object.isElement(element)) {
        options = element;
        element = null;
      } else {
        element = $(element);
      }

      var opt = this.setOptions(options);

      this.element = element || new Element('div');
      UI.addClassNames(this.element, 'ui-dialog ui-widget ' +
       'ui-widget-content ui-corner-all');

      this.element.hide().setStyle({
        position: 'absolute',
        overflow: 'hidden',
        zIndex:   opt.zIndex,
        outline:  '0'
      });

      this.element.writeAttribute({
        tabIndex: '-1',
        role: 'dialog'
      });

      if (opt.content) {
        this.content = Object.isElement(opt.content) ? opt.content :
         new Element('div').update(opt.content);
      } else {
        this.content = new Element('div');
      }

      UI.addClassNames(this.content, 'ui-dialog-content ui-widget-content');
      this.element.insert(this.content);

      this.titleBar = this.options.titleBar || new Element('div');
      UI.addClassNames(this.titleBar, 'ui-dialog-titlebar ui-widget-header ' +
  		 'ui-corner-all ui-helper-clearfix');
  		this.element.insert({ top: this.titleBar });

  	  this.closeButton = new Element('a', { 'href': '#' });
  	  UI.addClassNames(this.closeButton, 'ui-dialog-titlebar-close ' +
  	   'ui-corner-all');
  	  new UI.Button(this.closeButton, {
  	    text: false,
  	    icons: { primary: 'ui-icon-closethick' }
  	  });
  	  this.closeButton.observe('mousedown', Event.stop);
  	  this.closeButton.observe('click', function(event) {
  	    event.stop();
  	    this.close(false);
  	  }.bind(this));

  	  this.titleBar.insert(this.closeButton);


  	  this.titleText = new Element('span', { 'class': 'ui-dialog-title' });
  	  this.titleText.update(this.options.title).identify();
  	  this.element.writeAttribute('aria-labelledby',
  	   this.titleText.readAttribute('id'));
  	  this.titleBar.insert({ top: this.titleText }) ;

      UI.disableTextSelection(this.element);

      if (this.options.draggable) {
        UI.addBehavior(this.element, UI.Behavior.Drag,
         { handle: this.titleBar });
      }


      var buttons = this.options.buttons;

      if (buttons && buttons.length) {
        this._createButtons();
      }

      this.observers = {
        keypress: this.keypress.bind(this)
      };

    },

    toElement: function() {
      return this.element;
    },

    _createButtons: function() {
      var buttons = this.options.buttons;
      if (this.buttonPane) {
        this.buttonPane.remove();
      }

      this.buttonPane = new Element('div');
      UI.addClassNames(this.buttonPane,
       'ui-dialog-buttonpane ui-widget-content ui-helper-clearfix');

      buttons.each( function(button) {
        var element = new Element('button', { type: 'button' });
        UI.addClassNames(element, 'ui-state-default ui-corner-all');

        if (button.primary) {
          element.addClassName('ui-priority-primary');
        }

        if (button.secondary) {
          element.addClassName('ui-priority-secondary');
        }

        element.update(button.label);
        new UI.Button(element);
        element.observe('click', button.action.bind(this, this));

        this.buttonPane.insert(element);
      }, this);

      this.element.insert(this.buttonPane);
    },

    _position: function() {
      var vSize = document.viewport.getDimensions();
      var dialog = this.element, layout = dialog.getLayout();

      var position = {
        left: ((vSize.width  / 2) - (layout.get('width')  / 2)).round(),
        top:  ((vSize.height / 2) - (layout.get('height') / 2)).round()
      };

      var offsets = document.viewport.getScrollOffsets();

      position.left += offsets.left;
      position.top  += offsets.top;

      this.element.setStyle({
        left: position.left + 'px',
        top:  position.top  + 'px'
      });
    },

    open: function() {
      if (this._isOpen) return;

      var result = this.element.fire("ui:dialog:before:open",
       { dialog: this });
      if (result.stopped) return;

      var opt = this.options;

      this.overlay = opt.modal ? new UI.Overlay() : null;
      $(document.body).insert(this.overlay);
      $(document.body).insert(this);

      this.element.show();
      this._position();

      this.focusables = UI.findFocusables(this.element);

      var forms = this.content.select('form');
      if (this.content.match('form')) {
        forms.push(this.content);
      }

      if (!opt.submitForms) {
        forms.invoke('observe', 'submit', Event.stop);
      }

      var f = this.focusables.without(this.closeButton);
      var focus = opt.focus, foundFocusable = false;
      if (focus === 'auto' && forms.length > 0) {
        Form.focusFirstElement(forms.first());
        foundFocusable = true;
      } else if (focus === 'first') {
        f.first().focus();
        foundFocusable = true;
      } else if (focus === 'last') {
        f.last().focus();
        foundFocusable = true;
      } else if (Object.isElement(focus)) {
        focus.focus();
        foundFocusable = true;
      } else {
        if (this.buttonPane) {
          var primary = this.buttonPane.down('.ui-priority-primary');
          if (primary) {
            primary.focus();
            foundFocusable = true;
          }
        }
      }

      if (!foundFocusable && f.length > 0) {
        f.first().focus();
      }

      Event.observe(window, 'keydown', this.observers.keypress);
      this._isOpen = true;

      this.element.fire("ui:dialog:after:open", { dialog: this });
      return this;
    },

    close: function(success) {
      success = !!success; // Coerce to a boolean.
      var result = this.element.fire("ui:dialog:before:close",
       { dialog: this });
      if (result.stopped) return;

      if (this.overlay) {
        this.overlay.destroy();
      }

      this.element.hide();
      this._isOpen = false;
      Event.stopObserving(window, 'keydown', this.observers.keypress);

      var memo = { dialog: this, success: success };

      var form = this.content.match('form') ? this.content : this.content.down('form');
      if (form) {
        Object.extend(memo, { form: form.serialize({ hash: true }) });
      }

      this.element.fire("ui:dialog:after:close", memo);
      UI.enableTextSelection(this.element, true);
      return this;
    },

    keypress: function(event) {
      if (UI.modifierUsed(event)) return;

      var f = this.focusables, opt = this.options;
      if (event.keyCode === Event.KEY_ESC) {
        if (opt.closeOnEscape) this.close(false);
        return;
      }

      if (event.keyCode === Event.KEY_RETURN) {
        this.close(true);
        return;
      }

      if (event.keyCode === Event.KEY_TAB) {
        if (!this.options.modal) return;
        if (!f) return;
        var next, current = event.findElement();
        var index = f.indexOf(current);
        if (index === -1) {
          next = f.first();
        } else {
          if (event.shiftKey) {
            next = index === 0 ? f.last() : f[index - 1];
          } else {
            next = (index === (f.length - 1)) ? f.first() : f[index + 1];
          }
        }

        if (next) {
          event.stop();
          (function() { next.focus(); }).defer();
        }
      }
    }
  });

  Object.extend(UI.Dialog, {
    DEFAULT_OPTIONS: {
      zIndex: 1000,

      title: "Dialog",

      content: null,
      modal:   true,
      focus:   'auto',

      submitForms: false,

      closeOnEscape: true,

      draggable: true,
      resizable: false,

      buttons: [
        {
          label: "OK",
          primary: true,
          action: function(instance) {
            instance.close(true);
          }
        }
      ]
    }
  });

})(S2.UI);
(function(UI) {
  UI.Slider = Class.create(UI.Base, UI.Mixin.Element, {
    NAME: "S2.UI.Slider",

    initialize: function(element, options) {
      this.element = $(element);
      var opt = this.setOptions(options);

      UI.addClassNames(this.element, 'ui-slider ui-widget ' +
       'ui-widget-content ui-corner-all');

      this.orientation = opt.orientation;

      this.element.addClassName('ui-slider-' + this.orientation);

      this._computeTrackLength();

      var initialValues = opt.value.initial;
      if (!Object.isArray(initialValues)) {
        initialValues = [initialValues];
      }

      this.values  = initialValues;
      this.handles = [];
      this.values.each( function(value, index) {
        var handle = new Element('a', { href: '#' });
        handle.store('ui.slider.handle', index);
        this.handles.push(handle);
        this.element.insert(handle);
      }, this);

      UI.addClassNames(this.handles, 'ui-slider-handle ui-state-default ' +
       'ui-corner-all');
      this.handles.invoke('writeAttribute', 'tabIndex', '0');

      this.activeHandles = [this.handles.first()];

      this.handles.invoke('observe', 'click', Event.stop);
      UI.addBehavior(this.handles, [UI.Behavior.Hover, UI.Behavior.Focus]);

      this.observers = {
        focus:          this.focus.bind(this),
        blur:           this.blur.bind(this),
        keydown:        this.keydown.bind(this),
        keyup:          this.keyup.bind(this),
        mousedown:      this.mousedown.bind(this),
        mouseup:        this.mouseup.bind(this),
        mousemove:      this.mousemove.bind(this),
        rangeMouseDown: this.rangeMouseDown.bind(this),
        rangeMouseMove: this.rangeMouseMove.bind(this),
        rangeMouseUp:   this.rangeMouseUp.bind(this)
      };

      var v = opt.value;
      if (v.step !== null) {
        this._possibleValues = [];
        for (var val = v.min; val < v.max; val += v.step) {
          this._possibleValues.push(val);
        }
        this._possibleValues.push(v.max);

        this.keyboardStep = v.step;
      } else if (opt.possibleValues) {
        this._possibleValues = opt.possibleValues.clone();
        this.keyboardStep = null;
      } else {
        this.keyboardStep = (v.max - v.min) / 100;
      }

      this.range = null;
      if (opt.range && this.values.length === 2) {
        this.restricted = true;
        this.range = new Element('div', { 'class': 'ui-slider-range' });
        this.element.insert(this.range);

        this.range.addClassName('ui-widget-header');
      } else if (opt.range) {
        throw "Must have exactly 2 handles to depict a slider range.";
      }

      this._computeTrackLength();
      this._computeHandleLength();

      this.active   = false;
      this.dragging = false;
      this.disabled = false;

      this.addObservers();

      this.values.each(this._setValue, this);

      this.initialized = true;
    },

    addObservers: function() {
      var o = this.observers;
      this.on('mousedown', o.mousedown);
      if (this.range) {
        this.on('mousedown', '.ui-slider-range', o.rangeMouseDown);
      }

      this.on('keydown', '.ui-slider-handle', o.keydown);
      this.on('keyup',   '.ui-slider-handle', o.keyup);

      this.handles.invoke('observe', 'focus', o.focus);
      this.handles.invoke('observe', 'blur',  o.blur);
    },

    removeObservers: function($super) {
      $super();
      var o = this.observers;
      this.handles.invoke('stopObserving', 'focus', o.focus);
      this.handles.invoke('stopObserving', 'blur',  o.blur);
    },

    _computeTrackLength: function() {
      var length, dim;
      if (this.orientation === 'vertical') {
        dim = this.element.offsetHeight;
        length = (dim !== 0) ? dim :
         window.parseInt(this.element.getStyle('height'), 10);
      } else {
        dim = this.element.offsetWidth;
        length = (dim !== 0) ? dim :
         window.parseInt(this.element.getStyle('width'), 10);
      }

      this._trackLength = length;
      return length;
    },

    _computeHandleLength: function() {
      var handle = this.handles.first(), length, dim;

      if (!handle) return null;

      if (this.orientation === 'vertical') {
        dim = handle.offsetHeight;
        length = (dim !== 0) ? dim :
         window.parseInt(handle.getStyle('height'), 10);
        this._trackMargin = handle.getLayout().get('margin-top');
        this._trackLength -= 2 * this._trackMargin;
      } else {
        dim = handle.offsetWidth;
        length = (dim !== 0) ? dim :
         window.parseInt(handle.getStyle('width'), 10);
         this._trackMargin = handle.getLayout().get('margin-left');
         this._trackLength -= 2 * this._trackMargin;
      }

      this._handleLength = length;
      return length;
    },

    _nextValue: function(currentValue, direction) {
      if (this.options.possibleValues) {
        var index = this._possibleValues.indexOf(currentValue);
        return this._possibleValues[index + direction];
      } else {
        return currentValue + (this.keyboardStep * direction);
      }
    },

    keydown: function(event) {
      if (this.options.disabled) return;

      var handle = event.findElement();
      var index  = handle.retrieve('ui.slider.handle');
      var opt = this.options;

      if (!Object.isNumber(index)) return;

      var interceptKeys = [Event.KEY_HOME, Event.KEY_END, Event.KEY_UP,
       Event.KEY_DOWN, Event.KEY_LEFT, Event.KEY_RIGHT];

      if (!interceptKeys.include(event.keyCode)) return;
      event.preventDefault();

      handle.addClassName('ui-state-active');

      var currentValue, newValue, step = this.keyboardStep;
      currentValue = newValue = this.values[index];

      switch (event.keyCode) {
      case Event.KEY_HOME:
        newValue = opt.value.min; break;
      case Event.KEY_END:
        newValue = opt.value.max; break;
      case Event.KEY_UP: // fallthrough
      case Event.KEY_RIGHT:
        if (currentValue === opt.value.max) return;
        newValue = this._nextValue(currentValue, 1);
        break;
      case Event.KEY_DOWN: // fallthrough
      case Event.KEY_LEFT:
        if (currentValue === opt.value.min) return;
        newValue = this._nextValue(currentValue, -1);
        break;
      }

      this.dragging = true;
      this._setValue(newValue, index);

      if (!Prototype.Browser.WebKit) {
        var interval = this._timer ? 0.1 : 1;
        this._timer = arguments.callee.bind(this).delay(interval, event);
      }
    },

    keyup: function(event) {
      this.dragging = false;
      if (this._timer) {
        window.clearTimeout(this._timer);
        this._timer = null;
      }
      this._updateFinished();

      var handle = event.findElement();
      handle.removeClassName('ui-state-active');
    },

    setValue: function(sliderValue, handleIndex) {
      this._saveValues();
      this._setValue(sliderValue, handleIndex);
      this._fireChangedEvent();
    },

    _setValue: function(sliderValue, handleIndex) {
      if (!this.activeHandles) {
        this.activeHandles = [this.handles[handleIndex || 0]];
        this._updateStyles();
      }

      var activeHandle = this.activeHandles.first();

      handleIndex = handleIndex || activeHandle.retrieve('ui.slider.handle') || 0;

      if (this.initialized && this.restricted) {
        if (handleIndex > 0 && sliderValue < this.values[handleIndex - 1]) {
          sliderValue = this.values[handleIndex - 1];
        }
        if (handleIndex < (this.handles.length - 1) &&
         (sliderValue > this.values[handleIndex + 1])) {
          sliderValue = this.values[handleIndex + 1];
        }
      }

      sliderValue = this._getNearestValue(sliderValue);
      this.values[handleIndex] = sliderValue;

      var prop = (this.orientation === 'vertical') ? 'top' : 'left';
      var css = {};
      css[prop] = this._valueToPx(sliderValue) + 'px';
      this.handles[handleIndex].setStyle(css);

      this._drawRange();

      if (!this.dragging && !this.undoing && !this.initialized)  {
        this._updateFinished();
      }

      if (this.initialized) {
        this.element.fire("ui:slider:value:changing", {
          slider: this,
          values: this.values
        });
        this.options.onSlide(this.values, this);
      }

      return this;
    },

    _getNearestValue: function(value) {
      var range = this.options.value;

      if (value < range.min) value = range.min;
      if (value > range.max) value = range.max;

      if (this._possibleValues) {
        var left, right;
        for (var i = 0; i < this._possibleValues.length; i++) {
          right = this._possibleValues[i];
          if (right === value)  return value;
          if (right > value)    break;
        }
        left = this._possibleValues[i - 1];
        value = value.nearer(left, right);
      }

      return value;
    },

    _valueToPx: function(value) {
      var range = this.options.value;
      var pixels = (this._trackLength - this._handleLength ) /
       (range.max - range.min);
      pixels *= (value - range.min);

      if (this.orientation === 'vertical') {
        pixels = (this._trackLength - pixels) - this._handleLength;
      } else {
      }

      return Math.round(pixels);
    },

    mousedown: function(event) {
      var opt = this.options;
      if (!event.isLeftClick() || opt.disabled) return;
      event.stop();

      if (event.findElement('.ui-slider-range')) return;

      this._saveValues();

      this.active = true;
      var target  = event.findElement();
      var pointer = event.pointer();

      if (target === this.element) {
        var trackOffset = this.element.cumulativeOffset();

        var newPosition = {
          x: Math.round(pointer.x - trackOffset.left - this._handleLength / 2 - this._trackMargin),
          y: Math.round(pointer.y - trackOffset.top - this._handleLength / 2 - this._trackMargin)
        };

        this._setValue(this._pxToValue(newPosition));

        this.activeHandles = this.activeHandles || [this.handles.first()];
        handle = this.activeHandles.first();
        this._updateStyles();
        this._offsets = {x: 0, y: 0};
      } else {
        handle = event.findElement('.ui-slider-handle');
        if (!handle) return;

        this.activeHandles = [handle];
        this._updateStyles();
        var handleOffset = handle.cumulativeOffset();
        this._offsets = {
          x: pointer.x - (handleOffset.left + this._handleLength / 2),
          y: pointer.y - (handleOffset.top + this._handleLength / 2)
        };
      }

      document.observe('mousemove', this.observers.mousemove);
      document.observe('mouseup',   this.observers.mouseup);
    },

    mouseup: function(event) {
      if (this.active && this.dragging) {
        this._updateFinished();
        event.stop();
      }

      this.active = this.dragging = false;

      this.activeHandles = null;
      this._updateStyles();

      document.stopObserving('mousemove', this.observers.mousemove);
      document.stopObserving('mouseup',   this.observers.mouseup);
    },


    mousemove: function(event) {
      if (!this.active) return;
      event.stop();

      this.dragging = true;
      this._draw(event);

      if (Prototype.Browser.WebKit) window.scrollBy(0, 0);
    },

    rangeMouseDown: function(event) {
      var pointer = event.pointer();

      var trackOffset = this.element.cumulativeOffset();

      var newPosition = {
        x: Math.round(pointer.x - trackOffset.left),
        y: Math.round(pointer.y - trackOffset.top)
      };

      this._saveValues();
      this._rangeInitialValues = this.values.clone();
      this._rangePseudoValue = this._pxToValue(newPosition);

      document.observe('mousemove', this.observers.rangeMouseMove);
      document.observe('mouseup',   this.observers.rangeMouseUp);


      this.activeHandles = this.handles.clone();
      this._updateStyles();
    },

    rangeMouseMove: function(event) {
      this.dragging = true;
      event.stop();

      var opt = this.options;

      var pointer = event.pointer();
      var trackOffset = this.element.cumulativeOffset();

      var newPosition = {
        x: Math.round(pointer.x - trackOffset.left),
        y: Math.round(pointer.y - trackOffset.top)
      };

      var value = this._pxToValue(newPosition);
      var valueDelta = value - this._rangePseudoValue;
      var newValues = this._rangeInitialValues.map(
       function(v) { return v + valueDelta; });

      if (newValues[0] < opt.value.min) {
        valueDelta = opt.value.min - this._rangeInitialValues[0];
        newValues = this._rangeInitialValues.map(
         function(v) { return v + valueDelta; });
      } else if (newValues[1] > opt.value.max) {
        valueDelta = opt.value.max - this._rangeInitialValues[1];
        newValues = this._rangeInitialValues.map(
         function(v) { return v + valueDelta; });
      }

      newValues.each(this._setValue, this);
    },

    rangeMouseUp: function(event) {
      this.dragging = false;

      document.stopObserving('mousemove', this.observers.rangeMouseMove);
      document.stopObserving('mouseup',   this.observers.rangeMouseUp);

      this._updateFinished();
    },


    _draw: function(event) {
      var pointer = event.pointer();
      var trackOffset = this.element.cumulativeOffset();

      pointer.x -= (this._offsets.x + trackOffset.left + this._handleLength / 2 + this._trackMargin);
      pointer.y -= (this._offsets.y + trackOffset.top + this._handleLength / 2 + this._trackMargin);

      this._setValue(this._pxToValue(pointer));
    },

    _pxToValue: function(offsets) {
      var opt = this.options;
      var offset = (this.orientation === 'horizontal') ?
       offsets.x : offsets.y;

      var value = ((offset / (this._trackLength - this._handleLength) *
       (opt.value.max - opt.value.min)) + opt.value.min);

      if (this.orientation === 'vertical') {
        value = opt.value.max - (value - opt.value.min);
      }

      return value;
    },

    undo: function() {
      if (!this._oldValues) return;
      this._restoreValues();

      this.undoing = true;
      this.values.each(this._setValue, this);
      this.undoing = false;

      this._fireChangedEvent();
    },

    _saveValues: function() {
      this._oldValues = this.values.clone();
    },

    _restoreValues: function() {
      if (!this._oldValues) return;
      this.values = this._oldValues.clone();
      this._oldValues = null;
    },

    _fireChangedEvent: function() {
      var result = this.element.fire("ui:slider:value:changed", {
        slider: this,
        values: this.values
      });

      return result;
    },

    _updateFinished: function() {
      var result = this._fireChangedEvent();
      if (result.stopped) {
        this.undo();
        return;
      }

      this.activeHandles = null;
      this._updateStyles();

      this.options.onChange(this.values, this);
    },

    _updateStyles: function() {
      UI.removeClassNames(this.handles, 'ui-state-active');
      if (this.activeHandles) {
        UI.addClassNames(this.activeHandles, 'ui-state-active');
      }
    },

    _drawRange: function() {
      if (!this.range) return;
      var values = this.values, pixels = values.map(this._valueToPx, this);

      if (this.orientation === 'vertical') {
        this.range.setStyle({
          top: pixels[1] + 'px',
          height: (pixels[0] - pixels[1]) + 'px'
        });
      } else {
        this.range.setStyle({
          left:   pixels[0] + 'px',
          width:  (pixels[1] - pixels[0]) + 'px'
        });
      }
    },

    focus: function(event) {
      if (this.options.disabled) return;
      var handle = event.findElement();
      UI.removeClassNames(this.element.select('.ui-state-focus'),
       'ui-state-focus');
      handle.addClassName('ui-state-focus');
    },

    blur: function(event) {
      event.findElement().removeClassName('ui-state-focus');
    }
  });

  Object.extend(UI.Slider, {
    DEFAULT_OPTIONS: {
      range: false,
      disabled: false,
      value: { min: 0, max: 100, initial: 0, step: null },
      possibleValues: null,
      orientation: 'horizontal',

      onSlide:  Prototype.emptyFunction,
      onChange: Prototype.emptyFunction
    }
  });
})(S2.UI);

(function(UI) {
  UI.ProgressBar = Class.create(UI.Base, {
    NAME: "S2.UI.ProgressBar",

    initialize: function(element, options) {
      this.element = $(element);
      var opt = this.setOptions(options);

      UI.addClassNames(this.element, 'ui-progressbar ui-widget ' +
       'ui-widget-content ui-corner-all');

      this.element.writeAttribute({
        'role': 'progressbar',
        'aria-valuemin': 0,
        'aria-valuemax': 100,
        'aria-valuenow': 0
      });

      this.valueElement = new Element('div', {
        'class': 'ui-progressbar-value ui-widget-header ui-corner-left'
      });

      this.value = opt.value.initial;
      this._refreshValue();

      this.element.insert(this.valueElement);
      this.element.store(this.NAME, this);
    },

    destroy: function() {
      UI.removeClassNames(this.element, 'ui-progressbar ui-widget ' +
       'ui-widget-content ui-corner-all');
      UI.removeAttributes(this.element, 'role aria-valuemin aria-valuemax ' +
       'aria-valuenow');
      this.element.getData().unset(this.NAME);
    },

    getValue: function() {
      var value = this.value, v = this.options.value;
      if (value < v.min) value = v.min;
      if (value > v.max) value = v.max;
      return value;
    },

    setValue: function(value) {
      this._oldValue = this.getValue();
      this.value = value;
      this._refreshValue();
      return this;
    },

    undo: function() {
      this.undoing = true;
      this.setValue(this._oldValue);
      this.undoing = false;
      return this;
    },

    _refreshValue: function() {
      var value = this.getValue();
      if (!this.undoing) {
        var result = this.element.fire('ui:progressbar:value:changed', {
          progressBar: this,
          value: value
        });
        if (result.stopped) {
          this.undo();
          return;
        }
      }
      if (value === this.options.value.max) {
        this.valueElement.addClassName('ui-corner-right');
      } else {
        this.valueElement.removeClassName('ui-corner-right');
      }


      var width    = window.parseInt(this.element.getStyle('width'), 10);
      var newWidth = (width * value) / 100;
      var css      = "width: #{0}px".interpolate([newWidth]);

      UI.makeVisible(this.valueElement, (value > this.options.value.min));
      this.valueElement.morph(css, { duration: 0.7, transition: 'linear' });
      this.element.writeAttribute('aria-valuenow', value);
    }
  });

  Object.extend(UI.ProgressBar, {
    DEFAULT_OPTIONS: {
      value: { min: 0, max: 100, initial: 0 }
    }
  });

})(S2.UI);

(function(UI) {
  UI.Menu = Class.create(UI.Base, UI.Mixin.Shim, UI.Mixin.Element, {
    NAME: "S2.UI.Menu",

    initialize: function(element, options) {
      this.element = $(element);
      if (!this.element) {
        var options = element;
        this.element = new Element('ul');
      }

      this.activeId = this.element.identify() + '_active';
      var opt = this.setOptions();

      UI.addClassNames(this.element, 'ui-helper-hidden ui-widget ' +
       'ui-widget-content ui-menu ui-corner-' + opt.corner);

      this.choices = this.element.select('li');

      this._highlightedIndex = -1;

      this.element.writeAttribute({
        'role': 'menu',
        'aria-activedescendant': this.activeId
      });

      this.choices.invoke('writeAttribute', 'role', 'menuitem');

      this.observers = {
        mouseover: this._mouseover.bind(this),
        click: this._click.bind(this)
      };
      this.addObservers();

      this._shown = false;

      this.createShim();

    },

    addObservers: function() {
      this.observe('mouseover', this.observers.mouseover);
      this.observe('mousedown', this.observers.click);
    },

    removeObservers: function() {
      this.stopObserving('mouseover', this.observers.mouseover);
      this.stopObserving('mousedown', this.observers.click);
    },

    clear: function() {
      this.element.select('li').invoke('remove');
      this.choices = [];
      return this;
    },

    addChoice: function(choice) {
      var li;
      if (Object.isElement(choice)) {
        if (choice.tagName.toUpperCase() === 'LI') {
          li = choice;
        } else {
          li = new Element('li');
          li.insert(choice);
        }
      } else {
        li = new Element('li');
        li.update(choice);
      }

      li.addClassName('ui-menu-item');
      li.writeAttribute('role', 'menuitem');

      this.element.insert(li);
      this.choices = this.element.select('li');

      return li;
    },

    _mouseover: function(event) {
      var li = event.findElement('li');
      if (!li) return;

      this.highlightChoice(li);
    },

    _click: function(event) {
      var li = event.findElement('li');
      if (!li) return;

      this.selectChoice(li);
    },

    moveHighlight: function(delta) {
      this._highlightedIndex = (this._highlightedIndex + delta).constrain(
       -1, this.choices.length - 1);

      this.highlightChoice();
      return this;
    },

    highlightChoice: function(element) {
      var choices = this.choices, index;

      if (Object.isElement(element)) {
        index = choices.indexOf(element);
      } else if (Object.isNumber(element)) {
        index = element;
      } else {
        index = this._highlightedIndex;
      }

      UI.removeClassNames(this.choices, 'ui-state-active');
      if (index === -1) return;
      this.choices[index].addClassName('ui-state-active');
      this._highlightedIndex = index;

      var active = this.element.down('#' + this.activeId);
      if (active) active.writeAttribute('id', '');
      this.choices[index].writeAttribute('id', this.activeId);
    },

    selectChoice: function(element) {
      var element;
      if (Object.isNumber(element)) {
        element = this.choices[element];
      } else if (!element) {
        element = this.choices[this._highlightedIndex];
      }

      var result = this.element.fire('ui:menu:selected', {
        instance: this,
        element: element
      });

      if (!result.stopped) {
        this.close();
      }

      return this;
    },

    open: function() {

      var result = this.element.fire('ui:menu:before:open', { instance: this });
        this.element.removeClassName('ui-helper-hidden');
        this._highlightedIndex = -1;

      if (Prototype.Browser.IE) {
        this.adjustShim();
      }

      if (this.options.closeOnOutsideClick) {
        this._outsideClickObserver = $(this.element.ownerDocument).on('click', function(event) {
          var element = event.element();
          if (element !== this.element && !element.descendantOf(this.element)) {
            this.close();
          }
        }.bind(this));
      }

      this.element.fire('ui:menu:after:open', { instance: this });
    },

    close: function() {
      var result = this.element.fire('ui:menu:before:close', { instance: this });
        this.element.addClassName('ui-helper-hidden');

      this.element.fire('ui:menu:after:close', { instance: this });

      if (this._outsideClickObserver) {
        this._outsideClickObserver.stop();
        this._outsideClickObserver = null;
      }

      return this;
    },

    isOpen: function() {
      return !this.element.hasClassName('ui-helper-hidden');
    }
  });

  Object.extend(UI.Menu, {
    DEFAULT_OPTIONS: {
      corner: 'all',
      closeOnOutsideClick: true
    }
  });

})(S2.UI);


(function(UI) {

  UI.Autocompleter = Class.create(UI.Base, {
    NAME: "S2.UI.Autocompleter",

    initialize: function(element, options) {
      this.element = $(element);
      var opt = this.setOptions(options);

      UI.addClassNames(this.element, 'ui-widget ui-autocompleter');

      this.input = this.element.down('input[type="text"]');

      if (!this.input) {
        this.input = new Element('input', { type: 'text' });
        this.element.insert(this.input);
      }

      this.input.insert({ before: this.button });
      this.input.setAttribute('autocomplete', 'off');

      this.name = opt.parameterName || this.input.readAttribute('name');

      if (opt.choices) {
        this.choices = opt.choices.clone();
      }

      this.menu = new UI.Menu();
      this.element.insert(this.menu.element);

      (function() {
        var iLayout = this.input.getLayout();
        this.menu.element.setStyle({
          left: iLayout.get('left') + 'px',
          top:  (iLayout.get('top') + iLayout.get('margin-box-height')) + 'px'
        });
      }).bind(this).defer();

      this.observers = {
        blur: this._blur.bind(this),
        keyup: this._keyup.bind(this),
        keydown: this._keydown.bind(this),
        selected: this._selected.bind(this)
      };

      this.addObservers();
    },

    addObservers: function() {
      this.input.observe('blur',    this.observers.blur);
      this.input.observe('keyup',   this.observers.keyup);
      this.input.observe('keydown', this.observers.keydown);

      this.menu.observe('ui:menu:selected',
       this.observers.selected);
    },

    _schedule: function() {
      this._unschedule();
      this._timeout = this._change.bind(this).delay(this.options.frequency);
    },

    _unschedule: function() {
      if (this._timeout) window.clearTimeout(this._timeout);
    },

    _keyup: function(event) {
      var value = this.input.getValue();

      if (value) {
        if (value.blank() || value.length < this.options.minCharacters) {
          this.menu.close();
          this._unschedule();
          return;
        }
        if (value !== this._value) {
          this._schedule();
        }
      } else {
        this.menu.close();
        this._unschedule();
      }

      this._value = value;
    },

    _keydown: function(event) {
      if (UI.modifierUsed(event)) return;
      if (!this.menu.isOpen()) return;

      var keyCode = event.keyCode || event.charCode;

      switch (event.keyCode) {
        case Event.KEY_UP:
          this.menu.moveHighlight(-1);
          event.stop();
          break;
        case Event.KEY_DOWN:
          this.menu.moveHighlight(1);
          event.stop();
          break;
        case Event.KEY_TAB:
          this.menu.selectChoice();
          break;
        case Event.KEY_RETURN:
          this.menu.selectChoice();
          this.input.blur();
          event.stop();
          break;
        case Event.KEY_ESC:
          this.input.setValue('');
          this.input.blur();
          break;
      }
    },

    _getInput: function() {
      return this.input.getValue();
    },

    _setInput: function(value) {
      this.input.setValue(value);
    },

    _change: function() {
      this.findChoices();
    },

    findChoices: function() {
      var value = this._getInput(),
          choices = this.choices || [],
          opt = this.options;

      var results = choices.inject([], function(memo, choice) {
        if (opt.choiceValue(choice).toLowerCase().include(
         value.toLowerCase())) {
          memo.push(choice);
        }
        return memo;
      });

      this.setChoices(results);
    },

    setChoices: function(results) {
      this.results = results;
      this._updateMenu(results);
    },

    _updateMenu: function(results) {
      var opt = this.options;

      this.menu.clear();

      var needle = new RegExp(RegExp.escape(this._value), 'i');
      for (var i = 0, result, li, text; result = results[i]; i++) {
        text = opt.highlightSubstring ?
         opt.choiceValue(result).replace(needle, "<b>$&</b>") :
         opt.choiceValue(result);

        li = new Element('li').update((opt.choiceTemplate instanceof Template) ?
         opt.choiceTemplate.evaluate({text: text, object: result}) : text);
        li.store('ui.autocompleter.value', result);
        this.menu.addChoice(li);
      }

      if (results.length === 0) {
        this.menu.close();
      } else {
        this.menu.open();
      }
    },

    _moveMenuChoice: function(delta) {
      var choices = this.list.down('li');
      this._selectedIndex = (this._selectedIndex + delta).constrain(
       -1, this.results.length - 1);

      this._highlightMenuChoice();
    },

    _highlightMenuChoice: function(element) {
      var choices = this.list.select('li'), index;

      if (Object.isElement(element)) {
        index = choices.indexOf(element);
      } else if (Object.isNumber(element)) {
        index = element;
      } else {
        index = this._selectedIndex;
      }

      UI.removeClassNames(choices, 'ui-state-active');
      if (index === -1 || index === null) return;
      choices[index].addClassName('ui-state-active');

      this._selectedIndex = index;
    },

    _selected: function(event) {
      var memo = event.memo, li = memo.element;

      if (li) {
        var value = li.retrieve('ui.autocompleter.value');
        var result = this.element.fire('ui:autocompleter:selected', {
          instance: this,
          value:    value
        });
        if (result.stopped) return;
        this._setInput(this.options.choiceValue(value));
      }
      this.menu.close();
    },

    _blur: function(event) {
      this._unschedule();
      this.menu.close();
    }
  });

  Object.extend(UI.Autocompleter, {
    DEFAULT_OPTIONS: {
      tokens: [],
      frequency: 0.4,
      minCharacters: 1,

      highlightSubstring: true,

      onShow: Prototype.K,
      onHide: Prototype.K,

      choiceValue: function(choice) { return choice.toString(); }
    }
  });

})(S2.UI);


document.observe('dom:loaded',function(){
  if(!S2.enableMultitouchSupport) return;

  var b = $(document.body), sequenceId = 0;

  function initElementData(element){
    element._rotation = element._rotation || 0;
    element._scale = element._scale || 1;
    element._panX = element._panX || 0;
    element._panY = element._panY || 0;
    element._pans = [[0,0],[0,0],[0,0]];
    element._panidx = 1;
  }

  function setElementData(element, rotation, scale, panX, panY){
    element._rotation = rotation;
    element._scale = scale;
    element._panX = panX;
    element._panY = panY;
  }

  function fireEvent(element, data){
    element.fire('manipulate:update',
      Object.extend(data, { id: sequenceId }));
  }

  function setupIPhoneEvent(){
    var element, rotation, scale,
      touches = {}, t1 = null, t2 = null, state = 0, oX, oY,
      offsetX, offsetY, initialDistance, initialRotation;
    function updateTouches(touchlist){
      var i = touchlist.length;
      while(i--) touches[touchlist[i].identifier] = touchlist[i];
      var l = []; for(k in touches) l.push(k); l = l.sort();
      t1 = l.length > 0 ? l[0] : null;
      element = t1 ? touches[t1].target : null;
      if(element && element.nodeType==3) element = element.parentNode;
      t2 = l.length > 1 ? l[1] : null;
      if(state==0 && (t1&&t2)) {
        offsetX = (touches[t1].pageX-touches[t2].pageX).abs();
        offsetY = (touches[t1].pageY-touches[t2].pageY).abs();
        if(element) initElementData(element);
        initialDistance = Math.sqrt(offsetX*offsetX + offsetY*offsetY);
        initialRotation = Math.atan2(touches[t2].pageY-touches[t1].pageY,touches[t2].pageX-touches[t1].pageX);
        state = 1;
        return;
      }
      if(state==1 && !(t1&&t2)) {
        if(element) setElementData(element, rotation, scale);
        state = 0;
      }
    }
    function touchCount(){
      var c=0; for(k in touches) c++; return c;
    }

    b.observe('touchstart', function(event){
      var t = t1, o;
      updateTouches(event.changedTouches);
      if(t==null && t1) {
        o = element.viewportOffset();
        oX = o.left+(touches[t1].pageX-o.left), oY = o.top+(touches[t1].pageY-o.top);
      }
      event.stop();
    });
    b.observe('touchmove', function(event){
      updateTouches(event.changedTouches);
      if(t1&&!t2) {
        fireEvent(element, {
          panX: (element._panX||0)+touches[t1].pageX-oX,
          panY: (element._panY||0)+touches[t1].pageY-oY,
          scale: element._scale,
          rotation: element._rotation
        });
        event.stop();
        return;
      }
      if(!(t1&&t2)) return;
      var a = touches[t2].pageX-touches[t1].pageX,
        b = touches[t2].pageY-touches[t1].pageY,
        cX = (element._panX||0) + touches[t2].pageX - a/2 - oX,
        cY = (element._panY||0) + touches[t2].pageY - b/2 - oY,
        distance = Math.sqrt(a*a + b*b);

      scale = element._scale * distance/initialDistance;
      rotation = element._rotation + Math.atan2(b,a) - initialRotation;

      fireEvent(element, { rotation: rotation, scale: scale, panX: cX, panY: cY });
      event.stop();
    });
    ['touchcancel','touchend'].each(function(eventName){
      b.observe(eventName, function(event){
        var i = event.changedTouches.length;
        while(i--) delete(touches[event.changedTouches[i].identifier]);
        updateTouches([]);
        if(element) setElementData(element, rotation, scale);
      });
    });
  }

  function setupBridgedEvent(){
    var element, rotation, scale, panX, panY, active = false;

    b.observe('touchstart', function(event){
      event.preventDefault();
    });

    b.observe('transformactionstart', function(event){
      event.stop();

      element = event.element();
      initElementData(element);
      active = true;
    });
    b.observe('transformactionupdate', function(event){
      element = event.element();
      rotation = element._rotation + event.rotate;
      scale = element._scale * event.scale;
      panX = element._panX + event.translateX;
      panY = element._panY + event.translateY;

      fireEvent(element, {
        rotation: rotation, scale: scale,
        panX: panX, panY: panY,
        clientX: event.clientX, clientY: event.clientY
      });
      event.stop();
    }, false);
    b.observe('transformactionend', function(event){
      element = event.element();
      if(element) setElementData(element, rotation, scale, panX, panY);
      active = false;

      var speed = Math.sqrt(event.translateSpeedX*event.translateSpeedX +
          event.translateSpeedY*event.translateSpeedY);

      if(speed>25){
        element.fire('manipulate:flick', {
          speed: speed,
          direction: Math.atan2(event.translateSpeedY,event.translateSpeedX)
        });
      }
    });
    b.on('mousemove', function(event){
      event.stop();
    });
    b.on('mousedown', function(event){
      event.stop();
    });
    b.on('mouseup', function(event){
      event.stop();
    });
  }

  function setupGenericEvent(){
    var mX, mY, active = false, listen = true, element, mode,
      initialDistance, initialRotation, oX, oY, rotation, scale, distance;
    function objectForScaleEvent(event){
      var o = element.viewportOffset(),
        a = (event.pageX-o.left)-mX,
        b = (event.pageY-o.top)-mY;
      distance = Math.sqrt(a*a + b*b);
      scale = element._scale * distance/initialDistance;
      rotation = element._rotation + Math.atan2(b,a) - initialRotation;
      return {
        rotation: rotation, scale: scale,
        panX: element._panX, panY: element._panY };
    }
    function objectForPanEvent(event){
      return {
        rotation: element._rotation, scale: element._scale,
        panX: element._panX+event.pageX-oX, panY: element._panY+event.pageY-oY };
    }
    b.observe('mousedown', function(event){
      mode = event.shiftKey ? 'scale' : 'pan';
      element = event.element();
      if(!(element && element.fire)) return;
      sequenceId++;
      active = true;
      initElementData(element);
      var o =  element.viewportOffset();
      mX = element.offsetWidth/2;
      mY = element.offsetHeight/2;
      var a = event.pageX-o.left-mX, b = event.pageY-o.top-mY;
      initialDistance = Math.sqrt(a*a+b*b);
      initialRotation = Math.atan2(b,a);
      oX = o.left+(event.pageX-o.left), oY = o.top+(event.pageY-o.top);
      event.stop();
    });
    b.observe('mousemove', function(event){
      if(!active) return;
      fireEvent(element, mode == 'scale' ? objectForScaleEvent(event) : objectForPanEvent(event));
    });
    b.observe('mouseup', function(event){
      if(!active) return;
      active = false;
      if(mode=='scale'){
        var o = objectForScaleEvent(event);
        fireEvent(element, o);
        element._rotation = o.rotation;
        element._scale = o.scale;
      } else {
        fireEvent(element, objectForPanEvent(event));
        element._panX = element._panX+event.pageX-oX;
        element._panY = element._panY+event.pageY-oY;
      }
    });
    b.observe('dragstart', function(event){ event.stop(); });
  }

  try {
    document.createEvent("TransformActionEvent");
    return setupBridgedEvent();
  } catch(e) {}

  try {
    document.createEvent("TouchEvent");
    return setupIPhoneEvent();
  } catch(e) {}

  return setupGenericEvent();
});
