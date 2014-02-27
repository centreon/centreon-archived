$('.clone-trigger').click(function() {
    
    $('.clone_template').clone()
            .css("display", "block").removeClass('clone_template')
            .appendTo('.clonable');
   
    $(".clonable").sortable({
        handle: ".clonehandle",
        axis: "y",
        helper: "clone",
        opacity: 0.5,
        placeholder: "clone-placeholder",
        tolerance: "pointer",
        stop: function(event, ui) {
            cloneResort($(this).attr('id'));
        }
    });
   
    function cloneResort(id) {
        $('input[name^="clone_order_'+id+'_"]').each(function(idx, el) {
            $(el).val(idx);
        });
    }
});

$(document).on("click", '.remove-trigger', function() {
    console.log(this);
    var currentEl = $(this);
    var parentEl = currentEl.parent();
    $(parentEl).remove();
});