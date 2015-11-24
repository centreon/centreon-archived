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
    
    
    jQuery(elemA).each(function(idx, elem){
        var elementWith = jQuery(elem).width();
        var elementContentWith = elem.scrollWidth;
        if(elementWith < elementContentWith){
            var elemOldText = jQuery(elem).text();
            var elemOldHtml = jQuery(elem).html();
            var newHtml = jQuery('<div></div>');
            var popin = jQuery('<div></div>',{html : elemOldHtml, style : 'position:relative;width:700px;word-wrap:break-word;'}).appendTo(jQuery(elem));
            var newSpan = jQuery('<span></span>',{
                html : '...',
                style : 'cursor:pointer;visibility:hidden',
                title : elemOldText}
            );
            jQuery(elem).append(newSpan);
            var tmpWidth = newSpan.width();
            newSpan.click(function(){
                popin.centreonPopin("open");
            });
            
            newSpan.css('visibility','inherit');

            jQuery(elem).children().each(function (idx, el) {
                tmpWidth += jQuery(el).outerWidth(true);
                if (tmpWidth < elementWith) {
                    newHtml.append(jQuery(el).clone());
                }
            });
            newHtml.append(newSpan);
            jQuery(elem).empty().append(newHtml);
        }
    });

}