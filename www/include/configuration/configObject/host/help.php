<?php
$help = array();
$help["mc_update"] = dgettext(
    "help",
    "Choose the update mode for the below field: incremental adds the selected values,"
    . " replacement overwrites the original values."
);

/*
 * Host Configuration
 */
$help["host_name"] = dgettext(
    "help",
    "The host name defined here is used in host group and service definitions to reference this particular host."
);
$help["alias"] = dgettext("help", "The alias is used to define a longer name or description for the host.");
$help["address"] = dgettext(
    "help",
    "Network address. Can be either IP or FQDN. If no DNS service is available, using a FQDN could raise false alarms."
);
$help["snmp_options"] = dgettext(
    "help",
    "The SNMP community and version specified here can be referenced in the check command by using"
    . " the \$_HOSTSNMPCOMMUNITY\$ and \$_HOSTSNMPVERSION\$ macros."
);
$help["poller"] = dgettext(
    "help",
    "Central, remote server or poller that will monitor the host."
);
$help["host_location"] = dgettext(
    "help",
    "Timezone corresponding to the host's location. This will impact how the check period is applied to it."
);

$help["use"] = dgettext(
    "help",
    "This is where you specify the template that you want to inherit properties/variables from."
    . " Inherited properties doesn't need to be specified again. \"Local\" object variables always take"
    . " precedence over variables defined in the template object. Objects can inherit properties/variables"
    . " from multiple levels of template objects. When defining multiple sources, the first template specified"
    . " takes precedence over the later one, in the case where a property is defined in both."
);
$help["create_linked_services"] = dgettext(
    "help",
    "By enabling this option, the services linked to the template will be created independent of"
    . " the template and attached to this service."
);

$help["check_period"] = dgettext(
    "help",
    "This directive is used to specify the time period during which active checks of this host can be executed."
);
$help["check_command"] = dgettext(
    "help",
    "Specify the command that should be used to check if the host is up or down. Typically,"
    . " this command would try and ping the host to see if it is \"alive\". On a non-OK state monitoring engine"
    . " will assume the host is down. A blank argument disables active checks for the host status and Monitoring"
    . " Engine will always assume the host is up. This is useful if you are monitoring printers or other"
    . " devices that are frequently turned off."
);
$help["check_command_args"] = dgettext(
    "help",
    "Specify the parameters for the selected check command here. The format is: !ARG1!ARG2!...ARGn"
);
$help["max_check_attempts"] = dgettext(
    "help",
    "Define the number of times that monitoring engine will retry the host check command if it returns any"
    . " non-OK state. Setting this value to 1 will cause monitoring engine to generate an alert immediately."
    . " Note: If you do not want to check the status of the host, you must still set this to a minimum value of 1."
    . " To bypass the host check, just leave the check command option blank."
);
$help["check_interval"] = dgettext(
    "help",
    "Define the number of \"time units\" between regularly scheduled checks of the host. With the default time"
    . " unit of 60s, this number will mean multiples of 1 minute."
);
$help["retry_interval"] = dgettext(
    "help",
    "Define the number of \"time units\" to wait before scheduling a re-check for this host after a"
    . " non-UP state was detected. With the default time unit of 60s, this number will mean multiples of 1 minute."
    . " Once the host has been retried max_check_attempts times without a change in its status, it will revert"
    . " to being scheduled at its \"normal\" check interval rate."
);
$help["active_checks_enabled"] = dgettext(
    "help",
    "Enable or disable active checks (either regularly scheduled or on-demand) of this host here."
    . " By default active host checks are enabled."
);
$help["passive_checks_enabled"] = dgettext(
    "help",
    "Enable or disable passive checks here. When disabled submitted states will be not accepted."
);

$help["notifications_enabled"] = dgettext("help", "Specify whether or not notifications for this host are enabled.");
$help["contact_additive_inheritance"] = dgettext(
    "help",
    "When enabled, the contact definition will not override the definitions on template levels,"
    . " it will be appended instead."
);
$help["cg_additive_inheritance"] = dgettext(
    "help",
    "When enabled, the contactgroup definition will not override the definitions on template levels,"
    . " it will be appended instead."
);
$help["contacts"] = dgettext(
    "help",
    "This is a list of contacts that should be notified whenever there are problems (or recoveries) with this host."
    . " Useful if you want notifications to go to just a few people and don't want to configure contact groups."
    . " You must specify at least one contact or contact group in each host definition"
    . " (or indirectly through its template)."
);
$help["cg_additive_inheritance"] = dgettext(
    "help",
    "When enabled, the contact group definition will not override the definitions on template levels,"
    . " it will be appended instead."
);
$help["contact_groups"] = dgettext(
    "help",
    "This is a list of contact groups that should be notified whenever there are problems (or recoveries) with"
    . " this host. You must specify at least one contact or contact group in each host definition."
);

