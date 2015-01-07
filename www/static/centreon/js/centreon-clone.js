var clone_events = {
  add: [],
  remove: [],
  change: []
};

$("body").on("click", ".clone-trigger", function() {
    
    $element = $('.clone_template').clone()
            .css("display", "block").removeClass('clone_template').addClass('cloned_element')
            .appendTo('.clonable');
    
    $(".cloned_element").each(function(idx, el) {
        var id = $('#cloned_element_index').val();
        $(el).find("input").each(function(idy, el2) {
            var elementName = $(el2).attr('name');
            $(el2).attr('name', elementName.replace("#index#", id));
        });
        $('#cloned_element_index').val(parseInt(id) + 1);
   });
   $('#cloned_element_index').val("0");
    
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

    $.each(clone_events.add, function(id, fct) {
      fct($element);
    });
   
    function cloneResort(id) {
        $('input[name^="clone_order_'+id+'_"]').each(function(idx, el) {
            $(el).val(idx);
        });
    }
});

$(document).on("click", '.remove-trigger', function() {
    var $parentEl = $(this).closest('li.cloned_element');
    $.each(clone_events.remove, function(id, fct) {
      fct($parentEl);
    });
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
    $.each(clone_events.change, function(id, fct) {
      fct($inputValueEl.closest('li.cloned_element'));
    });
});
