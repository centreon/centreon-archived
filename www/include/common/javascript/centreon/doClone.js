jQuery(function() {
   
    jQuery(".clonable").each(function(idx, el) {
       var suffixid = jQuery(el).attr('id');
       jQuery(el).sheepIt({
           separator: '',
           allowRemoveLast: true,
           allowRemoveCurrent: true,
           allowRemoveAll: true,
           minFormsCount: 0,
	   maxFormsCount: 200,
           iniFormsCount: jQuery("#clone-count-" + suffixid).data("clone-count-" + suffixid),
           data: jQuery("#clone-values-" + suffixid).data("clone-values-" + suffixid),
           afterAdd: function(source, newForm) {
                var dataSet = jQuery("#clone-values-" + suffixid).data("clone-values-" + suffixid);
                if(typeof dataSet !== "undefined" && dataSet !== null && typeof dataSet[newForm.getPosition() - 1] !== "undefined"){
                    var currentLine = dataSet[newForm.getPosition() - 1];
                    if(typeof currentLine.style !== "undefined"){
                        currentLine.style.each(function( style, index ){
                            jQuery(newForm).find("input[name^='macroInput']").css(style.prop,style.value);
                            jQuery(newForm).find("input[name^='macroValue']").css(style.prop,style.value);
                        });
                    }
                    jQuery(newForm).find( "input[id^='resetMacro']" ).click(function(){
                        jQuery(newForm).find("input[id^='macroValue']").val(currentLine['macroValue_#index#']);
                    });
                }
           }
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