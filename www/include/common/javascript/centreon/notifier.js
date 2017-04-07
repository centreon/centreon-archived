/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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

 (function($) {
    var timeout = 0;

    function get_new_messages(element, settings) {
        if (timeout) {
            clearTimeout(timeout);
        }
        jQuery.ajax({
            url: "./include/monitoring/status/Notifications/notifications.php",
            data: {
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
            jQuery("#sound_status").attr("src", "./img/icons/speaker_off.png");
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
            jQuery("#sound_status").attr("src", "./img/icons/speaker_on.png");
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
