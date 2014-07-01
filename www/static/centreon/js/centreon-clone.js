$("body").on("click", ".clone-trigger", function() {
    
    $('.clone_template').clone()
            .css("display", "block").removeClass('clone_template').addClass('cloned_element')
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
    var $parentEl = $(this).closest('li.cloned_element');
    $($parentEl).remove();
});

$(document).on("change", 'input.hidden-value-trigger', function() {
    var $inputValueEl = $(this).closest('li.cloned_element').find('input.hidden-value');
    $inputValueEl.removeAttr('type');
    if (this.checked) {
        $inputValueEl.attr('type', 'password');
    } else {
        $inputValueEl.attr('type', 'text');
    }
});