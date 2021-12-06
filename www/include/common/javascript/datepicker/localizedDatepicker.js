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
    altFormat = altFormat || "mm/dd/yy";
    defaultDate = defaultDate || "0";

    setUserFormat();

    let timezone = localStorage.getItem('realTimezone') || moment.tz.guess();

    if (typeof(idName) == "undefined" || typeof(timestampToSet) == "undefined") {
        // Generate default date in proper timezone.
        if (defaultDate == "0") {
            defaultDate = moment().tz(timezone);
        } else {
            defaultDate = moment(defaultDate).tz(timezone);
        }

        // Initializing all the displayed and the hidden datepickers.
        jQuery("." + className).each(function () {

            // Finding all the alternative field.
            var altName = jQuery(this).attr("name");
            if (typeof(altName) != "undefined") {
                var alternativeField = "input[name=alternativeDate" + altName[0].toUpperCase() + altName.slice(1) + "]";
                var value = defaultDate;
                // Avoid to loose chosen localized values on refresh.
                if ($(alternativeField) && $(alternativeField).val()) {
                    // alternativeField value has a MM/DD/YYYY format (the engine supported format).
                    value = moment($(alternativeField).val(), "MM/DD//YYYY");
                } else if ($(this) && $(this).val()) {
                    // $(this).val(), if exists, is a GMT YYYY-MM-DDTHH:mm:ss timestamp.
                    // For example with PHP : gmdate("Y-m-d\TH:i:s").
                    value = moment($(this).val()).tz(timezone);
                }
                jQuery(this).datepicker({
                    // Formatting the hidden fields using a specific format.
                    altField: alternativeField,
                    altFormat: altFormat
                    // Datepicker date format elements : d, m, y, yy - moment date format elements :  D, M, YY, YYYY.
                }).datepicker(
                    "setDate",
                    value.format($(this).datepicker("option", "dateFormat").toUpperCase().replace(/Y/g,'YY'))
                );
            } else {
                alert("Fatal : attribute name not found for the class " + className);
                jQuery(this).datepicker();
            }
        });
    } else {
        // Setting the displayed and hidden fields with a timestamp value sent from the backend.
        // Used for MBI pages.
        var alternativeField = "input[name=alternativeDate" + idName + "]";
        var dateToSet = new Date(timestampToSet);
        jQuery("#" + idName).datepicker({
            altField: alternativeField,
            altFormat: altFormat
        }).datepicker('setDate', dateToSet);
    }
}

/**
 * Getting the user's localization, loading the corresponding library and setting the regional settings.
 */
function setUserFormat() {
    // Getting the local storage attribute.
    const userLanguage = localStorage.getItem('locale') ? localStorage.getItem('locale').substring(0, 5) : "en_US";

    if ("en_US" != userLanguage) {
        // Calling the webservice to check if the file exists.
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

/**
 * Turn the event On and take in account the modified values.
 */
function turnOnEvents() {
    // Start value of datepicker and timepicker selector.
    $(".datepicker").first().on('change', function (e) {
        updateDateAndTime();
    });
    $(".timepicker").first().on('change', function (e) {
        // On change, the first click is taken in account. but the second click will update the end timepicker,
        // as the focus is lost on the first timepicker when updating the end timepicker value.
        // So the timepicker popin needs to be hidden while the end value is modified.
        updateDateAndTime();
        $(this).timepicker();
    });

    // End value of datepicker and timepicker selector
    $(".datepicker").last().on('change', function (e) {
        // Check that the user do not set an end date lesser than the start date.
        checkEndDate();
        // Update the end time according to the chosen duration.
        updateEndTime();
    });
    $(".timepicker").last().on('change', function (e) {
        // In this case, we should not update the start time (could be in the past), but only the end time.
        // Update the end time to a consistent value (with the chosen duration) and alert the user about it.
        checkEndTime();
    });
}

/**
 * Turn the event Off to avoid infinite loop.
 */
function turnOffEvents() {
    $(".datepicker").off('change');
    $(".timepicker").off('change');
}

/**
 * Update the end datepicker and timepicker according to the start values.
 */
function updateDateAndTime() {
    let start = moment($('[name="alternativeDateStart"]').val()
        + ' ' + $(".timepicker").first().val(), "MM/DD/YYYY HH:mm");
    let end = moment($('[name="alternativeDateEnd"]').val()
        + ' ' + $(".timepicker").last().val(), "MM/DD/YYYY HH:mm");

    if (start.isSameOrAfter(end)) {
        turnOffEvents();
        start.add($('#duration').val(), $('#duration_scale').val());
        $(".datepicker").last()
            .datepicker(
                "setDate",
                start.format(
                    $(".datepicker").last().datepicker("option", "dateFormat").toUpperCase().replace(/Y/g, 'YY')
                )
            );
        $(".timepicker").last().timepicker("setTime", start.format("HH:mm"));
        turnOnEvents();
    }
}

/**
 * Used for the end DATEPICKER date.
 * Update the end timepicker according to the start values.
 */
function updateEndTime() {
    let start = moment($('[name="alternativeDateStart"]').val()
        + ' ' + $(".timepicker").first().val(), "MM/DD/YYYY HH:mm");
    let end = moment($('[name="alternativeDateEnd"]').val()
        + ' ' + $(".timepicker").last().val(), "MM/DD/YYYY HH:mm");

    if (start.isSameOrAfter(end)) {
        turnOffEvents();
        start.add($('#duration').val(), $('#duration_scale').val());
        $(".timepicker").last().timepicker("setTime", start.format("HH:mm"));
        turnOnEvents();
    }
}

/**
 * Used for the end DATEPICKER, to avoid an end date value lesser than the start date
 * Updates the end date according to the start values.
 */
function checkEndDate() {
    let start = moment($('[name="alternativeDateStart"]').val()
        + ' ' + $(".timepicker").first().val(), "MM/DD/YYYY HH:mm");
    let end = moment($('[name="alternativeDateEnd"]').val()
        + ' ' + $(".timepicker").last().val(), "MM/DD/YYYY HH:mm");

    if (start.isSameOrAfter(end)) {
        turnOffEvents();
        start.add($('#duration').val(), $('#duration_scale').val());
        $(".datepicker").last()
            .datepicker(
                "setDate",
                start.format(
                    $(".datepicker").last().datepicker("option", "dateFormat").toUpperCase().replace(/Y/g, 'YY')
                )
            );
        turnOnEvents();
    }
}

/**
 * Used for the end TIMEPICKER time.
 * Update the end timepicker according to the start values and the chosen duration,
 * and display a warning to the user.
 */
function checkEndTime() {
    let startTime = $(".timepicker").first().val()
    let start = moment($('[name="alternativeDateStart"]').val()
        + ' ' + startTime, "MM/DD/YYYY HH:mm");

    let endTime = $(".timepicker").last().val();
    let end = moment($('[name="alternativeDateEnd"]').val()
        + ' ' + endTime, "MM/DD/YYYY HH:mm");

    if (start.isSameOrAfter(end)) {
        // Display a warning to the user.
        alert("The downtime end time - " + endTime + ",\nis not consistent with the start time - " + startTime +
            "\n\nThe end time will be modified using the chosen duration");
        // Hidding the popin.
        $(".ui-timepicker.ui-widget").hide();
        updateEndTime();
    }
}
