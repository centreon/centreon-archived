$(document).on('centreon.refresh_status', function(e) {

    jQuery.fn.extend({
        topbarTooltip: function (tooltipObjects) {
            $(this).on('show.bs.dropdown', function(e) {
                $.each(tooltipObjects, function(subObjectType, subObjectList) {
                    $issuesPopover = $(this).find('issuesPopover');
                    $issuesPopover.empty();
                    
                    $currentSubObjectType = $('<ul></ul>').append(
                        $('<li></li>').append($('<h5></h5>').text(subObjectType)).append(
                            $('<p></p>').append($('<span></span>').addClass('danger').text('0'))
                        )
                    );
                    $.each(subObjectList, function(subObjectDatas) {
                        $currentSubObjectType.append(
                            $('<li></li>').append($('<h6></h6>').append(
                                $('<span></span>').addClass('icon-host').addClass('ico-16').text(subObjectDatas.name)).append(
                                    $('<p></p>').append($('<span></span>').addClass('duration').text(subObjectDatas.since))
                                )
                            )
                        );
                    });
                    
                    $issuesPopover.append($currentSubObjectType);
                });
            });
        }
    });


    /* Critical Issues */
    if (statusData.issues != undefined && statusData.issues > 0) {
        $('.top-counter-critical').find('div.indices').empty()
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.issues.critical.nb_hosts).addClass('danger'))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(statusData.issues.critical.nb_services).addClass('danger'))
            .append($('<br></br>'))
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.issues.critical.total_impacts));
            
        $('.top-counter-critical').find('issuesPopover').topbarTooltip();
    }
    
    /* Warning Issues */
    if (statusData.issues != undefined && statusData.issues > 0) {
        $('.top-counter-warning').find('div.indices').empty()
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.issues.warning.nb_hosts).addClass('danger'))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(statusData.issues.warning.nb_services).addClass('danger'))
            .append($('<br></br>'))
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(statusData.issues.warning.total_impacts));
            
        $('.top-counter-warning').find('issuesPopover').topbarTooltip();
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
            
        $('.top-counter-unknown').find('issuesPopover').topbarTooltip(statusData.states.configurationObjects);
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
