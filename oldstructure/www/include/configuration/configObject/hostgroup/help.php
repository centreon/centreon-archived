<?php
$help = array();

$help["hostgroup_name"] = dgettext("help", "This directive is used to define a short name used to identify the host group.");
$help["alias"] = dgettext("help", "This directive is used to define is a longer name or description used to identify the host group. It is provided in order to allow you to more easily identify a particular host group.");
$help["snmp_options"] = dgettext("help", "The SNMP community and version specified here can be referenced in the check command by using the \$_HOSTSNMPCOMMUNITY\$ and \$_HOSTSNMPVERSION\$ macros."); // must be verified!

$help["members"] = dgettext("help", "This is a list of hosts that should be included in this group. This directive may be used as an alternative to (or in addition to) the hostgroups directive in host definitions.");
$help["hostgroup_members"] = dgettext("help", "This optional directive can be used to include hosts from other host groups in this host group.");

$help["notes"] = dgettext("help", "Define an optional string of notes pertaining to the host group.");
$help["notes_url"] = dgettext("help", "Define an optional URL that can be used to provide more information about the host group. Any valid URL can be used. This can be very useful if you want to make detailed information on the host group, emergency contact methods, etc. available to other support staff.");
$help["action_url"] = dgettext("help", "Define an optional URL that can be used to provide more actions to be performed on the host group. You will see the link to the action URL in the host group details.");
$help["icon_image"] = dgettext("help", "Define the image that should be associated with this host group here. This image will be displayed in the various places. The image will look best if it is 40x40 pixels in size.");
$help["icon_image_alt"] = dgettext("help", "Define an optional string that is used in the alternative description of the icon image.");
$help["statusmap_image"] = dgettext("help", "Define an image that should be associated with this host group in the statusmap CGI in monitoring engine. You can choose a JPEG, PNG, and GIF image. The GD2 image format is preferred, as other image formats must be converted first when the statusmap image is generated. The image will look best if it is 40x40 pixels in size.");

$help['hg_rrd_retention'] = dgettext("help", "RRD retention duration of all the services that are in this host group. If service is in multiple host groups, the highest retention value will be used.");
$help['hg_comment'] = dgettext("help", "Comments on this host group.");
$help['hg_activate'] = dgettext("help", "Whether this host group is enabled.");
?>