$help["notification_interval"] = dgettext(
    "help",
    "Define the number of \"time units\" to wait before re-notifying a contact that this host is still down or"
    . " unreachable. With the default time unit of 60s, this number will mean multiples of 1 minute. A value of"
    . " 0 disables re-notifications of contacts about problems for this host - only one"
    . " problem notification will be sent out."
);
$help["notification_period"] = dgettext(
    "help",
    "Specify the time period during which notifications of events for this host can be sent out to contacts."
    . " If a state change occurs during a time which is not covered by the time period,"
    . " no notifications will be sent out."
);
$help["notification_options"] = dgettext(
    "help",
    "Define the states of the host for which notifications should be sent out. If you specify None as an option,"
    . " no host notifications will be sent out. If you do not specify any notification options, monitoring engine"
    . " will assume that you want notifications to be sent out for all possible states."
);
$help["first_notification_delay"] = dgettext(
    "help",
    "Define the number of \"time units\" to wait before sending out the first problem notification when this " .
    "host enters a non-UP state. " .
    "With the default time unit of 60s, this number will mean multiples of 1 minute. " .
    "If you set this value to 0, monitoring engine will start sending out notifications immediately."
);
$help["recovery_notification_delay"] = dgettext(
    "help",
    "Define the number of \"time units\" to wait before sending out the recovery notification when this " .
    "host enters an UP state. " .
    "With the default time unit of 60s, this number will mean multiples of 1 minute. " .
    "If you set this value to 0, monitoring engine will start sending out notifications immediately."
);

/*
 * Relations
 */
$help["hostgroups"] = dgettext(
    "help",
    "Host groups linked to the host."
);
$help["hostcategories"] = dgettext(
    "help",
    "Host categories linked to the host."
);
$help["parents"] = dgettext(
    "help",
    "Parent hosts are typically routers, switches, firewalls, etc. that lie between the monitoring host and a"
    . " remote hosts. A router, switch, etc. which is closest to the remote host is considered to be that"
    . " host\'s \"parent\". If this host is on the same network segment as the host doing the monitoring"
    . " (without any intermediate routers, etc.) the host is considered to be on the local network and will not have"
    . " a parent host. Leave this value blank if the host does not have a parent host (i.e. it is on the same"
    . " segment as the Monitoring Engine host). The order in which you specify parent hosts has no effect"
    . " on how things are monitored. Note that notification features based on host inheritance will not work if"
    . " the hosts are not monitored by the same poller."
);
$help["child_hosts"] = dgettext(
    "help",
    "Instead of specifying the parent hosts in the child's parent definition, it's possible to do it the other way"
    . " round and specify all child hosts in the parent's definition."
);
$help["service_templates"] = dgettext(
    "help",
    "Specify one or more templates of services that should be linked to this host's template."
    . " A host deployed from this host's template will benefit all services themselves based from services'"
    . " templates previously linked."
);

/*
 * Data Processing
 */

$help["obsess_over_host"] = dgettext(
    "help",
    "This directive determines whether or not checks for this host will be \"obsessed\" over."
    . " When enabled the obsess over host command will be executed after every check of this host."
);
$help["check_freshness"] = dgettext(
    "help",
    "This directive is used to determine whether or not freshness checks are enabled for this host."
    . " When enabled monitoring engine will trigger an active check when last passive result is older than the"
    . " value defined in the threshold. By default freshness checks are enabled."
);
$help["freshness_threshold"] = dgettext(
    "help",
    "Specify the freshness threshold (in seconds) for this host. If you set this directive to a value of 0,"
    . " monitoring engine will determine a freshness threshold to use automatically."
);

$help["flap_detection_enabled"] = dgettext(
    "help",
    "This directive is used to determine whether or not flap detection is enabled for this host."
);
$help["low_flap_threshold"] = dgettext(
    "help",
    "This directive is used to specify the low state change threshold used in flap detection for this host."
    . " If you set this directive to a value of 0, the program-wide value will be used."
);
$help["high_flap_threshold"] = dgettext(
    "help",
    "This directive is used to specify the high state change threshold used in flap detection for this host."
    . " If you set this directive to a value of 0, the program-wide value will be used."
);

$help["process_perf_data"] = dgettext(
    "help",
    "This directive is used to determine whether or not the processing of performance data is enabled for this host."
);

$help["retain_status_information"] = dgettext(
    "help",
    "This directive is used to determine whether or not status-related information about the host is retained"
    . " across program restarts. This is only useful if you have enabled the global state retention option."
);
$help["retain_nonstatus_information"] = dgettext(
    "help",
    "This directive is used to determine whether or not non-status information about the host is"
    . " retained across program restarts. This is only useful if you have enabled state retention using the"
    . " retain_state_information directive."
);
$help["stalking_options"] = dgettext(
    "help",
    "This directive determines which host states \"stalking\" is enabled for."
);

