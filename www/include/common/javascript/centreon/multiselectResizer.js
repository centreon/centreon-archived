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

/**
 * This is used for auto resizing the multiselect boxes when they
 * tend to get too large
 *
 * minAllowedWidth can be modified
 */
jQuery(function() {
    // minimum width of the selectbox, change it if you want
    var minAllowedWidth = 360;

    // maximum width of the selectbox, twice larger than minimum width
    var maxAllowedWidth = (minAllowedWidth * 2);

    var maxWidth = 0;

    // get maximum width of all multiselect boxes
    // we want them to have the same size
    jQuery("select[multiple=multiple]").each(function() {
        if(!jQuery(this).width() || jQuery(this).css('visibility') == 'hidden') {
            return;
        }

        var htmlText = jQuery('<span style="display:none;"></span>');
        htmlText.appendTo(jQuery('body'));

        jQuery(this).children("option").each(function() {
            var curLen;

            htmlText.text(jQuery(this).text());
            curLen = htmlText.width() + 20; // ~ scrollbar width
            if (curLen > maxWidth) {
                maxWidth = curLen;
            }
        });
    });

    // set min width
    if (maxWidth < minAllowedWidth) {
        maxWidth = minAllowedWidth;
    }
                                         
    // set max width
    if (maxWidth > maxAllowedWidth) {
        maxWidth = maxAllowedWidth;
    }
   
    // resize all boxes 
    jQuery("select[multiple=multiple]").each(function() {
        if(!jQuery(this).width() || jQuery(this).css('visibility') == 'hidden') {
            return;
        }

        // resize
        jQuery(this).width(maxWidth);
    });
});
