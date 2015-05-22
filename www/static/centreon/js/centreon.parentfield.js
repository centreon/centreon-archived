/**
 * Plugin for manage display field related an another field depend a value
 */
(function ($) {

  function CentreonParentField(settings, $elem) {
    var name = $elem.attr("name");
    var searchString = "[data-parentfield='" + name + "']";
    var self = this;
    self.elements = {};
    /* Get children element */
    $(searchString).each(function (index) {
      var $childEl = $(this);
      var values = $childEl.data("parentvalue");
      if (typeof values === "string" && values.indexOf(",")) {
        $.each(values.split(","), function (idx, value) {
          if (self.elements[value] === undefined) {
            self.elements[value] = [];
          }
          self.elements[value].push($childEl);
        });
      } else {
        if (self.elements[values] === undefined) {
          self.elements[values] = [];
        }
        self.elements[values].push($childEl);
      }
    });

    /* Add event */
    $elem.on("blur change", function () {
      if ($elem.attr("type") == "checkbox") {
        if ($elem.is(":checked")) {
          self.display(1);
        } else {
          self.display(0);
        }
      } else {
        self.display($(this).val());
      }
    });

    /* Initialize */
    if ($elem.attr("type") == "radio") {
      if ($elem.is(":checked")) {
        self.display($elem.val());
      }
    } else if ($elem.attr("type") == "checkbox") {
      if ($elem.is(":checked")) {
        self.display(1);
      } else {
        self.display(0);
      }
    } else {
      self.display($elem.val());
    }
  }

  CentreonParentField.prototype = {
    /**
     * Display or hide children field
     *
     * @param {String} value The value to compare
     */
    display: function (value) {
      $.each(this.elements, function (key, val) {
        if (key == value) {
          $.each(val, function (idx, $elem) {
            $elem.parents(".form-group").show();
          });
        } else {
          $.each(val, function (idx, $elem) {
            $elem.parents(".form-group").hide();
          });
        }
      });
    }
  };

  $.fn.centreonParentField = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);

    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonParentField");
      var methodReturn;

      if (!data) {
        $this.data("centreonParentField", (data = new CentreonParentField(options, $this)));
      }
      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };
})(jQuery);


/**
 * Load information and initialize parent field
 */
function loadParentField() {
  $("[data-parentfield]").each(function (element) {
    var parentName = $(this).data('parentfield');
    $("[name='" + parentName + "']:not(.centreon-search)").centreonParentField();
  });
}

/* Generic load */
$(function () {
  loadParentField();
});
