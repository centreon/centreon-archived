<?php
$help = array();

/**
 * Monitoring Engine
 */

$help['tip_default_engine'] = dgettext('help', 'Default monitoring engine.');
$help['tip_images_directory'] = dgettext('help', 'Directory where images are stored.');
$help['tip_plugins_directory'] = dgettext('help', 'Directory where check plugins are stored.');
$help["tip_interval_length"] = dgettext(
    "help",
    "This is the number of seconds per \"unit interval\" used for timing in the scheduling queue,"
    . " re-notifications, etc. \"Units intervals\" are used in the object configuration file to determine how"
    . " often to run a service check, how often to re-notify a contact, etc. The default value for this is set to 60,"
    . " which means that a \"unit value\" of 1 in the object configuration file will mean 60 seconds (1 minute)."
);

/**
 * Monitoring database layer
 */

$help['tip_broker_engine_used_by_centreon'] = dgettext('help', 'Broker module used by Centreon.');

/**
 * Correlation Engine
 */

$help['tip_start_script_for_correlator_engine'] = dgettext('help', 'Init script for broker daemon.');

/**
 * Mailer path
 */

$help['tip_directory+mailer_binary'] = dgettext('help', 'Mailer binary with complete path.');

/**
 * Tactical Overview
 */

$help['tip_maximum_number_of_hosts_to_show'] = dgettext(
    'help',
    'Maximum number of hosts to show in the Tactical Overview page.'
);
$help['tip_maximum_number_of_services_to_show'] = dgettext(
    'help',
    'Maximum number of services to show in the Tactical Overview page.'
);
$help['tip_page_refresh_interval'] = dgettext('help', 'Refresh interval used in the Tactical Overview page.');

/**
 * Default acknowledgement settings
 */

$help['tip_sticky'] = dgettext('help', '[Sticky] option is enabled by default.');
$help['tip_notify'] = dgettext('help', '[Notify] option is enabled by default.');
$help['tip_persistent'] = dgettext('help', '[Persistent] option is enabled by default.');
$help['tip_acknowledge_services_attached_to_hosts'] = dgettext(
    'help',
    '[Acknowledge services attached to hosts] option is enabled by default.'
);
$help['tip_force_active_checks'] = dgettext('help', '[Force Active Checks] option is enabled by default.');

/**
 * Default downtime settings
 */

$help['tip_fixed'] = dgettext('help', 'Fixed.');
$help['tip_set_downtimes_on_services_attached_to_hosts'] = dgettext(
    'help',
    '[Set downtimes on services attached to hosts] option is enbaled by default.'
);
$help['tip_duration'] = dgettext('help', 'Default duration of scheduled downtimes.');

/**
 * Misc
 */
$help['tip_console_notification'] = dgettext(
    'help',
    'When enabled, notification messages are displayed when new alerts arise in the monitoring consoles.'
);
$help['tip_host_notification_0'] = dgettext('help', 'When enabled, "Host Up" notification messages will be displayed.');
$help['tip_host_notification_1'] = dgettext(
    'help',
    'When enabled, "Host Down" notification messages will be displayed.'
);
$help['tip_host_notification_2'] = dgettext(
    'help',
    'When enabled, "Host Unreachable" notification messages will be displayed.'
);
$help['tip_svc_notification_0'] = dgettext(
    'help',
    'When enabled, "Service OK" notification messages will be displayed.'
);
$help['tip_svc_notification_1'] = dgettext(
    'help',
    'When enabled, "Service Warning" notification messages will be displayed.'
);
$help['tip_svc_notification_2'] = dgettext(
    'help',
    'When enabled, "Service Critical" notification messages will be displayed.'
);
$help['tip_svc_notification_3'] = dgettext(
    'help',
    'When enabled, "Service Unknown" notification messages will be displayed.'
);
