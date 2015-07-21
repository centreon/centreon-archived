function initCustomCurveGraph() {
    $(".clonable").centreonClone({
      nbElementForScroll: 3,
      events: {
        add: [
          function(element) {
            element.find(".color-picker").spectrum({
              showInput: true,
              allowEmpty: true,
              preferredFormat: "hex"
            });
          },
        ],
        remove: [],
        change: []
      }
    });

    $( ".clonable" ).sortable({
      handle: ".clonehandle",
      containment: "parent",
      tolerance: "pointer"
    });

    $(".cloned_element .color-picker").spectrum({
      showInput: true,
      allowEmpty: true,
      preferredFormat: "hex"
    });
}

$(function() {
  initCustomCurveGraph();
})

