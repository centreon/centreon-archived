<?php
/*
 * Global include for all files
 */

/* MySQL configuration file */
include_once "@CENTREON_ETC@/centreon.conf.php";

define('_CENTREON_PATH_', $centreon_path);
define('_CENTREON_LOG_', '@CENTREON_LOG@');
define('_CENTREON_VARLIB_', '@CENTREON_VARLIB@');

define('hostCentreon', $conf_centreon['hostCentreon']);
define('hostCentstorage', $conf_centreon['hostCentstorage']);
define('user', $conf_centreon['user']);
define('password', $conf_centreon['password']);
define('db', $conf_centreon['db']);
define('dbcstg', $conf_centreon['dbcstg']);
define('port', $conf_centreon['port']);


/* Enable PHP error */
ini_set('display_errors', 'Off');
