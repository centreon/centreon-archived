/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
// JavaScript Document


function setOverflowDivToTitle(elemA){


    jQuery(elemA).contents().filter(function() {
        return this.nodeType === 3;
    }).each(function() {
        //this.nodeValue = jQuery.trim(this.nodeValue); code here if you want to apply some modification to the node value
    }).wrap('<span class="unWrapedElement"></span>');
    jQuery(elemA).wrapInner('<div style="display:inline-block" ></div>');
    
    jQuery(elemA).each(function(idx, elem){
        var elementWith = jQuery(elem).width();
        var elementContentWith = jQuery(elem).children( ":first-child" ).width();
        var wrapper = jQuery(elem).children( ":first-child" );
        if(elementWith < elementContentWith){
            var elemOldText = jQuery(elem).children( ":first-child" ).text();
            var elemOldHtml = jQuery(elem).children( ":first-child" ).html();
            var newHtml = jQuery('<div></div>');
            var popin = jQuery('<div></div>',{html : elemOldHtml, style : 'position:relative;width:700px;word-wrap:break-word;'}).appendTo(jQuery(elem));
            var newSpan = jQuery('<span></span>',{
                html : '...',
                style : 'cursor:pointer;visibility:hidden',
                title : elemOldText}
            );
            jQuery(elem).append(newSpan);
            var tmpWidth = newSpan.outerWidth(true);
            newSpan.click(function(){
                popin.centreonPopin("open");
            });
            
            newSpan.css('visibility','inherit');
            
            if(wrapper.children().length > 1){
                wrapper.children().each(function (idx, el) {
                    tmpWidth += jQuery(el).outerWidth(true);
                    if (tmpWidth < elementWith) {
                        newHtml.append(jQuery(el).clone());
                    }
                });
            }else{
                var childtext = wrapper.children().text();
                var textLenght = childtext.length;
                var maxTextLenght = elementWith / (elementContentWith / textLenght);
                var finalText = TextAbstract(wrapper.children().text(),maxTextLenght);
                var clone = wrapper.children().clone().text(finalText);
                newHtml.append(clone);
            }
            newHtml.append(newSpan);
            jQuery(elem).empty().append(newHtml);
        }
    });

}


function TextAbstract(text, length) {
    if (text == null) {
        return "";
    }
    if (text.length <= length) {
        return text;
    }
    text = text.substring(0, length);
    last = text.lastIndexOf(" ");
    text = text.substring(0, last);
    return text;
}