<?php
$help = array();

$help["name"] = dgettext("help", "Define a short name for this dependency.");
$help["description"] = dgettext("help", "Define a description for this dependency for easier identification and differentiation.");
$help["inherits_parent"] = dgettext("help", "This directive indicates whether or not the dependency inherits dependencies of the host that is being depended upon (also referred to as the master host). In other words, if the master host is dependent upon other hosts and any one of those dependencies fail, this dependency will also fail.");

$help["execution_failure_criteria"] = dgettext("help", "This directive is used to specify the criteria that determine when the dependent host should not be actively checked. If the master host is in one of the failure states we specify, the dependent host will not be actively checked. If you specify None as an option, the execution dependency will never fail and the dependent host will always be actively checked (if other conditions allow for it to be).");
$help["notification_failure_criteria"] = dgettext("help", "This directive is used to define the criteria that determine when notifications for the dependent host should not be sent out. If the master host is in one of the failure states we specify, notifications for the dependent host will not be sent to contacts. If you specify None as an option, the notification dependency will never fail and notifications for the dependent host will always be sent out.");

$help["host_name"] = dgettext("help", "This directive is used to identify the short name(s) of the host(s) that is being depended upon (also referred to as the master host).");
$help["hostgroup_name"] = dgettext("help", "This directive is used to identify the short name(s) of the hostgroup(s) that is being depended upon (also referred to as the master host). The hostgroup_name may be used instead of, or in addition to, the host_name directive.");

$help["dependent_host_name"] = dgettext("help", "This directive is used to identify the dependent host(s).");
$help["dependent_hostgroup_name"] = dgettext("help", "This directive is used to identify the short name(s) of the dependent hostgroup(s). The dependent_hostgroup_name may be used instead of, or in addition to, the dependent_host_name directive.");

/*
 * unsupported in centreon
 */
$help["dependency_period"] = dgettext("help", "This directive is used to specify the short name of the time period during which this dependency is valid. If this directive is not specified, the dependency is considered to be valid during all times.");

?>

