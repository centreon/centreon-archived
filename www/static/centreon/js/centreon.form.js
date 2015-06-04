/*global jQuery:false */

(function ($) {
  
  function CentreonForm(settings, $elem) {
    var self = this,
        rules;
    this.settings = settings;
    this.$elem = $elem;
    this.groups = {};
    this.formUrl = this.$elem.data("route");

    this.initClassInput();

    /* Add event for change the float label */
    this.$elem.find("input, textarea").on(
      "focus",
      function () {
        var $parent = $(this).closest(".form-group"),
            $help = $parent.find("cite");
        self.getHelp($(this));
        $help.show();
        self.toggleLabel($(this));
      }
    )
    .on(
      "keyup change",
      function (e) {
        self.toggleLabel($(this));
      }
    )
    .on(
      "blur",
      function () {
        var $parent = $(this).closest(".form-group"),
            $help = $parent.find("cite");
        if (false === $parent.hasClass("has-error")) {
          $help.hide();
        }
        self.toggleLabel($(this));
      }
    );

    /* Add validation for this form */
    if (this.settings.rules === undefined) {
      rules = (formValidRule[this.$elem.id] === undefined ? {} : formValidRule[this.$elem.id]);
    } else {
      rules = this.settings.rules;
    }
    this.$elem.validate({
      rules: rules,
      invalidHandler: this.invalidHandler
    });
  }

  /**
   * Initialize label classes
   */
  CentreonForm.prototype.initClassInput = function () {
    var self = this;
    this.$elem.find(".form-group").each(function () {
      var name = $(this).find("input[name]").attr("name");
      /* Add group in cache */
      self.groups[name] = {
        help: undefined
      };

      /* Set css if input is empty */
      $(this).find("input[name]").each(function () {
        self.toggleLabel($(this));
      });
    });
  };

  /**
   * Get the help from ajax or cache
   *
   * @param {Object} $el: The input element
   */
  CentreonForm.prototype.getHelp = function ($el) {
    var self = this,
        $parent = $el.closest(".form-group"),
        $help = $parent.find("cite"),
        name = $el.attr("name");
    /* Test if the help is in cache */
    if (this.groups[name]["help"] !== undefined) {
      if (false === $parent.hasClass("has-error")) {
        $help.html(this.groups[name]["help"]);
      }
      return;
    }

    /* Get the help with ajax */
    $.ajax({
      url: this.settings.helpUrl,
      type: "get",
      dataType: "json",
      data: {
        form: this.formUrl,
        field: name
      },
      success: function (data) {
        if (false === $parent.hasClass("has-error")) {
          /* Add cache */
          self.groups[name]["help"] = data.text;
          $help.html(data.text);
        }
      }
    });
  };

  /**
   * Display the label for an element
   *
   * @param {Object} $el: The input element
   */
  CentreonForm.prototype.showLabel = function ($el) {
    $el.closest(".form-group").removeClass("js-hide-label").addClass("js-unhighlight-label");
    $el.css("padding", "16px 12px 6px");
  };

  /**
   * Hide the label for an element
   *
   * @param {Object} $el: The input element
   */
  CentreonForm.prototype.hideLabel = function ($el) {
    $el.closest(".form-group").addClass("js-hide-label").removeClass("js-unhighlight-label");
    $el.css("padding", "10px 12px 12px");
  };

  /**
   * Toggle the label by the value of the element
   *
   * @param {Object} $el: The input element
   */
  CentreonForm.prototype.toggleLabel = function ($el) {
    var $parent;
    if ($el.attr("name") === undefined) {
      $parent = $el.closest(".form-group");
      $el = $parent.find("input[name]");
    }
    if ($.trim($el.val()) !== "") {
      this.showLabel($el);
    } else {
      this.hideLabel($el);
    }
  };

  /**
   * Event for invalid hanlder, emit by jQuery validator
   *
   * @param {Event} event: The jQuery validator event
   * @param {Object} validator: The validator object
   */
  CentreonForm.prototype.invalidHandler = function (event, validator) {
    var formId = this.$elem.id,
        $list = $("#" + formId + "_errors"),
        $firstInput = $(validator.errorList[0].element);
    $list.children().remove();
    $("<li></li>").text("There are " + validator.errorList.length + " errors").appendTo($list);
    $list.closest(".flash").addClass("alert-danger").show();
    /* Focus the first element in error */
    $firstInput.focus();
    $("body").scrollTo($firstInput);
  };

  $.fn.centreonForm = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonForm.defaults, options);
    var methodReturn = undefined;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonForm");

      if (!data) {
        $this.data("centreonForm", ( data = new CentreonForm(settings, $this)));
      }

      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };

  $.fn.centreonForm.defaults = {
    helpUrl: "/form/help",
    rules: undefined
  };
})(jQuery);
