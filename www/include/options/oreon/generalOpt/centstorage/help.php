<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

$help = array();

/**
 * Engine Status
 */

$help['tip_enable_centstorage_engine'] = dgettext('help', 'Enables Centstorage Engine.');
$help['tip_insert_in_index_data'] = dgettext('help', 'Enables resource\'s insertion in index_data.');
$help['tip_path_to_rrdtool_database_for_metrics'] = dgettext('help', 'Path to RRDTool database for graphs of metrics.');
$help['tip_path_to_rrdtool_database_for_status'] = dgettext('help', 'Path to RRDTool database for graphs of status.');
$help['tip_path_to_rrdtool_database_for_nagios_statistics'] = dgettext('help', 'Path to RRDTool database for graphs of monitoring engine stats.');

/**
 * Retention durations
 */

$help['tip_rrdtool_database_size'] = dgettext('help', 'RRDTool database size (in days).');
$help['tip_retention_duration_for_data_in_mysql'] = dgettext('help', 'Duration of retention regarding performance data stored in database.');

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
$help['tip_logs_retention_duration'] = dgettext('help', 'Retention duration of logs.');

/**
 * Reporting Dashboard
 */

$help['tip_reporting_retention'] = dgettext('help', 'Retention duration of reporting data.');

/**
 * Audit Logs
 */

$help['tip_audit_log_option'] = dgettext('help', 'Enable/Disable logging of all modifications in Centreon');