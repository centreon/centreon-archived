function initCustomMacro() {
    $(".clonable").centreonClone();
    $( ".clonable" ).sortable({
        handle: ".clonehandle",
        containment: "parent",
        tolerance: "pointer"
    });
}

$(function() {
  initCustomMacro();
})

