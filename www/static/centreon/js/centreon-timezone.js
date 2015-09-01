/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

var sDefaultFormatDate = "YYYY-MM-DD HH:mm:ss";
var sDefaultFormatDateWithoutSecond = "YYYY-MM-DD HH:mm";

function displayDate()
{
    var aFieldTime = $.find('[data-time]');
    
    $.each(aFieldTime, function(idx, el) {
        var unixtime = $(el).data('time');
        if (unixtime != '') {
            var sDate = moment.unix(unixtime);
            var localDate = sDate.format(sDefaultFormatDate);
            var newDate = getDateByTz(unixtime);
            var sNewFormat = '';
            if (sessionStorage.length > 0 &&  sessionStorage.getItem("sTimezone") != 'undefined' && sessionStorage.getItem("sTimezone") != '') {             
               sNewFormat = newDate+" ("+localDate+")";
            } else {
                sNewFormat = localDate;
            }
            
            $(el).text(sNewFormat);
        }
    });
       
}
/**
 * 
 * @param string sTimezone
 */
function changeTimezone(sTimezone)
{
    if (sTimezone == '' || typeof sTimezone === 'undefined') {
        sessionStorage.clear();
    } else {
        sessionStorage.setItem("sTimezone", sTimezone);
    }
    
    displayDate();
    
    if ($("input[name='period']").length > 0 &&  typeof nbGraph !== 'undefined') {
        var startTime, endTime, startTimeNew, endTimeNew, startDateNew, endDateNew, time;
        
        if (sTimezone == '' || typeof sTimezone === 'undefined') {
            endDateNew = moment().format('YYYY-MM-DD HH:mm');
            startDateNew = moment(endDateNew).subtract(24, 'hours').format('YYYY-MM-DD HH:mm');
        } else {
            time = $("input[name='period']").val();

            startTime = moment(time.split(" - ")[0]).format('X');
            endTime = moment(time.split(" - ")[1]).format('X');

            startDateNew = getDateByTz(startTime, sDefaultFormatDateWithoutSecond);
            endDateNew = getDateByTz(endTime, sDefaultFormatDateWithoutSecond);

            startTimeNew = moment(startDateNew).unix();
            endTimeNew = moment(endDateNew).unix();
        }

        $("input[name='period']").val(startDateNew +" - "+endDateNew);

        if (nbGraph > 0) {
           updateChart(startTimeNew, endTimeNew);
        }
    }
   
}
/**
 * 
 * @param {type} unixtime
 * @returns {String}
 */

function getDateByTz(unixtime, sFormat)
{
    var sDateNew = ''; 
    sFormat = typeof sFormat !== 'undefined' ? sFormat : sDefaultFormatDate;
    
    var sDate = moment.unix(unixtime);
    if (sessionStorage.length > 0 &&  sessionStorage.getItem("sTimezone") != 'undefined' && sessionStorage.getItem("sTimezone") != '') {
        sDateNew =  sDate.tz(sessionStorage.getItem("sTimezone")).format(sFormat);
    } else {
        sDateNew = sDate.format(sFormat);
    }
    return sDateNew;
}
