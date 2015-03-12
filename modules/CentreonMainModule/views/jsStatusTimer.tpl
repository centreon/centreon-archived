$(document).on('centreon.refresh_status', function(e) {
    clockTimestamp = statusData.time.server;
    $('#list_timezone').find('li:not(:last)').remove();
    
    listUserTimezone = statusData.usertimezone;
    $('#list_timezone').prepend("<li class='divider'></li>");
    $.each(listUserTimezone, function(id, element) {
        if (element.text != '' && clockTimestamp != '') {
            var color = '';
            if (element.text == sessionStorage.getItem("sTimezone")) {
                color = '#ADD8E6';
            }
            var sText =  element.text+ " (" + moment.unix(clockTimestamp).tz(element.text).format('HH:mm:ss')+")";
            $('#list_timezone').prepend("<li style='background-color: "+color+"'><a href='#' onclick='changeTimezone(\""+element.text+"\")'>"+sText+"</a><a href='#' class='modalDelete' data-id='"+element.id+"'><i class='fa fa-times-circle'></i></a></li>");
        }
    });
});