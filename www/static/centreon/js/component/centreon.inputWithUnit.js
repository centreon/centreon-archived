/**
 * Generate a input with unit choice
 */
(function ($) {
  /**
   * Constructor
   *
   * @param {Object} settings: The Options of the components
   * @param {Object} $elem: The element
   */
  function CentreonInputWithUnit(settings, $elem) {
    var self = this;
    this.settings = settings;
    this.$elem = $elem;

    this.createElement();
    this.init();

    /* Add event for change value on input field */
    this.$input.on("blur", function () {
      var unit = self.$btn.find("span.legend").text(),
          $legentEl;
      $legentEl = self.$listUnit.find("> li > a").filter(function () {
        if ($(this).find("span.legend").text() == unit) {
          return true;
        }
        return false;
      });
      self.setUnit($legentEl);
    });
    this.$listUnit.find("a").on("click", function () {
      self.setUnit($(this));
    });
  }

  /**
   * Create the element
   */
  CentreonInputWithUnit.prototype.createElement = function () {
    var self = this,
        $group = $("<div class='input-group'></div>");
    this.$btn = $("<button type='button' class='btnC btnDefault dropdown-toggle' data-toggle='dropdown' aria-expanded='false'><span class='legend'></span> <span class='caret'></span></button>");
    this.$input = $("<input type='number' class='form-control'>");
    this.$listUnit = $("<ul class='dropdown-menu dropdown-menu-right' role='menu'></ul>");
    $.each(this.settings.units, function (idx, unit) {
      var $legend = $("<span class='legend'></span>").text(unit.legend),
          $link = $("<a href='#'></a>").text(" " + unit.text).prepend($legend);
      $("<li></li>").append($link).appendTo(self.$listUnit);
    });

    $group.append(this.$input);
    $("<div class='input-group-btn'></div>").append(this.$btn).append(this.$listUnit).appendTo($group);

    /* Force input to type hidden */
    if (this.$elem.attr("type") != "hidden") {
      this.$elem.attr("type", "hidden");
    }

    this.$elem.parent().prepend($group); //.append(this.$unit);

    if ($("input[name='" + this.$elem.attr("name") + "_unit']").length === 0) {
      this.$unit = $("<input type='hidden'>").attr("name", this.$elem.attr("name") + "_unit").appendTo(this.$elem.parent());
    } else {
      this.$unit = $("input[name='" + this.$elem.attr("name") + "_unit']");
    }
  };

  /**
   * Initialize the element
   */
  CentreonInputWithUnit.prototype.init = function () {
    var val = this.$elem.val(),
        unit, $el;
    
    if (this.$unit.val() === undefined || this.$unit.val() === "") {
      unit = this.$elem.data("unit");
    } else {
      unit = this.$unit.val();
    }
    
    if (unit === undefined) {
      unit = this.settings.units[0].legend;
      multiple = this.settings.units[0].multiple;
    } else {
      multiple = this.getUnitByLegend(unit).multiple;
    }

    $el = this.$listUnit.find("> li > a").filter(function () {
      if ($(this).find("span.legend").text() == unit) {
        return true;
      }
      return false;
    });

    if (val !== undefined && val != 0) {
      this.$input.val(parseFloat(val) / multiple);
    }

    this.setUnit($el, false);
  };

  /**
   * Set unit
   *
   * @param {Object} $el: The element selected
   * @param {Boolean} refreshValue: If refresh value in form input
   */
  CentreonInputWithUnit.prototype.setUnit = function ($el, refreshValue) {
    var legend = $el.find("span.legend").text(),
        refreshValue = (refreshValue === undefined ? true : refreshValue),
        inputValue;
    this.$btn.find("span.legend").text(legend);
    this.$unit.val(legend);

    if (refreshValue) {
      inputValue = parseFloat(this.$input.val());
      if (isNaN(inputValue)) {
        this.$elem.val("");
      } else {
        this.$elem.val(inputValue * this.getUnitByLegend(legend).multiple);
      }
    }
  };

  /**
   * Get unit dict
   *
   * @param {String} legend: The legend
   * @return {Object}
   */
  CentreonInputWithUnit.prototype.getUnitByLegend = function (legend) {
    var i = 0;
    for (i; i < this.settings.units.length; i = i + 1) {
      if (this.settings.units[i].legend == legend) {
        return this.settings.units[i];
      }
    }
    return undefined;
  };

  $.fn.centreonInputWithUnit = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonInputWithUnit.defaults, options);
    var methodReturn = undefined;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonInputWithUnit");

      if (!data) {
        $this.data("centreonInputWithUnit", ( data = new CentreonInputWithUnit(settings, $this)));
      }

      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };

  $.fn.centreonInputWithUnit.defaults = {
    units: [
      {
        legend: "s",
        text: "seconds",
        multiple: 1
      },
      {
        legend: "m",
        text: "minutes",
        multiple: 60
      },
      {
        legend: "h",
        text: "hours",
        multiple: 3600
      }
    ]
  };
})(jQuery);
