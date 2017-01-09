/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */
 
function setOverflowDivToTitle(elemA){
    jQuery(elemA).contents().filter(function() {
        return this.nodeType === 3;
    }).each(function() {
        //this.nodeValue = jQuery.trim(this.nodeValue); code here if you want to apply some modification to the node value
    }).wrap('<span class="unWrapedElement"></span>');
    jQuery(elemA).wrapInner('<div style="display:inline-block"></div>');

    jQuery(elemA).each(function(idx, elem){
        var elementWith = jQuery(elem).width();
        var elementContentWith = jQuery(elem).children(":first-child").width();
        var wrapper = jQuery(elem).children(":first-child");
        if (elementWith < elementContentWith) {
            var elemOldText = jQuery(elem).children(":first-child").text();
            var elemOldHtml = jQuery(elem).children(":first-child").html();
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
            
            if (wrapper.children().length > 1) {
                wrapper.children().each(function (idx, el) {
                    tmpWidth += jQuery(el).outerWidth(true);
                    if (tmpWidth < elementWith) {
                        newHtml.append(jQuery(el).clone());
                    }
                });
            } else {
                var childtext = wrapper.children().text();
                var textLenght = childtext.length;
                var maxTextLenght = elementWith / (elementContentWith / textLenght);
                var finalText = TextAbstract(wrapper.children().text(), maxTextLenght);
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