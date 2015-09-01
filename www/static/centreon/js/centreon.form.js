/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

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
      invalidHandler: function (event, validator) {
        self.invalidHandler(event, validator);
      }
    });
  }

  /**
   * Initialize label classes
   */
  CentreonForm.prototype.initClassInput = function () {
    var self = this;
    this.$elem.find(".form-group").each(function () {
      var name = $(this).find("input[name], textarea[name]").attr("name");
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

    /* Test if name is set, else it's not the primary element input */
    if (name === undefined) {
      name = $parent.find("input[name]").attr("name");
    }

    /* Test if the help is in cache */
    if ((name !== undefined) && (this.groups[name]["help"] !== undefined)) {
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
    if (($.trim($el.val()) !== "") || ($el.hasClass("select2-offscreen"))) {
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
    $("body").scrollTop($firstInput);
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
