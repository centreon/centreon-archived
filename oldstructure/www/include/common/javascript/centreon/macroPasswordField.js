jQuery(function() {
    jQuery("input[id^='macroPassword_']").each(function(id, el) {
        change_macro_input_type(el, true);
    });
});

function change_macro_input_type(box, must_disable) {
    var tmp = box.id.split('_');
    var macro_dom_id = tmp[1];
    var input = jQuery("#macroValue_" + macro_dom_id);

    if (must_disable === true) {
        jQuery(box).parent().hide();
    }
    input.removeAttr("type");
    if (box.checked) {
        input.prop('type', 'password');
    } else {
        input.prop('type', 'text');
    }
}