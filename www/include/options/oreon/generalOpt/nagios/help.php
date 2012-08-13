<?php
$help = array();

/**
 * Monitoring Engine
 */

$help['tip_default_engine'] = dgettext('help', 'Default monitoring engine.');
$help['tip_images_directory'] = dgettext('help', 'Directory where images are stored.');
$help['tip_plugins_directory'] = dgettext('help', 'Directory where check plugins are stored.');

/**
 * Monitoring database layer
 */

$help['tip_broker_engine_used_by_centreon'] = dgettext('help', 'Broker module used by Centreon.');

/**
 * Correlation Engine
 */

$help['tip_start_script_for_correlator_engine'] = dgettext('help', 'Init script for Correlation Engine. Only compatible with Centreon Engine.');

/**
 * Mailer path
 */

$help['tip_directory+mailer_binary'] = dgettext('help', 'Mailer binary with complete path.');

/**
 * Tactical Overview
 */

$help['tip_maximum_number_of_hosts_to_show'] = dgettext('help', 'Maximum number of hosts to show in the Tactical Overview page.');
$help['tip_maximum_number_of_services_to_show'] = dgettext('help', 'Maximum number of services to show in the Tactical Overview page.');
$help['tip_page_refresh_interval'] = dgettext('help', 'Refresh interval used in the Tactical Overview page.');

/**
 * Default acknowledgement settings
 */

$help['tip_sticky'] = dgettext('help', '[Sticky] option is enabled by default.');
$help['tip_notify'] = dgettext('help', '[Notify] option is enabled by default.');
$help['tip_persistent'] = dgettext('help', '[Persistent] option is enabled by default.');
$help['tip_acknowledge_services_attached_to_hosts'] = dgettext('help', '[Acknowledge services attached to hosts] option is enabled by default.');
$help['tip_force_active_checks'] = dgettext('help', '[Force Active Checks] option is enabled by default.');

/**
 * Default downtime settings
 */

$help['tip_fixed'] = dgettext('help', 'Fixed.');
$help['tip_set_downtimes_on_services_attached_to_hosts'] = dgettext('help', '[Set downtimes on services attached to hosts] option is enbaled by default.');
$help['tip_duration'] = dgettext('help', 'Default duration of scheduled downtimes.');

/**
 * Centcore Settings
 */
$help['tip_enable_perfdata_sync'] = dgettext('help', 'Enable Perfdata synchronisation between poller and Central Server operated by Centore');
$help['tip_enable_logs_sync'] = dgettext('help', 'Enable Nagios Logs synchronisation between poller and Central Server operated by Centore');