<?php
$help = array();

$help["servicegroup_name"] = dcgettext("help", "Define a short name for the service group here. The short name will be used to display the service group in monitoring, views and reporting.");
$help["alias"] = dcgettext("help", "Define a longer name or description for the service group here.");
$help["members"] = dcgettext("help", "This is a list of host-bound services that should be included in this service group.");
$help["hg_members"] = dcgettext("help", "This is a list of host group-bound services that should be included in this service group.");

/*
 * unsupported in Centreon
 */
$help["servicegroup_members"] = dcgettext("help", "This optional directive can be used to include services from other \"sub\" service groups in this service group.");
$help["notes"] = dcgettext("help", "This directive is used to define an optional string of notes pertaining to the service group. If you specify a note here, you will see the it in the extended information CGI (when you are viewing information about the specified service group).");
$help["notes_url"] = dcgettext("help", "This directive is used to define an optional URL that can be used to provide more information about the service group. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/). This can be very useful if you want to make detailed information on the service group, emergency contact methods, etc. available to other support staff.");
$help["action_url"] = dcgettext("help", "This directive is used to define an optional URL that can be used to provide more actions to be performed on the service group. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/).");

?>

