$(function() {
    $(".clonable").centreonClone();
    $( ".clonable" ).sortable({
        handle: ".clonehandle",
        containment: "parent",
        tolerance: "pointer"
    });
    /*
    $('.scrollable').slimScroll({
        height: '350px',
        railOpacity: 0.9,
        disableFadeOut: true
                });
    */
    
})

