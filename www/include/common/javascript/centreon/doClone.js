jQuery(function() {
   
    jQuery(".clonable").each(function(idx, el) {
       var suffixid = jQuery(el).attr('id');
       jQuery(el).sheepIt({
           separator: '',
           allowRemoveLast: true,
           allowRemoveCurrent: true,
           allowRemoveAll: true,
           minFormsCount: 0,
           iniFormsCount: jQuery("#clone-count-" + suffixid).data("clone-count-" + suffixid),
           data: jQuery("#clone-values-" + suffixid).data("clone-values-" + suffixid)
       });
       cloneResort(suffixid);
   });
   
   jQuery(".clonable").sortable(
                           {
                             handle: ".clonehandle",
                             axis: "y",
                             helper: "clone",
                             opacity: 0.5,
                             placeholder: "clone-placeholder",
                             tolerance: "pointer",
                             stop: function(event, ui) {
                                 cloneResort(jQuery(this).attr('id'));
                             }
                           }
                        );
   
   function cloneResort(id) {
        jQuery('input[name^="clone_order_'+id+'_"]').each(function(idx, el) {
            jQuery(el).val(idx);
        });
   }
});