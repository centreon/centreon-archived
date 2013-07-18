<?php
$help = array();

/**
 * Centcore Settings
 */
$help['tip_enable_perfdata_sync'] = dgettext('help', 'Enable Perfdata synchronisation between poller and Central Server operated by Centore');
$help['tip_enable_logs_sync'] = dgettext('help', 'Enable Monitoring Engine Logs synchronisation between poller and Central Server operated by Centore');
$help['centcore_cmd_timeout'] = dgettext('help', "Timeout value in seconds. Used for freeing Centcore when it's stuck because of blocking pipe files or SSH connections.");