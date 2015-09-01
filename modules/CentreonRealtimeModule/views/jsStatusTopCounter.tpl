$(document).on('centreon.refresh_status', function(e) {

    jQuery.fn.extend({
        topbarTooltip: function (tooltipObjects) {


            $issuesPopover = $(this).empty();
            $.each(tooltipObjects, function(subObjectType, subObjectList) {

                $totalObjects = eval('statusData.total'+subObjectType);
                $currentSubObjectType = $('<ul class="listObject"></ul>');

                $currentSubObjectType.append($('<li></li>')
                    .append($('<h5></h5>').text(subObjectType)
                    .append($('<div class="right"></div>')
                    .append($('<span></span>').addClass('danger').text(subObjectList.length))
                    .append($('<span></span>').text('/'+($totalObjects))))
                ));

                $.each(subObjectList, function(subObjectKey, subObjectDatas) {
                
                    objectText = $('<span class="ico-16"></span>');
                    if (subObjectDatas.icon.type == 'class') {
                        objectText.addClass(subObjectDatas.icon.value);
                    }

                    $currentSubObjectType.append($('<li class="listDetails"></li>')
                            .append($('<h6></h6>')
                            .append(objectText)
                            .append($('<a href="'+subObjectDatas.url+'" alt="'+subObjectDatas.name+'"></a>')
                            .append(subObjectDatas.name)
                            .append($('<span></span>').addClass('duration').text(subObjectDatas.since))))
                        );
                });

                $issuesPopover.append($currentSubObjectType);
            });

            return $issuesPopover.html();

                title='';
             $('.bt-critical').popover({
                content: $issuesPopover,
                title: title,
                html: true,
                placement: "bottom",
                template: '<div class="popover"><div class="popover-content"></div></div>'
                //trigger: 'focus'

                });
            
        }
    });

    /* Critical Issues */
    if (statusData.issues.critical != undefined) {
        hostsInCritical = 0;
        hostsInCriticalClass = 'success';
        if (statusData.issues.critical.nb_hosts != undefined) {
            hostsInCritical = statusData.issues.critical.nb_hosts;
            if (statusData.issues.critical.nb_hosts > 0) {
                hostsInCriticalClass = 'danger';
            }
        }
        
        servicesInCritical = 0;
        servicesInCriticalClass = 'success';
        if (statusData.issues.critical.nb_services != undefined) {
            servicesInCritical = statusData.issues.critical.nb_services;
            if (statusData.issues.critical.nb_services > 0) {
                servicesInCriticalClass = 'danger';
            }
        }
        
        criticalImpacts = 0;
        criticalImpactsClass = 'success';
        if (statusData.issues.critical.total_impacts != undefined) {
            criticalImpacts = statusData.issues.critical.total_impacts;
            if (statusData.issues.critical.total_impacts > 0) {
                criticalImpactsClass = 'danger';
            }
        }
        
        $('.top-counter-critical').find('div.indices').empty()
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(hostsInCritical).addClass(hostsInCriticalClass))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(servicesInCritical).addClass(servicesInCriticalClass))
            .append($('<br></br>'))
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(criticalImpacts).addClass(criticalImpactsClass));
            
        $('.top-counter-critical').find('.issuesPopover').topbarTooltip(statusData.issues.critical.objects);
    }

    
    /* Warning Issues */
    if (statusData.issues.warning) {
        hostsInWarning = 0;
        hostsInWarningClass = 'success';
        if (statusData.issues.warning.nb_hosts != undefined) {
            hostsInWarning = statusData.issues.warning.nb_hosts;
            if (statusData.issues.warning.nb_hosts > 0) {
                hostsInWarningClass = 'warning';
            }
        }
        
        servicesInWarning = 0;
        servicesInWarningClass = 'success';
        if (statusData.issues.warning.nb_services != undefined) {
            servicesInWarning = statusData.issues.warning.nb_services;
            if (statusData.issues.warning.nb_services > 0) {
                servicesInWarningClass = 'warning';
            }
        }
        
        warningImpacts = 0;
        warningImpactsClass = 'success';
        if (statusData.issues.warning.total_impacts != undefined) {
            warningImpacts = statusData.issues.warning.total_impacts;
            if (statusData.issues.warning.total_impacts > 0) {
                warningImpactsClass = 'warning';
            }
        }


        $('.top-counter-warning').find('div.indices').empty()
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(hostsInWarning).addClass(hostsInWarningClass))
            .append($('<span></span>').addClass('icon-fill-service ico-16'))
            .append($('<span></span>').text(servicesInWarning).addClass(servicesInWarningClass))
            .append($('<br></br>'))
            .append($('<span></span>').addClass('icon-fill-host ico-16'))
            .append($('<span></span>').text(warningImpacts).addClass(warningImpactsClass));
            
        $('.top-counter-warning').find('.issuesPopover').topbarTooltip(statusData.issues.warning.objects);
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
            
        //$('.top-counter-unknown').find('issuesPopover').topbarTooltip(statusData.states.configurationObjects);
    }
    
    /* Pollers data */
    if (statusData.states.pollers != undefined) {
        /* Data for Poller */
        $('.top-counter-poller').find('div.indices').empty()
            .append($('<span></span>').text(statusData.states.pollers.stopped.nb_pollers))
            .append($('<br></br>'))
            .append($('<span></span>').text(statusData.states.pollers.unreachable.nb_pollers));
            
        //$('.top-counter-unknown').find('issuesPopover').topbarTooltip(statusData.states.pollers.stopped.objects);
    }

       $(document).ready(function() {

                        var content = $('<div class="listPopover">').topbarTooltip(statusData.issues.critical.objects);

                        var title = "title";

                             $('.bt-critical').popover({
                                container : '.GlobalNavbar',
                                content: content,
                                title: title,
                                html: true,
                                placement: "bottom",
                                template: '<div class="popover"><div class="popover-content"></div></div>'
                                //trigger: 'focus'

                                });
                   });
});
