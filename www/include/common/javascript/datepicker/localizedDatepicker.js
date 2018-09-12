/*
* Copyright 2005-2018 Centreon
* Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * Used for initializing datepicker with an alternative format field
 *
 * @param className string : tag class name
 * @param altFormat string : format of the alternative field
 * @param defaultDate string : datepicker parameter of the setDate
 */
function initDatepicker(className, altFormat, defaultDate) {

    if ("undefined" == className) {
        className = "datepicker";
    }

    if ("undefined" == altFormat) {
        altFormat = "mm/dd/yy";
    }

    if ("undefined" == defaultDate) {
        defaultDate = "0";
    }

    /* Getting user's localization and loading corresponding library */
    var userLanguage = localStorage.getItem('locale').substring(0, 2);
    if ("en" != userLanguage &&
        "undefined" != userLanguage
    ) {
        jQuery('<script>')
            .attr('src', './include/common/javascript/datepicker/datepicker-' + userLanguage + '.js')
            .appendTo('body');
        setTimeout(function () {
            if ("undefined" !=  typeof(jQuery.datepicker.regional[userLanguage])) {
                jQuery.datepicker.setDefaults(jQuery.datepicker.regional[userLanguage]);
            }
        })
    }

    /* initializing datepicker and the hidden alternate format field */
    jQuery("." + className).each(function () {
        /* finding the alternative field */
        var altName = jQuery(this).attr("name");
        var alternativeField = "input[name=alternativeDate" + altName[0].toUpperCase() + altName.slice(1) + "]";
        jQuery(this).datepicker({
            altField: alternativeField,
            altFormat: altFormat,
            onSelect: function (date) {
                alternativeField.val
            }
        })
    }).datepicker("setDate", defaultDate);
}