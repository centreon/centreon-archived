<?php
$help = array();

/*
 * General Information
 */
$help["contact_name"] = dcgettext("help", "The full name is used to identify the contact in contact group definitions and in notifications.");
$help["alias"] = dcgettext("help", "The alias is a short name used as login name in Centreon.");
$help["email"] = dcgettext("help", "Specify the primary email address for the contact here. Additional (email) addresses can be defined under additional informations of the contact. Depending on the notification command used, the email address can be used to send out email notifications.");
$help["pager"] = dcgettext("help", "Specify a pager number or an address at a pager gateway here. Any format is possible as long as it is supported by the notification command.");
$help["contactgroups"] = dcgettext("help", "Link the contact to the contactgroup(s) the user should belong to. This is an alternative way to specifying the members in contactgroup definitions.");

$help["host_notification_options"] = dcgettext("help", "Define the host states for which notifications can be sent out to this contact. If you specify None as an option, the contact will not receive any type of host notifications.");
$help["host_notification_period"] = dcgettext("help", "Specify the time period during which the contact can be notified about host problems or recoveries. You can think of this as an \"on call\" time for host notifications for the contact.");
$help["host_notification_commands"] = dcgettext("help", "Define one or more commands used to notify the contact of a host problem or recovery. All notification commands are executed when the contact needs to be notified.");

$help["service_notification_options"] = dcgettext("help", "Define the service states for which notifications can be sent out to this contact. If you specify None as an option, the contact will not receive any type of service notifications.");
$help["service_notification_period"] = dcgettext("help", "Specify the time period during which the contact can be notified about service problems or recoveries. You can think of this as an \"on call\" time for service notifications for the contact.");
$help["service_notification_commands"] = dcgettext("help", "Define one ore more commands used to notify the contact of a service problem or recovery. All notification commands are executed when the contact needs to be notified.");

/*
 * Centreon specific authentication
 */
$help["centreon_login"] = dcgettext("help", "Specify if the contact is allowed to login into centreon.");
$help["password"] = dcgettext("help", "Define the password for the centreon login here.");
$help["language"] = dcgettext("help", "Define the default language for the user for the centreon front-end here.");
$help["admin"] = dcgettext("help", "Specify if the user has administrative permissions. Administrators are not restricted by access control list (ACL) settings.");
$help["auth_type"] = dcgettext("help", "Specify the source for user credentials. Choose between Centreon and LDAP, whereas LDAP is only available when configured in Administration Options.");

/*
 * Additional Information
 */
$help["addressx"] = dcgettext("help", "Addresses 1-6 are optional and used to define additional \"addresses\" for the contact. These addresses can be anything - cell phone numbers, instant messaging addresses, etc. - the format must be supported by your notification plugins.");

/*
 * unsupported in Centreon
 */
$help["host_notifications_enabled"] = dcgettext("help", "This directive is used to determine whether or not the contact will receive notifications about host problems and recoveries.");
$help["service_notifications_enabled"] = dcgettext("help", "This directive is used to determine whether or not the contact will receive notifications about service problems and recoveries.");
$help["can_submit_commands"] = dcgettext("help", "This directive is used to determine whether or not the contact can submit external commands to Nagios from the CGIs.");
$help["retain_status_information"] = dcgettext("help", "This directive is used to determine whether or not status-related information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");
$help["retain_nonstatus_information"] = dcgettext("help", "This directive is used to determine whether or not non-status information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive.");

?>

