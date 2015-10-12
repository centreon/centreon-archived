function loadDescription(id)
{  
    var text = jQuery("#macroDescription_"+id).val();


    var popDescription = '<div class="MB_alert"><p><textarea rows="4" cols="80">'+text+'</textarea></p><input type="button" onclick="closePop('+id+')" value="OK" /></div>';
    if(typeof jQuery("#macroTpl_"+id) != 'undefined'){
        var fromTpl  = jQuery("#macroTpl_"+id).val();
        popDescription += '<div>'+ fromTpl +'</div>';
    }
    
    var popin = jQuery('<div/>',{html : popDescription}).css('position','relative');
    popin.appendTo('body');
    
    jQuery("#macroDescription_"+id)[0].myPopin = popin;
    popin.centreonPopin("open");
    popin.parent().addClass('fixedDiv');
}

function closePop(id)
{
    var popin = jQuery("#macroDescription_"+id)[0].myPopin;
    popin.centreonPopin("close");
    var content = popin.find('textarea').val();
    jQuery("#macroDescription_"+id).val(content);
}