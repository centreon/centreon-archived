/*global jQuery: false */
/**
 * Plugins for manage scheduled downtime component
 */
(function ($) {

  /**
   * The contructor of the object
   *
   * @param {Object} setting: The settings 
   * @param {jQuery} $elem: The jQuery object element
   */
  function CentreonScheduledDowntime(settings, $elem) {
    var self = this;
    this.settings = settings;
    this.$elem = $elem;
    
    /* List of periods */
    this.periodFocus = 0;
    this.currentPeriod = {
      pos: 0,
      days: []
    };
    this.periodPos = 1;
    this.periods = {};
    /* Define the template for a period listing */
    this.periodString = Hogan.compile('<li class="legend legend-{{pos}}"> \
      <a href="#" class="period-name" data-period-pos="{{pos}}"> \
        <span class="color"></span> \
        Period \
      </a> \
      <a class="delete-period" href="#" data-period-pos="{{pos}}"> \
      <i class="fa fa-times-circle pull-right"></i> \
      </a> \
      <a class="edit-period" href="#" data-period-pos="{{pos}}"> \
      <i class="fa fa-pencil pull-right"></i> \
      </a> \
      </li>');


    /* Initialize time select components */
    this.$elem.find("input[name='time_start']").datetimepicker(
        {
          format: "HH:mm"
        }
    );
    this.$elem.find("input[name='time_end']").datetimepicker(
        {
          format: "HH:mm"
        }
    );

    /* Add event for add a period */
    this.$elem.find(".addPeriodBtn").on("click", function (e) {
      e.preventDefault();
      self.addPeriod();
    });

    /* Add event for change type */
    this.$elem.find("select[name='period_type']").on("change", function () {
      var val = $(this).val();
      self.cleanDays();
      self.changeMode(val);
    });

    /* Add event for cancel */
    this.$elem.find(".cancelPeriodBtn").on("click", function (e) {
      e.preventDefault();
      self.currentPeriod = {
        pos: 0,
        days: []
      }
      self.$elem.find(".period-info").hide();
    });

    /* Add event for valid a period */
    this.$elem.find(".validPeriodBtn").on("click", function (e) {
      e.preventDefault();
      alertClose();
      var periodPos = self.currentPeriod.pos;
      var timeStart = self.$elem.find("input[name='time_start']").val();
      var timeEnd = self.$elem.find("input[name='time_end']").val();
      var fixed = self.$elem.find("input[name='fixed']:checked").val();
      var duration = self.$elem.find("input[name='duration']").val();
      var inError = false;
      var errorMsg = "";
      self.$elem.find(".has-error").removeClass("has-error");

      /* Validate period form data */
      if (self.currentPeriod.days.length === 0) {
        errorMsg += "No period day are selected.<br>";
        inError = true;
      }
      if (timeStart.trim() === '') {
        self.$elem.find("input[name='time_start']").parent(".form-group").addClass("has-error");
        errorMsg += "The time start must be set.<br>";
        inError = true;
      }
      if (timeEnd.trim() === '') {
        errorMsg += "The time end must be set.<br>";
        self.$elem.find("input[name='time_end']").parent(".form-group").addClass("has-error");
        inError = true;
      }
      if (fixed === 'flexibled' && duration.trim() === '') {
        errorMsg += "The duration must be set if the downtime is flexible.<br>";
        self.$elem.find("input[name='duration']").parent(".form-group").addClass("has-error");
        inError = true;
      }

      if (false === inError) {
        self.periods[periodPos] = {
          periodType: self.$elem.find("select[name='period_type']").val(),
          timeStart: timeStart,
          timeEnd: timeEnd,
          fixed: fixed,
          duration: duration,
          days: self.convertDaysForPeriod(
            self.$elem.find("select[name='period_type']").val(),
            self.currentPeriod.days
          )
        };

        self.currentPeriod = {
          pos: 0,
          days: []
        };

        self.$elem.find(".period-info").hide();

        /* Save into an input the value for send on post */
        $("#" + self.$elem.data("input-id")).val(JSON.stringify(self.periods));
      } else {
        alertMessage(errorMsg, "alert-danger");
      }
    });

    /* Add event for edit a period */
    this.$elem.on("click", ".periods .list .edit-period", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var pos = $(this).data("period-pos");
      self.editPeriod(pos);
    });

    /* Add event for delete a period */
    this.$elem.on("click", ".periods .list .delete-period", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var pos = $(this).data("period-pos");
      self.$elem.find(".calendar .days .spot-" + pos).remove();
      $(this).parent("li.legend").remove();
      self.periods[pos] = undefined;
    });

    /* Add event for focus */
    this.$elem.on("click", ".periods .list .period-name", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var pos = $(this).data("period-pos");
      if (self.periodFocus != pos) {
        self.periodFocus = pos;
        self.$elem.find(".calendar .days .spot").addClass("inactive");
        self.$elem.find(".periods .list .legend").addClass("inactive");
        self.$elem.find(".calendar .days .spot-" + pos).removeClass("inactive");
        self.$elem.find(".periods .list .legend-" + pos).removeClass("inactive");
      } else {
        self.periodFocus = 0;
        self.$elem.find(".calendar .days .spot").removeClass("inactive");
        self.$elem.find(".periods .list .legend").removeClass("inactive");
      }
    });

    /* Recalculate height of days */
    this.$elem.find(".calendar > .days > div").each(function (idx, elem) {
      var elWidth = $(elem).width();
      $(elem).height(elWidth);
    });

    /* Make month days selectable */
    this.$elem.find(".calendar > .days").selectable({
      disabled: true,
      filter: "> div",
      stop: function () {
        if (false === $(this).selectable("option", "disabled")) {
          var selectedList = $(this).find(".ui-selected");
          self.toggleDays(selectedList);
          /* Remove selection */
          $(this).find(".ui-selected").removeClass("ui-selected");
        }
      }
    });

    /* Load periods */
    var url = this.$elem.data("load-url");
    if (url !== undefined && url !== "") {
      $.ajax({
        url: url,
        type: "get",
        dataType: "json",
        success: function (data, textStatus, jqXHR) {
          $.each(data, function (idx, period) {
            self.periods[self.periodPos] = period;
            self.loadDays(self.periodPos, period);
            self.addPeriod(false);
            self.periodPos++;
          });
        }
      });
    }
  }

  CentreonScheduledDowntime.prototype = {
    /**
     * Toggle selection mode to none
     */
    noneMode: function () {
      /* Disable selectable monthly days */
      this.$elem.find(".calendar > .days").selectable("disable");
      
      /* Disable selectable custom days */
      this.$elem.find(".calendar > .days > div")
        .css("cursor", "default")
        .off("mouseover")
        .off("mouseout")
        .off("click");

      this.$elem.find(".calendar > .week-days > div")
        .css("cursor", "default")
        .off("mouseover")
        .off("mouseout")
        .off("click");
    },
    /**
     * Toggle selection mode to weekly mode
     */
    weeklyMode: function () {
      var self = this;
      this.noneMode();
      /* Enable selectable weekly day */
      this.$elem.find(".calendar > .week-days > div")
        .css("cursor", "pointer")
        .on("mouseover", function () {
          var dayName = $(this).data("wday");
          self.$elem
            .find(".calendar > .days [data-wday='" + dayName + "']")
            .addClass("ui-selecting");
        })
        .on("mouseout", function () {
          var dayName = $(this).data("wday");
          self.$elem
            .find(".calendar > .days [data-wday='" + dayName + "']")
            .removeClass("ui-selecting");
        })
        .on("click", function () {
          var wday = $(this).data("wday");
          self.toggleDays(self.$elem.find(".calendar .days [data-wday='" + wday + "']"));
        });
    },
    /**
     * Toggle selection mode to monthly
     */
    monthlyMode: function () {
      this.noneMode();
      //this.addMouseOver();
      /* Enable selectable monthly days */
      this.$elem.find(".calendar > .days").selectable("enable");
    },
    /**
     * Toggle selection mode to custom
     */
    customMode: function () {
      var self = this;
      this.noneMode();
      this.addMouseOver();
      this.$elem.find(".calendar > .days > div")
        .on("click", function () {
          self.toggleDays([this]);
        });
    },
    /**
     * Add mouse over change background for a cell day
     */
    addMouseOver: function () {
      this.$elem.find(".calendar > .days > div")
        .css("cursor", "pointer")
        .on("mouseover", function () {
          $(this).addClass("ui-selecting");
        })
        .on("mouseout", function () {
          $(this).removeClass("ui-selecting");
        });
    },
    /**
     * Reset the form to empty or set with a period
     *
     * @param {Object} period: The period information to set or undefined if a new period
     */
    resetForm: function (period) {
      var info = $.extend(
          {},
          {
            periodType: 'weekly',
            timeStart: '',
            timeEnd: '',
            fixed: 'fixed',
            duration: ''
          },
          period
      );
      this.$elem.find(".has-error").removeClass("has-error");
      this.$elem.find("input[name='time_start']").val(info.timeStart);
      this.$elem.find("input[name='time_end']").val(info.timeEnd);
      this.$elem.find("input[name='duration']").val(info.duration);
      this.$elem.find("input[name='fixed']").val(info.fixed);
      this.$elem.find("select[name='period_type']").val(info.periodType);

      this.changeMode(info.periodType);
    },
    /**
     * Change the mode of selection
     *
     * @param {string} mode: The selection mode
     */
    changeMode: function (mode) {
      switch (mode) {
        case 'weekly':
          this.weeklyMode();
          break;
        case 'monthly':
          this.monthlyMode();
          break;
        case 'custom':
          this.customMode();
          break;
        default:
          this.noneMode();
          break;
      }
    },
    /**
     * Add a period
     *
     * A new period element to list and open the edit panel
     */
    addPeriod: function (load) {
      var load = load === undefined ? true : load;
      if (this.currentPeriod.pos != 0) {
        alertMessage("You must valid or cancel the current period before add a new.", "alert-warning", 10);
        return;
      }

      var $periodEl = $(this.periodString.render({ pos: this.periodPos }));
      this.$elem.find(".periods .list").append($periodEl);

      if (load) {
        this.periods[this.periodPos] = {};

        this.currentPeriod = {
          pos: this.periodPos,
          days: []
        };

        this.periodPos++;

        this.weeklyMode();

        this.resetForm();

        this.$elem.find(".period-info").show();
      }
    },
    /**
     * Edit a period
     *
     * @param {integer} pos: The period position
     */
    editPeriod: function (pos) {
      var $el = this.$elem.find("[data-period-pos='" + pos + "']");

      if ($el.length > 0) {
        this.currentPeriod = {
          pos: pos,
          days: this.convertDaysForDisplay(this.periods[pos].periodType, this.periods[pos].days)
        };

        this.resetForm(this.periods[pos]);

        this.$elem.find(".period-info").show();
      }
    },
    /**
     * Add/remove a day or a group of days to a period
     *
     * @param {Array} listDays: A list of days
     */
    toggleDays: function (listDays) {
      var self = this;
      $.each(listDays, function (idx, el) {
        if (-1 === $.inArray(el, self.currentPeriod.days)) {
          self.currentPeriod.days.push(el);
          self.addSpot([el]);
        } else {
          self.currentPeriod.days = $.grep(self.currentPeriod.days, function (val) {
            return val !== el;
          });
          self.delSpot([el]);
        }
      });
    },
    /**
     * Add a spot for mark the day is in a period
     *
     * @param {Array} listDays: A list of days
     */
    addSpot: function (listDays) {
      var classColor = "spot-" + this.currentPeriod.pos;
      var $spotBaseEl = $("<span></span>")
        .addClass("spot")
        .addClass("pull-right")
        .addClass(classColor);
      $.each(listDays, function (idx, el) {
        $(el).find(".period-spot").append($spotBaseEl.clone());
      });
      $spotBaseEl.remove();
    },
    /**
     * Remove a spot for mark the day is in a period
     *
     * @param {Array} listDays: A list of days
     */
    delSpot: function (listDays) {
      var classColor = ".spot-" + this.currentPeriod.pos;
      $.each(listDays, function(idx, el) {
        $(el).find(classColor).remove();
      });
    },
    /**
     * Remove all selected days for a period
     */
    cleanDays: function () {
      this.delSpot(this.currentPeriod.days);
      this.currentPeriod.days = [];
    },
    /**
     * Convert display days to period day to save in database
     *
     * @param {string} type: The time period type
     * @param {Array} days: The list of spot days
     * @return {Array}
     */
    convertDaysForPeriod: function (type, days) {
      var result = [];
      var i = 0;
      switch (type) {
        case 'weekly':
          for (i; i < days.length; i++) {
            if ($(days[i]).data("day") < 8) {
              result.push($(days[i]).data("day"));
            }
          }
          break;
        case 'monthly':
          for (i; i < days.length; i++) {
            result.push($(days[i]).data("day"));
          }
          break;
        case 'custom':
          var day;
          var wday;
          var nthDay;
          for (i; i < days.length; i++) {
            day = $(days[i]).data("day");
            wday = day % 7;
            nthDay = ((day - 1) / 7) + 1;
            result.push({
              wday: wday,
              nthDay: nthDay
            });
          }
          break;
        default:
          break;
      }
      return result;
    }, 
    convertDaysForDisplay: function (type, days) {
      var result = [];
      var i = 0;
      var day;
      var self = this;

      switch (type) {
        case 'weekly':
          for (i; i < days.length; i++) {
            /* Set all month day for a week day */
            day = parseInt(days[i]);
            do {
              result.push(day);
              day += 7;
            } while (day < 32);
          }
          break;
        case 'monthly':
          result = days;
          break;
        case 'custom':
          for (i; i < days.length; i++) {
            day = (7 * (days[i].nthDay - 1)) + days[i].wday;
            result.push(day);
          }
          break;
      }

      result = result.map(function (day) {
        days = self.$elem.find(".calendar .days [data-day='" + day + "']");
        if (days.length > 0) {
          return days[0];
        }
        return undefined;
      });

      return result;
    },
    loadDays: function (pos, period) {
      var displayDays = this.convertDaysForDisplay(period.periodType, period.days);
      this.currentPeriod.pos = pos;
      this.addSpot(displayDays);
      this.currentPeriod.pos = 0;
    }
  };
  
  /**
   * Method for initialize Javascript components
   *
   * @param {Object} options: The options for the components
   */
  $.fn.centreonScheduledDowntime = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonScheduledDowntime.defaults, options);
    var methodReturn = undefined;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonScheduledDowntime");

      if (!data) {
        $this.data("centreonScheduledDowntime", (data = new CentreonScheduledDowntime(settings, $this)));
      }
      
      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };

  /**
   * Default options
   */
  $.fn.centreonScheduledDowntime.defaults = {
  };
})(jQuery);

/* Initialize all scheduled downtime components */
$(function () {
  $(".scheduled-downtime").centreonScheduledDowntime();
});
