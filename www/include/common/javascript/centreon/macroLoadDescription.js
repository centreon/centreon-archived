function loadDescription(id)
{  
    var text = jQuery("#macroDescription_"+id).val();
    var popDescription = '<div class="MB_alert"><p><textarea rows="4" cols="80">'+text+'</textarea></p><input type="button" onclick="closePop('+id+')" value="OK" /></div>';
    Modalbox.show(popDescription, {title: 'Description: ' + document.title, width: 800});
}

function closePop(id)
{
    Modalbox.hide();
    var content = jQuery('textarea').val();
    jQuery("#macroDescription_"+id).val(content);
}