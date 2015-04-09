var sDefaultFormatDate = "YYYY-MM-DD HH:mm:ss";

function displayDate()
{
    var aFieldTime = $.find('[data-time]');
    
    $.each(aFieldTime, function(idx, el) {
        var unixtime = $(el).data('time');
        if (unixtime != '') {
            if (sessionStorage.length > 0 &&  sessionStorage.getItem("sTimezone") != 'undefined' && sessionStorage.getItem("sTimezone") != '') {
                var sDate = moment.unix(unixtime);
                var localDate = sDate.format(sDefaultFormatDate);
                var newDate =  sDate.tz(sessionStorage.getItem("sTimezone")).format(sDefaultFormatDate);
                $(el).text(newDate+" ("+localDate+")");
            } else {
                var sDate = moment.unix(unixtime);
                var localDate = sDate.format(sDefaultFormatDate);
                $(el).text(localDate);
            }
        }
    });
       
}
/**
 * 
 * @param string sTimezone
 */
function changeTimezone(sTimezone)
{
    if (sTimezone == '') {
        sessionStorage.clear();
    } else {
        sessionStorage.setItem("sTimezone", sTimezone);
    }
    displayDate();
}