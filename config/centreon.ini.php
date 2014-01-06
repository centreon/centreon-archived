<?php

/* 
 * MySQL Connection
 */
$conf_centreon['hostCentreon'] = "localhost";
$conf_centreon['hostCentstorage'] = "localhost";
$conf_centreon['user'] = "centreon";
$conf_centreon['password'] = "XKvg2oj19CW4";
$conf_centreon['db'] = "centreon";
$conf_centreon['dbcstg'] = "centreon_storage";
$conf_centreon['port'] = "3306";


ini_set("include_path", ini_get("include_path").":/srv/centreon-3/www/class");

/* path to classes */
$classdir='/srv/centreon-3/class';

/* Centreon Path */
$centreon_path='/srv/centreon-3/';

/*
 * Enable Errors for PHP Notice
 */
ini_set("display_errors", "On");

/*
 * Set session timeout
 */
ini_set("session.gc_maxlifetime", "31536000");

/*
 * Set Smarty Cache/Compil directory
 */
$smartyDirectory = $centreon_path."/var/Smarty/";

?>