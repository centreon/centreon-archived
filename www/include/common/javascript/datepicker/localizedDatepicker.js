/*
* Copyright 2005-2020 Centreon
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
 * @param defaultDate string : datepicker parameter of the setDate - GMT YYYY-MM-DDTHH:mm:ss timestamp
 * @param idName string : tag id of the displayed field
 * @param timestampToSet int : timestamp used to make a new date using the user localization and format
 */
function initDatepicker(className, altFormat, defaultDate, idName, timestampToSet) {
    className = className || "datepicker";
    altFormat =  altFormat || "mm/dd/yy";
    defaultDate = defaultDate || "0";

    setUserFormat();

    var timezone = localStorage.getItem('realTimezone') || moment.tz.guess();

    if (typeof(idName) == "undefined" || typeof(timestampToSet) == "undefined") {
        // generate default date in proper timezone
        if (defaultDate == "0") {
            defaultDate = moment().tz(timezone);
        } else {
            defaultDate = moment(defaultDate).tz(timezone);
        }

        // initializing all the displayed and the hidden datepickers
        jQuery("." + className).each(function () {

            // finding all the alternative field
            var altName = jQuery(this).attr("name");
            if (typeof(altName) != "undefined") {
                var alternativeField = "input[name=alternativeDate" + altName[0].toUpperCase() + altName.slice(1) + "]";
                // $(this).val(), if exists, is a GMT YYYY-MM-DDTHH:mm:ss timestamp, for example with PHP : gmdate("Y-m-d\TH:i:s")
                var value = $(this) && $(this).val() ? moment($(this).val()).tz(timezone) : defaultDate;
                jQuery(this).datepicker({
                    //formatting the hidden fields using a specific format
                    altField: alternativeField,
                    altFormat: altFormat
                //datepicker date format elements : d, m, y, yy - moment date format elements :  D, M, YY, YYYY
                }).datepicker("setDate", value.format($(this).datepicker("option", "dateFormat").toUpperCase().replace(/Y/g,'YY')));
            } else {
                alert("Fatal : attribute name not found for the class " + className);
                jQuery(this).datepicker();
            }
        });
    } else {
        // setting the displayed and hidden fields with a timestamp value sent from the backend
        // used for MBI pages
        var alternativeField = "input[name=alternativeDate" + idName + "]";
        var dateToSet = new Date(timestampToSet);
        jQuery("#" + idName).datepicker({
            altField: alternativeField,
            altFormat: altFormat
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
    let userLanguage = localStorage.getItem('locale') ? localStorage.getItem('locale').substring(0, 5) : "en_US";

    if ("en_US" != userLanguage) {
        //calling the webservice to check if the file exists
        $.ajax({
            url: './api/internal.php?object=centreon_datepicker_i18n&action=datepickerLibrary',
            type: 'GET',
            async: false,
            data: {data: userLanguage},
            success: function(data) {
                if (null !== data && data.length > 15) {
                    //A localized library was found, loading it.
                    jQuery('<script>')
                        .attr('src', './include/common/javascript/datepicker/' + data)
                        .appendTo('body');
                } else {
                    console.log('WARNING : datepicker localized library not found for : "' + userLanguage + '"');
                    console.log('Initializing the datepicker for "en_US"');
                }
            }
        });
    }
}

function turnOnEvents() {
    $(".datepicker").first().on('change', function (e) {
        updateEndTime();
    });
    $(".timepicker").first().on('change', function (e) {
        updateEndTime();
    });
    $(".datepicker").last().on('change', function (e) {
        updateStartTime();
    });
    $(".timepicker").last().on('change', function (e) {
        updateStartTime();
    });
}

function turnOffEvents() {
    $(".datepicker").off('change');
    $(".timepicker").off('change');
}

function updateEndTime() {
    var start = moment($('[name="alternativeDateStart"]').val() + ' ' +  $(".timepicker").first().val(), "MM/DD/YYYY HH:mm");
    var end = moment($('[name="alternativeDateEnd"]').val() + ' ' +  $(".timepicker").last().val(), "MM/DD/YYYY HH:mm");

    if (start.isAfter(end) || start.isSame(end)) {
        turnOffEvents();
        start.add($('#duration').val(), $('#duration_scale').val());
        $(".datepicker").last().datepicker("setDate", start.format($(".datepicker").last().datepicker("option", "dateFormat").toUpperCase().replace(/Y/g,'YY')));
        $(".timepicker").last().timepicker("setTime", start.format("HH:mm"));
        turnOnEvents();
    }
}

function updateStartTime() {
    var start = moment($('[name="alternativeDateStart"]').val() + ' ' +  $(".timepicker").first().val(), "MM/DD/YYYY HH:mm");
    var end = moment($('[name="alternativeDateEnd"]').val() + ' ' +  $(".timepicker").last().val(), "MM/DD/YYYY HH:mm");

    if (start.isAfter(end) || start.isSame(end)) {
        turnOffEvents();
        end.subtract($('#duration').val(), $('#duration_scale').val());
        $(".datepicker").first().datepicker("setDate", end.format($(".datepicker").first().datepicker("option", "dateFormat").toUpperCase().replace(/Y/g,'YY')));
        $(".timepicker").first().timepicker("setTime", end.format("HH:mm"));
        turnOnEvents();
    }
}
