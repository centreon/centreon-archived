<?php
/*
 * Global include for all files
 */

// Define constants
$constants = array(
    '_CENTREON_PATH_' => realpath(__DIR__ . '/..'),
    '_CENTREON_ETC_' => '@CENTREON_ETC@',
    '_CENTREON_LOG_' => '@CENTREON_LOG@',
    '_CENTREON_VARLIB_' => '@CENTREON_VARLIB@'
);
foreach ($constants as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

if (file_exists(_CENTREON_ETC_ . '/centreon.conf.php')) {
    require_once _CENTREON_ETC_ . '/centreon.conf.php';

    define('hostCentreon', $conf_centreon['hostCentreon']);
    define('hostCentstorage', $conf_centreon['hostCentstorage']);
    define('user', $conf_centreon['user']);
    define('password', $conf_centreon['password']);
    define('db', $conf_centreon['db']);
    define('dbcstg', $conf_centreon['dbcstg']);
    define('port', $conf_centreon['port']);

    if (isset($dependencyInjector)) {
        $dependencyInjector['configuration'] = function ($c) use ($conf_centreon) {
            return new CentreonLegacy\Core\Configuration\Configuration($conf_centreon);
        };
    }
}