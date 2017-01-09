<?php
$help = array();

$help["servicegroup_name"] = dgettext("help", "Define a short name for the service group here. The short name will be used to display the service group in monitoring, views and reporting.");
$help["alias"] = dgettext("help", "Define a longer name or description for the service group here.");
$help["members"] = dgettext("help", "This is a list of host-bound services that should be included in this service group.");
$help["hg_members"] = dgettext("help", "This is a list of host group-bound services that should be included in this service group.");
$help["st_members"] = dgettext("help", "This is a list of service templates that should be included in this service group. Service template needs to be associated with a host template in order to show up here.");

/*
 * unsupported in Centreon
 */
$help["servicegroup_members"] = dgettext("help", "This optional directive can be used to include services from other \"sub\" service groups in this service group.");
$help["notes"] = dgettext("help", "This directive is used to define an optional string of notes pertaining to the service group. If you specify a note here, you will see it in the extended information CGI (when you are viewing information about the specified service group).");
$help["notes_url"] = dgettext("help", "This directive is used to define an optional URL that can be used to provide more information about the service group. Any valid URL can be used. If you plan on using relative paths, the base path will be the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/). This can be very useful if you want to make detailed information on the service group, emergency contact methods, etc. available to other support staff.");
$help["action_url"] = dgettext("help", "This directive is used to define an optional URL that can be used to provide more actions to be performed on the service group. Any valid URL can be used. If you plan on using relative paths, the base path will be the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/).");

$help["geo_coords"] = dgettext("help", "Geographical coordinates use by Centreon Map module to position element on map. Define \"Latitude,Longitude\", for example for Paris coordinates set \"48.51,2.20\"");
