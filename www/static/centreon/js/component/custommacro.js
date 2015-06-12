function initCustomMacro() {
    $(".clonable").centreonClone({
        nbElementForScroll: 3
    });
    $( ".clonable" ).sortable({
        handle: ".clonehandle",
        containment: "parent",
        tolerance: "pointer"
    });
}

$(function() {
  initCustomMacro();
})

