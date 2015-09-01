<?php
$help = array();

$help["name"] = dgettext("help", "Enter a short name for the escalation to identify it.");
$help["alias"] = dgettext("help", "The alias is used to define a longer name or description for the escalation.");
$help["first_notification"] = dgettext("help", "Enter a number that identifies the first notification for which this escalation is effective. For instance, if you set this value to 3, this escalation will only be used if the host is down or unreachable long enough for a third notification to go out.");
$help["last_notification"] = dgettext("help", "Enter a number that identifies the last notification for which this escalation is effective. For instance, if you set this value to 5, this escalation will not be used if more than five notifications are sent out for the host. Setting this value to 0 means to keep using this escalation entry forever (no matter how many notifications go out).");
$help["notification_interval"] = dgettext("help", "Select the interval at which notifications should be made while this escalation is valid. If you specify a value of 0 for the interval, Monitoring Engine will send the first notification when this escalation definition is valid, but will then prevent any more problem notifications from being sent out. Notifications are sent out again until the host or service recovers. This is useful if you want to stop having notifications sent out after a certain amount of time. Note: If multiple escalation entries overlap for one or more notification ranges, the smallest notification interval from all escalation entries is used.");
$help["escalation_period"] = dgettext("help", "Select the time period during which this escalation is valid. If no time period is specified, the escalation is considered to be valid during all times.");
$help["host_escalation_options"] = dgettext("help", "Define the criteria that determine when the host escalation is used. The escalation is used only if the host is in one of the states specified in these options. If no options are specified in a host escalation, the escalation is considered to be valid during all host states.");
$help["service_escalation_options"] = dgettext("help", "Define the criteria that determine when the service escalation is used. The escalation is used only if the service is in one of the states specified in these options. If no options are specified in a service escalation, the escalation is considered to be valid during all service states.");
$help["contact_groups"] = dgettext("help", "Select the contact group(s) that should be notified when the notification is escalated. You must specify at least one contact group in each escalation definition.");

$help["host_name"] = dgettext("help", "Select the host(s) that the escalation should apply to or is associated with.");
$help["service_description"] = dgettext("help", "Select the service(s) the escalation should apply to or is associated with.");
$help["hostgroup_name"] = dgettext("help", "Select the hostgroup(s) that the escalation should apply to. The escalation will apply to all hosts that are members of the specified hostgroup(s).");
$help["servicegroup_name"] = dgettext("help", "Select the service group(s) the escalation should apply to or is associated with. The escalation will apply to all services that are members of the specified service group(s).");
$help["metaservice_name"] = dgettext("help", "Select the meta service(s) the escalation should apply to or is associated with.");

/*
 * unsupported in centreon
 */
$help["contacts"] = dgettext("help", "Select contacts that should be notified whenever there are problems (or recoveries) with this host. Useful if you want notifications to go to just a few people and don't want to configure contact groups. You must specify at least one contact or contact group in each host escalation definition.");

?>

