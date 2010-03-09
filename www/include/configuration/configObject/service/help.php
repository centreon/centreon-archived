<?php
$help = array();
$help["mc_update"] = dgettext("help", "Choose the update mode for the below field: incremental adds the selected values, replacement overwrites the original values.");

/*
 * Service Configuration
 */
$help["service_alias"] = dgettext("help", "Name used for service in auto-deploy by template.");
$help["service_description"] = dgettext("help", "Define the description of the service. It may contain spaces, dashes, and colons (semicolons, apostrophes, and quotation marks should be avoided). Services must have a unique description on a per host basis.");
$help["use"] = dgettext("help", "This is where you specify the name of the template object that you want to inherit properties/variables from. Inherited properties doesn't need to be specified again. \"Local\" object variables always take precedence over variables defined in the template object. Objects can inherit properties/variables from multiple levels of template objects. When defining multiple sources, the first template specified takes precedence over the later one, in the case where a property is defined in both.");

$help["is_volatile"] = dgettext("help", "This directive is used to denote whether the service is \"volatile\". A volatile service resets its state to OK with every query. Services are normally not volatile.");
$help["check_period"] = dgettext("help", "This directive is used to specify the time period during which active checks of this service can be made.");
$help["check_command"] = dgettext("help", "This directive is used to specify the command that Nagios will run in order to check the status of the service.");
$help["check_command_args"] = dgettext("help", "Specify the parameters for the selected check command here. The format is: !ARG1!ARG2!...ARGn");

$help["max_check_attempts"] = dgettext("help", "This directive is used to define the number of times that Nagios will retry the service check command if it returns any state other than an OK state. Setting this value to 1 will cause Nagios to generate an alert without retrying the service check again.");
$help["check_interval"] = dgettext("help", "Define the number of \"time units\" between regularly scheduled checks of the host. With the default time unit of 60s, this number will mean multiples of 1 minute. \"Regular\" checks are those that occur when the service is in an OK state or when the service is in a non-OK state, but has already been rechecked max_check_attempts number of times.");
$help["retry_interval"] = dgettext("help", "Define the number of \"time units\" to wait before scheduling a re-check for this service after a non-OK state was detected. With the default time unit of 60s, this number will mean multiples of 1 minute. Once the service has been retried max_check_attempts times without a change in its status, it will revert to being scheduled at its \"normal\" check interval rate.");

$help["active_checks_enabled"] = dgettext("help", "Enable or disable active checks (either regularly scheduled or on-demand) of this host here. By default active host checks are enabled.");
$help["passive_checks_enabled"] = dgettext("help", "Enable or disable passive checks here. When disabled submitted states will be not accepted.");

$help["notifications_enabled"] = dgettext("help", "Specify whether or not notifications for this service are enabled.");
$help["contacts"] = dgettext("help", "This is a list of contacts that should be notified whenever there are problems (or recoveries) with this service. Useful if you want notifications to go to just a few people and don't want to configure contact groups. You must specify at least one contact or contact group in each service definition (or indirectly through its template).");
$help["contact_groups"] = dgettext("help", "This is a list of contact groups that should be notified whenever there are problems (or recoveries) with this service. You must specify at least one contact or contact group in each service definition.");
$help["notification_interval"] = dgettext("help", "Define the number of \"time units\" to wait before re-notifying a contact that this service is still down or unreachable. With the default time unit of 60s, this number will mean multiples of 1 minute. A value of 0 disables re-notififications of contacts about problems for this host - only one problem notification will be sent out.");
$help["notification_period"] = dgettext("help", "Specify the time period during which notifications of events for this service can be sent out to contacts. If a state change occurs during a time which is not covered by the time period, no notifications will be sent out.");
$help["notification_options"] = dgettext("help", "Define the states of the service for which notifications should be sent out. If you specify None as an option, no service notifications will be sent out. If you do not specify any notification options, Nagios will assume that you want notifications to be sent out for all possible states.");
$help["first_notification_delay"] = dgettext("help", "Define the number of \"time units\" to wait before sending out the first problem notification when this service enters a non-OK state. With the default time unit of 60s, this number will mean multiples of 1 minute. If you set this value to 0, Nagios will start sending out notifications immediately.");

/*
 * Relations
 */
