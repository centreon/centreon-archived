function initTooltips() {
  $(".param-help").each(function() {
    $(this).qtip({
      content: {
        text: $(this).data("help"),
    title: $(this).data("helptitle"),
    button: true
      },
    position: {
      my: "top right",
    at: "bottom left",
    target: $(this)
    },
    show: {
      event: "click",
    solo: "true"
    },
    style: {
      classes: "qtip-bootstrap"
    },
    hide: {
      event: "unfocus"
    }
    });
  });
}
