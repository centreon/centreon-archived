/*global jQuery:false */
/**
 * Infinite scroll into a div
 */
(function($) {
  function CentreonInfiniteScroll(settings, $elem) {
    var self = this;
    this.settings = settings;
    this.$elem = $elem;
    this.lastTime = null;
    this.recentTime = null;
    this.loading = true;
    this.hasEvent = true;
    this.lastScroll = 0;
    this.newNotSee = 0;

    /* Prepare templates */
    this.template = null;
    if (this.settings.template !== "" && this.settings.template !== undefined) {
      this.template = Hogan.compile(this.settings.template);
    }

    /* Prepare badge for new items */
    self.$badge = $("<div></div>");

    /* Add event to scroll */
    this.$elem.on("scroll", function (e) {
      if (self.hasEvent) {
        if ($(this).scrollTop() === 0) {
          self.newNotSee = 0;
          self.$badge.hide();
        }
        if (self.lastScroll < $(this).scrollTop()) {
          var childrens = self.$elem.children();
          var height = 0
          for (i = 0; i < childrens.length - 1; i++) {
            height += $(childrens[i]).height() + self.settings.padding;
          }
          if ($(this).scrollTop() + self.$elem.height() > height) {
            self.loadData();
          }
        }
      }
      self.lastScroll = $(this).scrollTop();
    });

    self.loadData();
    setTimeout( function () { self.loadNewData(); }, this.settings.refresh );
  }

  CentreonInfiniteScroll.prototype = {
    loadData: function () {
      var self = this;
      if ( this.settings.ajaxUrlGetScroll === "" ) {
        return;
      }

      data = this.prepareData();
      data.startTime = this.lastTime;

      $.ajax({
        url: this.settings.ajaxUrlGetScroll,
        type: "post",
        data: data,
        dataType: "json",
        success: function (data, statusText, jqXHR) {
          if (data.data.length === 0) {
            self.hasEvent = false;
            self.loading = false;
            self.recentTime = new Date().getTime() / 1000;
            return;
          }
          $.each(data.data, function (idx, values) {
            var line;
            /* Insert with template */
            if (self.template !== null) {
              line = self.template.render(values);
              self.$elem.append($(line));
            }
          });

          self.lastTime = data.lastTimeEntry;
          if (self.recentTime === null) {
            self.recentTime = data.recentTime;
          }

          /* Fix for time is 0 */
          if (data.data.length < self.settings.limit) {
            self.loading = false;
          }

          /* Continu to load in first call */
          if (self.loading) {
            var childrens = self.$elem.children();
            var height = 0
            for (i = 0; i < childrens.length; i++) {
              height += $(childrens[i]).height();
            }
            if (self.$elem.scrollTop() + self.$elem.height() > height) {
              self.loadData();
            } else {
              self.loading = false;
            }
          }

          /* Send trigger for loaded data */
          if (!self.loading) {
            self.$elem.trigger("loaded");
          }
        }
      });
    },
    loadNewData: function () {
      var self = this;
      if (this.settings.ajaxUrlGetNew === "" || this.recentTime === null) {
        setTimeout(function () { self.loadNewData(); }, self.settings.refresh);
        return;
      }
      
      data = this.prepareData();
      data.startTime = this.recentTime;

      $.ajax({
        url: this.settings.ajaxUrlGetNew,
        type: "post",
        data: data,
        dataType: "json",
        success: function (data, statusText, jqXHR) {
          var nbEl = data.data.length - 1;
          for ( ; nbEl >= 0; nbEl--) {
            values = data.data[nbEl];
            /* Insert with template */
            if (self.template !== null) {
              line = self.template.render(values);
              self.$elem.prepend($(line));
            }
          }

          if (data.data.length > 0) {
            if (self.$elem.scrollTop() !== 0) {
              self.newNotSee += data.data.length;
              self.$badge.find('span').text(self.newNotSee + " events");
              self.$badge.show();
            }

            setTimeout(function () { self.loadNewData(); }, self.settings.refresh );

            /* Send trigger for loaded data */
            if (!self.loading) {
              self.$elem.trigger("loaded");
            }
          }
        }
      });
    },
    prepareData: function () {
      var data = {};
      /* Get filter from form */
      if (this.settings.formFilter !== "") {
        $.each($(this.settings.formFilter).serializeArray(), function (idx, field) {
          if (field.value !== "") {
            if (field.name in data) {
              if (data[field.name] instanceof Array) {
                data[field.name].push(field.value);
              } else {
                tmpValue = data[field.name];
                data[field.name] = [];
                data[field.name].push(tmpValue);
                data[field.name].push(field.value);
              }
            } else {
              data[field.name] = field.value;
            }
          }
        });
      }
      return data;
    }
  };

  $.fn.centreonInfiniteScroll = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonInfiniteScroll.defaults, options);
    var methodReturn = undefined;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonInfiniteScroll");

      if (!data) {
        $this.data("centreonInfiniteScroll", ( data = new CentreonInfiniteScroll(settings, $this)));
      }
      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };

  $.fn.centreonInfiniteScroll.defaults = {
    ajaxUrlGetScroll: "",
    ajaxUrlGetNew: "",
    refresh: 10000,
    padding: 10,
    limit: 20,
    formFilter: "",
    template: ""
  };
})(jQuery);
