<?php
$help = array();

$help["contactgroup_name"] = dgettext("help", "The contact group name is a short name used to identify the contact group in other sections.");
$help["alias"] = dgettext("help", "The alias is a longer name or description used to identify the contact group.");
$help["members"] = dgettext("help", "The linked contacts define a list of contacts that should be included in this group. This definition is an alternative way to specifying the contact groups in contact definitions.");

/*
 * unsupported in Centreon
 */
$help["contactgroup_members"] = dgettext("help", "This optional directive can be used to include contacts from other \"sub\" contact groups in this contact group. Specify a list of other contact groups whose members should be included in this group.");

?>

