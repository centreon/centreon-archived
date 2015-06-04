/* Initialize jQuery.validate */

$(function () {
  $.validator.setDefaults({
    ignore: "*:not([name])",
    highlight: function (element) {
      $(element).closest(".form-group").addClass("has-error");
    },
    unhighlight: function (element) {
      $(element).closest(".form-group").removeClass("has-error");
    },
    errorElement: "cite",
    errorClass: "errorNoClass",
    errorPlacement: function (error, element) {
      var $parent = $(element).closest(".form-group"),
          $help = $parent.find("cite");
      $help.text($(error).text());
      $help.show();
    }
  });
});