$help["event_handler_enabled"] = dgettext(
    "help",
    "This directive is used to determine whether or not the event handler for this host is enabled."
);
$help["event_handler"] = dgettext(
    "help",
    "The event handler command is triggered whenever a change in the state of the host is detected,"
    . " i.e. whenever it goes down or recovers."
);
$help["event_handler_args"] = dgettext(
    "help",
    "This parameters are passed to the event handler commands in the same way check command parameters are handled."
    . " The format is: !ARG1!ARG2!...ARGn"
);

$help["host_acknowledgement_timeout"] = dgettext(
    "help",
    "Specify a duration of acknowledgement for this host or host depending to this template."
    . " If you leave it blank, no timeout will be set."
);

/*
 * Host extended infos
 */

$help["notes_url"] = dgettext(
    "help",
    "Clickable URL displayed in the Notes column of the Resources Status page."
);
$help["notes"] = dgettext(
    "help",
    "Information note displayed as a tooltip in the Notes column of the Resources Status page."
);
$help["action_url"] = dgettext(
    "help",
    "Define an optional URL that can be used to provide more actions to be performed on the host."
    . " You will see the link to the action URL in the host details."
);
$help["icon_image"] = dgettext(
    "help",
    "Define the image that should be associated with this host here."
    . " This image will be displayed in the various places. The image will look best if it is 40x40 pixels in size."
);
$help["icon_image_alt"] = dgettext(
    "help",
    "Define an optional string that is used in the alternative description of the icon image."
);
$help["statusmap_image"] = dgettext(
    "help",
    "Define an image that should be associated with this host in the statusmap CGI in monitoring engine."
    . " You can choose a JPEG, PNG, and GIF image. The GD2 image format is preferred, as other image formats"
    . " must be converted first when the statusmap image is generated. The image will look best if it "
    . "is 40x40 pixels in size."
);
$help["geo_coords"] = dgettext(
    "help",
    "Geographic coordinates to allow Centreon MAP to plot the resource on a geographic view. "
    . "Format: Latitude,Longitude. For example, Paris' coordinates are 48.51,2.20"
);
$help["2d_coords"] = dgettext(
    "help",
    "Define the coordinates to use when drawing the host in the statusmap CGI."
    . " Coordinates should be given in positive integers, as they correspond to physical pixels in the generated image."
    . " The origin for drawing (0,0) is in the upper left hand corner of the image and extends in the positive"
    . " x direction (to the right) along the top of the image and in the positive y direction (down) along"
    . " the left hand side of the image. For reference, the size of the icons drawn is usually about 40x40 pixels"
    . " (text takes a little extra space). The coordinates you specify here are for the upper left hand corner of"
    . " the host icon that is drawn. Note: Don't worry about what the maximum x and y coordinates that you can use are."
    . " The CGI will automatically calculate the maximum dimensions of the image it creates based on the largest"
    . " x and y coordinates you specify."
);
$help["3d_coords"] = dgettext(
    "help",
    "Define the coordinates to use when drawing the host in the statuswrl CGI."
    . " Coordinates can be positive or negative real numbers. The origin for drawing is (0.0,0.0,0.0)."
    . " For reference, the size of the host cubes drawn is 0.5 units on each side (text takes a little more space)."
    . " The coordinates you specify here are used as the center of the host cube."
);
$help['criticality_id'] = dgettext(
    "help",
    "Host severity level. Can be used to sort alerts in the monitoring menus, including the Resources Status page."
);
$help['acl_groups'] = dgettext(
    "help",
    "This is required so that you can access your host after creation."
    . " Some selected resource groups may contain filter, thus still preventing you from seeing the new host."
    . " In this case, make sure to link your Host to a Host Category."
);

/*
 * Macros
 */
$help["macro"] = dgettext(
    "help",
    "Macros are used as object-specific variables/properties, which can be referenced in commands and extended infos."
    . " Example: a macro named MACADDRESS can be referenced as \$_HOSTMACADDRESS\$."
);

/*
 * unsupported in centreon
 */
$help["display_name"] = dgettext(
    "help",
    "This directive is used to define an alternate name that should be displayed in the web interface for this host."
    . " If not specified, this defaults to the value you specify as host name."
);
$help["flap_detection_options"] = dgettext(
    "help",
    "This directive is used to determine what host states the flap detection logic will use for this host."
);
$help["initial_state"] = dgettext(
    "help",
    "By default monitoring engine will assume that all hosts are in UP states when it starts."
    . " You can override the initial state for a host by using this directive."
);
$help['host_activate'] = dgettext(
    "help",
    "This setting determines whether the host and its services must be monitored or not."
);
