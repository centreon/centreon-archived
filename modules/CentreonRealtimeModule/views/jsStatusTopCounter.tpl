$(document).on('centreon.refresh_status', function(e) {

    /* Issues */
    if (statusData.issues > 0) {
        
    }
    
    /* Pending, Unknown and Unreachable states datas */
    if (statusData.states.configurationObjects != undefined) {
        /* Data for Configuration Object */
        $('.top-counter-unknown').find('div.indices').empty()
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.states.configurationObjects.pending.nb_hosts).addClass('danger'))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(statusData.states.configurationObjects.pending.nb_services).addClass('danger'))
            .append($('<br></br>'))
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.states.configurationObjects.unreachable.nb_hosts))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(statusData.states.configurationObjects.unknown.nb_services));
    }
    
    /* Pollers data */
    if (statusData.states.pollers != undefined) {
        /* Data for Poller */
        $('.top-counter-poller').find('div.indices').empty()
            .append($('<span></span>').text(statusData.states.pollers.stopped.nb_pollers))
            .append($('<br></br>'))
            .append($('<span></span>').text(statusData.states.pollers.unreachable.nb_pollers));
    }
    
    
});
