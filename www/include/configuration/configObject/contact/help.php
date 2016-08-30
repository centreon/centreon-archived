<?php
$help = array();
$help["mc_update"] = dgettext("help", "Choose the update mode for the below field: incremental adds the selected values, replacement overwrites the original values.");

/*
 * General Information
 */
$help["contact_name"] = dgettext("help", "The full name is used to identify the contact in contact group definitions and in notifications.");
$help["alias"] = dgettext("help", "The alias is a short name used as login name in Centreon.");
$help["email"] = dgettext("help", "Specify the primary email address for the contact here. Additional (email) addresses can be defined under additional information of the contact. Depending on the notification command used, the email address can be used to send out email notifications.");
$help["pager"] = dgettext("help", "Specify a pager number or an address at a pager gateway here. Any format is possible as long as it is supported by the notification command.");
$help["contactgroups"] = dgettext("help", "Link the contact to the contactgroup(s) the user should belong to. This is an alternative way to specifying the members in contactgroup definitions.");

$help["contact_enable_notifications"] = dgettext("help", "Enable notification form for this user. If you select \"no\" , this contact will never be generated into configuration files.");

$help["host_notification_options"] = dgettext("help", "Define the host states for which notifications can be sent out to this contact. If you specify None as an option, the contact will not receive any type of host notifications.");
$help["host_notification_period"] = dgettext("help", "Specify the time period during which the contact can be notified about host problems or recoveries. You can think of this as an \"on call\" time for host notifications for the contact.");
$help["host_notification_commands"] = dgettext("help", "Define one or more commands used to notify the contact of a host problem or recovery. All notification commands are executed when the contact needs to be notified.");

$help["service_notification_options"] = dgettext("help", "Define the service states for which notifications can be sent out to this contact. If you specify None as an option, the contact will not receive any type of service notifications.");
$help["service_notification_period"] = dgettext("help", "Specify the time period during which the contact can be notified about service problems or recoveries. You can think of this as an \"on call\" time for service notifications for the contact.");
$help["service_notification_commands"] = dgettext("help", "Define one ore more commands used to notify the contact of a service problem or recovery. All notification commands are executed when the contact needs to be notified.");

$help["ldap_dn"] = dgettext("help", "Enter the LDAP Distinguished Name (DN) which identifies this user.");

$help["ldap_group"] = dgettext("help", "LDAP groups of user, for informative purpose.");

/*
 * Centreon specific authentication
 */
$help["centreon_login"] = dgettext("help", "Specify if the contact is allowed to login into centreon.");
$help["password"] = dgettext("help", "Define the password for the centreon login here.");
$help["password2"] = dgettext("help", "Enter the password again.");
$help["language"] = dgettext("help", "Define the default language for the user for the centreon front-end here.");
$help["admin"] = dgettext("help", "Specify if the user has administrative permissions. Administrators are not restricted by access control list (ACL) settings.");
$help["autologin_key"] = dgettext("help", "Token used for autologin. Refer to the Centreon documentation to know more about its usage.");
$help["auth_type"] = dgettext("help", "Specify the source for user credentials. Choose between Centreon and LDAP, whereas LDAP is only available when configured in Administration Options.");
$help["location"] = dgettext("help", "Select the timezone, in which the user resides, from the list. The timezones are listed as time difference to Greenwich Mean Time (GMT) in hours.");
$help["reach_api"] = dgettext("help", "Allow this user to access to Centreon Rest API with its account.");

/*
 * Additional Information
 */
$help["addressx"] = dgettext("help", "Addresses 1-6 are optional and used to define additional \"addresses\" for the contact. These addresses can be anything - cell phone numbers, instant messaging addresses, etc. - the format must be supported by your notification plugins.");

/*
 * unsupported in Centreon
 */
$help["host_notifications_enabled"] = dgettext("help", "This directive is used to determine whether or not the contact will receive notifications about host problems and recoveries.");
$help["service_notifications_enabled"] = dgettext("help", "This directive is used to determine whether or not the contact will receive notifications about service problems and recoveries.");
$help["can_submit_commands"] = dgettext("help", "This directive is used to determine whether or not the contact can submit external commands to monitoring engine from the CGIs.");
$help["retain_status_information"] = dgettext("help", "This directive is used to determine whether or not status-related information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");
$help["retain_nonstatus_information"] = dgettext("help", "This directive is used to determine whether or not non-status information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");
$help["aclgroups"] = dgettext("help", "ACL groups of user.");
