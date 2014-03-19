(function($) {
    var timeout = 0;

    function get_new_messages(element, settings) {
        if (timeout) {
            clearTimeout(timeout);
        }
        jQuery.ajax({
            url: "./include/monitoring/status/Notifications/broker/notifications.php",
            data: {
                sid: settings.sid,
                refresh_rate: settings.refresh_rate
            }
        }).done(function(xml_content) {
            $(xml_content).find('message').each(function() {
                if ($(this).attr('output')) {
                    var output = $(this).attr('output');
                    var css_class = $(this).attr('class');
                    noty({
                        layout: 'bottomRight',
                        text: output,
                        type: css_class,
                        timeout: 10000
                    });
                }
                if ($(this).attr('sound')) {
                    var snd = new buzz.sound("sounds/"+$(this).attr('sound'), {
                        formats: [ "ogg", "mp3" ]
                    });
                    snd.play();
                }
            });
        });
        timeout = setTimeout(function() { get_new_messages(element, settings); }, settings.refresh_rate);
    }

    $.fn.centreon_notify_stop = function() {
        jQuery.ajax({
            url: "./include/monitoring/status/Notifications/notifications_action.php",
            data: {
                action: "stop"
            }
        }).done(function() {
            jQuery("#sound_status").attr("src", "./img/icones/24x24/speaker_off.png");
            jQuery("#sound_status").attr("onClick", "jQuery().centreon_notify_start();");
        });
    }

    $.fn.centreon_notify_start = function() {
        jQuery.ajax({
            url: "./include/monitoring/status/Notifications/notifications_action.php",
            data: {
                action: "start"
            }
        }).done(function() {
            jQuery("#sound_status").attr("src", "./img/icones/24x24/speaker_on.png");
            jQuery("#sound_status").attr("onClick", "jQuery().centreon_notify_stop();");
        });
    }

    $.fn.centreon_notify = function(options) {
        var $this = $(this);

        var settings = $.extend({
            sid: "",
            refresh_rate: 15000
        }, options);
        get_new_messages(this, settings);
    };
}(jQuery));
