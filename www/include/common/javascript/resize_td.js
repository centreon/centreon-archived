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


function setOverflowDivToTitle(elemA,separator){
    jQuery(elemA).each(function(idx, elem){
        var tmp = jQuery('<span></span>');
        tmp.html(separator).css('display','inline-block').addClass('toto').css('visibility','hidden');
        jQuery(elem).append(tmp);
        var sepWidth = tmp.width();
        tmp.remove();
        var elementWith = jQuery(elem).width();
        var elementContentWith = elem.scrollWidth;
        var newHtml = '';
        var tmpWidth = 0;
        if(elementWith < elementContentWith){
            jQuery(elem).children().each(function (idx, el) {
                tmpWidth += sepWidth + jQuery(el).width();
                if (tmpWidth < elementWith) {
                                newHtml += separator + jQuery(el).html();
                }
            });
            newHtml += '<span style="cursor:pointer;" title="' + jQuery(elem).text() + '">...</span>';
            jQuery(elem).empty().html(newHtml);
        }
    });
}