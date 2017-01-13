<?php
$help = array();

/**
 * General Information
 */

$help['tip_action_name'] = dgettext('help', 'Name of action rule.');
$help['tip_description'] = dgettext('help', 'Description of action rule.');

/**
 * Relations
 */

$help['tip_linked_groups'] = dgettext('help', 'Implied ACL groups.');

/**
 *  Global Functionalities Access
 */

$help['tip_display_top_counter'] = dgettext('help', 'The monitoring overview will be displayed at the top of all pages.');
$help['tip_display_top_counter_pollers_statistics'] = dgettext('help', 'The monitoring poller status overview will be displayed at the top of all pages.');
$help['tip_display_poller_listing'] = dgettext('help', 'The poller filter will be available to users in the monitoring consoles.');

/**
 * Generation of files
 */
$help['tip_display_generate_cfg'] = dgettext('help', 'Allows user to generate and export configuration, and restart poller.');
$help['tip_display_generate_trap'] = dgettext('help', 'Allows user to generate and export configuration, and restart centreontrapd process.');

/**
 * Global Nagios Actions (External Process Commands)
 */

$help['tip_shutdown_nagios'] = dgettext('help', 'Allows users to stop the monitoring systems.');
$help['tip_restart_nagios'] = dgettext('help', 'Allows users to restart the monitoring systems.');
$help['tip_enable_disable_notifications'] = dgettext('help', 'Allows users to enable or disable notifications.');
$help['tip_enable_service_checks'] = dgettext('help', 'Allows users to enable or disable service checks.');
$help['tip_enable_passive_service_checks'] = dgettext('help', 'Allows users to enable or disable passive service checks.');
$help['tip_enable_host_checks'] = dgettext('help', 'Allows users to enable or disable host checks.');
$help['tip_enable_passive_host_checks'] = dgettext('help', 'Allows users to enable or disable passive host checks.');
$help['tip_enable_event_handlers'] = dgettext('help', 'Allows users to enable or disable event handlers.');
$help['tip_enable_flap_detection'] = dgettext('help', 'Allows users to enable or disable flap detection.');
$help['tip_enable_obsessive_service_checks'] = dgettext('help', 'Allows users to enable or disable obsessive service checks.');
$help['tip_enable_obsessive_host_checks'] = dgettext('help', 'Allows users to enable or disable obsessive host checks.');
$help['tip_enable_performance_data'] = dgettext('help', 'Allows users to enable or disable performance data processing.');

/**
 * Services Actions Access
 */

$help['tip_enable_disable_checks_for_a_service'] = dgettext('help', 'Allows users to enable or disable checks of a service.');
$help['tip_enable_disable_notifications_for_a_service'] = dgettext('help', 'Allows users to enable or disable notifications of a service.');
$help['tip_acknowledge_a_service'] = dgettext('help', 'Allows users to acknowledge a service.');
$help['tip_disacknowledge_a_service'] = dgettext('help', 'Allows users to remove an acknowledgement from a service.');
$help['tip_re_schedule_the_next_check_for_a_service'] = dgettext('help', 'Allows users to re-schedule next check of a service.');
$help['tip_re_schedule_the_next_check_for_a_service_forced'] = dgettext('help', 'Allows users to re-schedule next check of a service by placing its priority to the top.');
$help['tip_schedule_downtime_for_a_service'] = dgettext('help', 'Allows users to schedule downtime on a service.');
$help['tip_add_delete_a_comment_for_a_service'] = dgettext('help', 'Allows users to add or delete a comment of a service.');
$help['tip_enable_disable_event_handler_for_a_service'] = dgettext('help', 'Allows users to enable or disable the event handler processing of a service.');
$help['tip_enable_disable_flap_detection_of_a_service'] = dgettext('help', 'Allows users to enable or disable flap detection of a service.');
$help['tip_enable_disable_passive_checks_of_a_service'] = dgettext('help', 'Allows users to enable or disable passive checks of a service.');
$help['tip_submit_result_for_a_service'] = dgettext('help', 'Allows users to submit result to a service.');

/**
 * Hosts Actions Access
 */

$help['tip_enable_disable_checks_for_a_host'] = dgettext('help', 'Allows users to enable or disable checks of a host.');
$help['tip_enable_disable_notifications_for_a_host'] = dgettext('help', 'Allows users to enable or disable notifications of a host.');
$help['tip_acknowledge_a_host'] = dgettext('help', 'Allows users to acknowledge a host.');
$help['tip_disacknowledge_a_host'] = dgettext('help', 'Allows users to remove an acknowledgement from a host.');
$help['tip_schedule_the_check_for_a_host'] = dgettext('help', 'Allows users to re-schedule next check of a host.');
$help['tip_schedule_the_check_for_a_host_forced'] = dgettext('help', 'Allows users to re-schedule next check of a host by placing its priority to the top.');
$help['tip_schedule_downtime_for_a_host'] = dgettext('help', 'Allows users to schedule downtime on a host.');
$help['tip_add_delete_a_comment_for_a_host'] = dgettext('help', 'Allows users to add or delete a comment of a host.');
$help['tip_enable_disable_event_handler_for_a_host'] = dgettext('help', 'Allows users to enable or disable the event handler processing of a host.');
$help['tip_enable_disable_flap_detection_for_a_host'] = dgettext('help', 'Allows users to enable or disable flap detection of a host.');
$help['tip_enable_disable_checks_services_of_a_host'] = dgettext('help', 'Allows users to enable or disable all service checks of a host.');
$help['tip_enable_disable_notifications_services_of_a_host'] = dgettext('help', 'Allows users to enable or disable service notifications of a host.');
$help['tip_submit_result_for_a_host'] = dgettext('help', 'Allows users to submit result to a host.');

/**
 * Additional Information
 */

$help['tip_status'] = dgettext('help', 'Enable or disable the ACL action rule.');
