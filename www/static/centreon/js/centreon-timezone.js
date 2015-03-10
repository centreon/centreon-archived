var sDefaultFormatDate = "YYYY-MM-DD hh:mm:ss";

function displayDate()
{

    var aFieldTime = $.find('[data-time]');
    
    if (aFieldTime.length > 0) {
        $.each(aFieldTime, function(idx, el) {
            var oldDate = $(el).text();
            
            if (sessionStorage.getItem("sTimezone") != undefined) {
                var oldDate = $(el).text();
                var sDate = moment.unix(moment(oldDate).unix());
                var newDate =  sDate.utcOffset(sessionStorage.getItem("sTimezone")).format(sDefaultFormatDate);
                $(el).text(newDate+" ("+oldDate+")");
            } else {
                $(el).text(oldDate+" ("+oldDate+")");
            }
        });
    }
}