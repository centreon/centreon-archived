<?php
$help = array();

/**
 * RRDTool Configuration
 */

$help['tip_directory+rrdtool_binary'] = dgettext('help', 'RRDTOOL binary complete path.');
$help['tip_rrdtool_version'] = dgettext('help', 'RRDTool version.');

/**
 * RRDCached Properties
 */
$help['tip_rrdcached_enable'] = dgettext(
    'help',
    'Enable the rrdcached for Centreon. This option is valid only with Centreon Broker'
);
$help['tip_rrdcached_port'] = dgettext('help', 'Port for communicating with rrdcached');
$help['tip_rrdcached_unix_path'] = dgettext(
    'help',
    'The absolute path to unix socket for communicating with rrdcached'
);
