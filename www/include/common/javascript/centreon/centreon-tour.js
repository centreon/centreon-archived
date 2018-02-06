/* global jQuery, navigator */
(function ($) {
  function CentreonTour(settings, $elem) {
    this.internal = new CentreonTourInternal(settings, $elem);
    if (settings.autostart) {
      this.start();
    }
  }
  
  CentreonTour.prototype.start = function (force) {
    force = force === undefined ? false : force;
    if (false === this.internal.alreadyRun() || force === true) {
      this.internal.$elem.data('tourbus').depart();
    }
  };
  
  function CentreonTourInternal(settings, $elem) {
    var self = this;
    
    this.settings = settings;
    this.$elem = $elem;
    this.localStorage = null;
    if (window.localStorage) {
      this.localStorage = window.localStorage;
    }
    
    /* Initialize jQuery.tourbus */
    $(this.$elem).tourbus({
      onStop: function () {
        self.endTour();
      },
      onLegStart: function (leg, bus) {
        var $elem = leg.$target;
        if (leg.rawData.highlight) {
          $(self.settings.overlay).show();
          if (leg.rawData.focus) {
            $elem = $(leg.rawData.focus);
          }
          $elem.addClass(self.settings.highlightCls); 
        }
      },
      onLegEnd: function (leg, bus) {
        var $elem = leg.$target;
        if (leg.rawData.highlight) {
          $(self.settings.overlay).hide();
          if (leg.rawData.focus) {
            $elem = $(leg.rawData.focus);
          }
          $elem.removeClass(self.settings.highlightCls); 
        }
      }
    });
  }
  
  CentreonTourInternal.prototype.endTour = function () {
    var tours = this.getTourInfo();
    tours[this.settings.name] = {
      done: true,
      version: this.settings.version
    };
    if (this.localStorage) {
      this.localStorage.setItem('tours', JSON.stringify(tours));
    } else {
      $.cookie(
        JSON.stringify(tours),
        {
          expires: 1460,
          path: '/'
        }
      );
    }
  };
  
  CentreonTourInternal.prototype.getTourInfo = function () {
    var tours = {};
    var cookies;
    if (this.localStorage) {
      if (this.localStorage.getItem('tours')) {
        tours = JSON.parse(this.localStorage.getItem('tours'));
      }
    } else {
      if ($.cookie('tours')) {
        tours = JSON.parse($.cookie('tours'));
      }
    }
    return tours;
  };
  
  CentreonTourInternal.prototype.alreadyRun = function () {
    var tours = this.getTourInfo();
    if (tours.hasOwnProperty(this.settings.name)
      && tours[this.settings.name].done
      && tours[this.settings.name].version === this.settings.version) {
      return true;
    }
    return false;
  };
  
  $.fn.centreonTour = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonTour.defaults, options);
    var methodReturn;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonTour");

      if (!data) {
        $this.data("centreonTour", ( data = new CentreonTour(settings, $this)));
      }

      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }
    });
    return (methodReturn === undefined) ? $set : methodReturn;
  };
  
  $.fn.centreonTour.defaults = {
    name: null,
    version: null,
    autostart: false,
    overlay: '.tourbus-overlay',
    highlightCls: 'tourbus-highlight'
  };
  
})(jQuery);