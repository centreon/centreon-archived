<?php
$help = array();

$help["name"] = dgettext("help", "Define a short name for this dependency.");
$help["description"] = dgettext("help", "Define a description for this dependency for easier identification and differentiation.");
$help["inherits_parent"] = dgettext("help", "This directive indicates whether or not the dependency inherits dependencies of the host that is being depended upon (also referred to as the master host). In other words, if the master host is dependent upon other hosts and any one of those dependencies fail, this dependency will also fail.");

$help["execution_failure_criteria"] = dgettext("help", "This directive is used to specify the criteria that determine when the dependent host should not be actively checked. If the master host is in one of the failure states we specify, the dependent host will not be actively checked. If you specify None as an option, the execution dependency will never fail and the dependent host will always be actively checked (if other conditions allow for it to be).");
$help["notification_failure_criteria"] = dgettext("help", "This directive is used to define the criteria that determine when notifications for the dependent host should not be sent out. If the master host is in one of the failure states we specify, notifications for the dependent host will not be sent to contacts. If you specify None as an option, the notification dependency will never fail and notifications for the dependent host will always be sent out.");

/*
 * Host service description
 */


$help["host_name"] = dgettext("help", "This directive is used to identify the host(s) that the service that is being depended upon (also referred to as the master service) \"runs\" on or is associated with.");
$help["service_description"] = dgettext("help", "This directive is used to identify the description of the service that is being depended upon (also referred to as the master service).");
$help["dependent_host_name"] = dgettext("help", "This directive is used to identify the host(s) that the dependent service \"runs\" on or is associated with. Leaving this directive blank can be used to create \"same host\" dependencies.");
$help["dependent_service_description"] = dgettext("help", "This directive is used to identify the description of the dependent service.");

$help["hostgroup_name"] = dgettext("help", "This directive is used to identify the short name(s) of the hostgroup(s) that the service that is being depended upon (also referred to as the master service) \"runs\" on or is associated with. Multiple hostgroups should be separated by commas. The hostgroup_name may be used instead of, or in addition to, the host_name directive.");
$help["dependent_hostgroup_name"] = dgettext("help", "This directive is used to specify the short name (s) of the hostgroup(s) that the dependent service \"runs\" on or is associated with. The dependent_hostgroup may be used instead of, or in addition to, the dependent_host directive.");

$help["servicegroup_name"] = dgettext("help", "This directive is used to identify the description of the service group that is being depended upon (also referred to as the master service group).");
$help["dependent_servicegroup_name"] = dgettext("help", "This directive is used to identify the description of the dependent service group.");

$help["metaservice_name"] = dgettext("help", "This directive is used to identify the description of the meta service that is being depended upon (also referred to as the master meta service).");
$help["dependent_metaservice_name"] = dgettext("help", "This directive is used to identify the description of the dependent meta service.");


/*
 * unsupported in centreon
 */
$help["dependency_period"] = dgettext("help", "This directive is used to specify the short name of the time period during which this dependency is valid. If this directive is not specified, the dependency is considered to be valid during all times.");


?>