$help["host_templates"] = dgettext("help", "Specify one or multiple host templates, that should be linked to this template. A service, that uses this service template, will complete the missing host relation and result in a full service definition.");
$help["host_name"] = dgettext("help", "Specify the host(s) that this service \"runs\" on or is associated with.");
$help["hostgroup_name"] = dgettext("help", "Specify the hostgroup(s) that this service \"runs\" on or is associated with. One or more hostgroup(s) may be used instead of, or in addition to, specifying hosts.");
$help["servicegroups"] = dgettext("help", "This directive is used to identify the short name(s) of the servicegroup(s) that the service belongs to. This directive may be used as an alternative to using the members directive in servicegroup definitions.");
$help["snmptraps"] = dgettext("help", "Specify the relation of known SNMP traps to state changes of this service.");

/*
 * Data processing
 */
$help["obsess_over_service"] = dgettext("help", "This directive determines whether or not checks for the service will be \"obsessed\" over. When enabled the obsess over service command will be executed after every check of this service.");
$help["check_freshness"] = dgettext("help", "This directive is used to determine whether or not freshness checks are enabled for this service. When enabled Nagios will trigger an active check when last passive result is older than the value defined in the threshold. By default freshness checks are enabled.");
$help["freshness_threshold"] = dgettext("help", "This directive is used to specify the freshness threshold (in seconds) for this service. If you set this directive to a value of 0, Nagios will determine a freshness threshold to use automatically.");

$help["flap_detection_enabled"] = dgettext("help", "This directive is used to determine whether or not flap detection is enabled for this service. A service is marked as flapping when frequent state changes occur.");
$help["low_flap_threshold"] = dgettext("help", "Specify the low state change threshold used in flap detection for this service. A service with a state change rate below this threshold is marked normal. By setting the value to 0, the program-wide value will be used.");
$help["high_flap_threshold"] = dgettext("help", "Specify the high state change threshold used in flap detection for this service. A service with a state change rate above or equal to this threshold is marked as flapping. By setting the value to 0, the program-wide value will be used.");

$help["process_perf_data"] = dgettext("help", "Specify whether or not the processing of performance data is enabled for this service.");

$help["retain_status_information"] = dgettext("help", "Specify whether or not status-related information about the service is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");
$help["retain_nonstatus_information"] = dgettext("help", "Specify whether or not non-status information about the service is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");
$help["stalking_options"] = dgettext("help", "Define which service states \"stalking\" is enabled for.");

$help["event_handler_enabled"] = dgettext("help", "This directive is used to determine whether or not the event handler for this service is enabled.");
$help["event_handler"] = dgettext("help", "This directive is used to specify the command that should be run whenever a change in the state of the service is detected (i.e. whenever it goes down or recovers).");
$help["event_handler_args"] = dgettext("help", "This parameters are passed to the event handler commands in the same way check command parameters are handled. The format is: !ARG1!ARG2!...ARGn");

/*
 * Service extended infos
 */
$help["graph_template"] = dgettext("help", "The optional definition of a graph template will be used as default graph template for this service.");
$help["categories"] = dgettext("help", "");

$help["notes_url"] = dgettext("help", "Define an optional URL that can be used to provide more information about the host. Any valid URL can be used. This can be very useful if you want to make detailed information on the host, emergency contact methods, etc. available to other support staff.");
$help["notes"] = dgettext("help", "Define an optional string of notes pertaining to the host.");
$help["action_url"] = dgettext("help", "Define an optional URL that can be used to provide more actions to be performed on the host. You will see the link to the action URL in the host details.");
$help["icon_image"] = dgettext("help", "Define the image that should be associated with this host here. This image will be displayed in the various places. The image will look best if it is 40x40 pixels in size.");
$help["icon_image_alt"] = dgettext("help", "Define an optional string that is used in the alternative description of the icon image.");


/*
 * Macros
 */
$help["macro"] = dgettext("help", "Macros are used as object-specific variables/properties, which can be referenced in commands and extended infos. A Macro named TECHCONTACT can be referenced as \$_SERVICETECHCONTACT\$.");

/*
 * unsupported in centreon
 */
$help["display_name"] = dgettext("help", "This directive is used to define an alternate name that should be displayed in the web interface for this service. If not specified, this defaults to the value you specify as service description.");
$help["flap_detection_options"] = dgettext("help", "This directive is used to determine what service states the flap detection logic will use for this service.");
$help["initial_state"] = dgettext("help", "By default Nagios will assume that all services are in OK states when it starts. You can override the initial state for a service by using this directive.");

?>

