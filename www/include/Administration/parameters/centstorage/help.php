<?php
$help = array();

/**
 * Engine Status
 */

$help['tip_enable_centstorage_engine'] = dgettext('help', 'Enables Centstorage Engine.');
$help['tip_insert_in_index_data'] = dgettext('help', 'Enables resource\'s insertion in index_data.');
$help['tip_path_to_rrdtool_database_for_metrics'] = dgettext('help', 'Path to RRDTool database for graphs of metrics.');
$help['tip_path_to_rrdtool_database_for_status'] = dgettext('help', 'Path to RRDTool database for graphs of status.');
$help['tip_path_to_rrdtool_database_for_nagios_statistics'] = dgettext(
    'help',
    'Path to RRDTool database for graphs of monitoring engine stats.'
);

/**
 * Retention durations
 */

$help['tip_rrdtool_database_size'] = dgettext('help', 'RRDTool database size (in days).');
$help['tip_retention_duration_for_data_in_mysql'] = dgettext(
    'help',
    'Duration of retention regarding performance data stored in database. 0 means that no retention will be applied.'
);
$help['tip_retention_duration_for_data_in_downtimes'] = dgettext(
    'help',
    'Duration of retention regarding downtimes stored in database. 0 means that no retention will be applied.'
);
$help['tip_retention_duration_for_data_in_comments'] = dgettext(
    'help',
    'Duration of retention regarding comments stored in database. 0 means that no retention will be applied.'
);

/**
 * Purge options
 */

$help['tip_rrdtool_auto_delete'] = dgettext('help', 'Enables RRDTool auto purge system.');

/**
 * Censtorage Core Options
 */

$help['tip_purge_check_interval'] = dgettext('help', 'Centstorage will check for data to purge every now and then.');

/**
 * Input treatment options
 */

$help['tip_storage_type'] = dgettext('help', 'Storage Type.');

/**
 * Drop possibility after parsing performance data
 */

$help['tip_drop_data_in_another_file'] = dgettext('help', 'Dumps data into another file.');
$help['tip_drop_file'] = dgettext('help', 'Dump file.');

/**
 * Logs Integration Properties
 */

$help['tip_archive_nagios_logs'] = dgettext('help', 'Archives logs of monitoring engine.');
$help['tip_logs_retention_duration'] = dgettext(
    'help',
    'Retention duration of logs. 0 means that no retention will be applied.'
);

/**
 * Reporting Dashboard
 */

$help['tip_reporting_retention'] = dgettext(
    'help',
    'Retention duration of reporting data. 0 means that no retention will be applied.'
);

/**
 * Audit Logs
 */

$help['tip_audit_log_option'] = dgettext('help', 'Enable/Disable logging of all modifications in Centreon');
