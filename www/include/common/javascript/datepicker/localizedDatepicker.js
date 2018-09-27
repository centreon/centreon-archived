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
 * Used to initialize datepicker with an alternative format field
 *
 * @param className string : tag class name
 * @param altFormat string : format of the alternative field
 * @param defaultDate string : datepicker parameter of the setDate
 * @param idName string : tag id of the displayed field
 * @param timestampToSet int : timestamp used to make a new date using the user localization and format
 */
function initDatepicker(className, altFormat, defaultDate, idName, timestampToSet) {
    className = className || "datepicker";
    altFormat =  altFormat || "mm/dd/yy";
    defaultDate = defaultDate || "O";

    setUserFormat();


    if (typeof(idName) == "undefined" || typeof(timestampToSet) == "undefined") {
        // initializing all the displayed and the hidden datepickers
        jQuery("." + className).each(function () {
            // finding all the alternative field
            var altName = jQuery(this).attr("name");
            if ("undefined" != jQuery(this).attr("name")) {
                var alternativeField = "input[name=alternativeDate" + altName[0].toUpperCase() + altName.slice(1) + "]";
                jQuery(this).datepicker({
                    //formatting the hidden fields using a specific format
                    altField: alternativeField,
                    altFormat: altFormat,
                    onSelect: function (date) {
                        alternativeField.val
                    }
                })
            } else {
                alert("Fatal : attribute name not found for the class " + className);
                jQuery(this).datepicker()
            }
        }).datepicker("setDate", defaultDate);
    } else {
        // setting the displayed and hidden fields with a timestamp value sent from the backend
        var alternativeField = "input[name=alternativeDate" + idName + "]";
        var dateToSet = new Date(timestampToSet);
        jQuery("#" + idName).datepicker({
            altField: alternativeField,
            altFormat: altFormat,
            onSelect: function (date) {
                alternativeField.val
            }
        }).datepicker('setDate', dateToSet);
    }
}

/**
 * Getting the user's localization, loading the corresponding library and setting the regional settings
 *
 * @param none
 */
function setUserFormat() {
    // Getting the local storage attribute
    var userLanguage = localStorage.getItem('locale').substring(0, 2);
    if ("en" != userLanguage &&
        "undefined" != userLanguage
    ) {
        jQuery('<script>')
            .attr('src', './include/common/javascript/datepicker/datepicker-' + userLanguage + '.js')
            .appendTo('body');
        setTimeout(function () {
            // checking if the localized library was launched
            if ("undefined" !=  typeof(jQuery.datepicker.regional[userLanguage])) {
                jQuery.datepicker.setDefaults(jQuery.datepicker.regional[userLanguage]);
            }
        })
    }
}
