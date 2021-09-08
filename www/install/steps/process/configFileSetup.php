<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

session_start();
require_once __DIR__ . '/../../../../bootstrap.php';
require_once '../functions.php';

$return = [
    'id' => 'configfile',
    'result' => 1,
    'msg' => '',
];

$step = new \CentreonLegacy\Core\Install\Step\Step6($dependencyInjector);
$parameters = $step->getDatabaseConfiguration();
$configuration = $step->getBaseConfiguration();
$engine = $step->getEngineConfiguration();

if ($parameters['address']) {
    $host = $parameters['address'];
} else {
    $host = 'localhost';
}

// mandatory parameters used by centCore or Gorgone.d in the legacy SSH mode
$patterns = [
    '/--ADDRESS--/',
    '/--DBUSER--/',
    '/--DBPASS--/',
    '/--CONFDB--/',
    '/--STORAGEDB--/',
    '/--CENTREONDIR--/',
    '/--CENTREON_CACHEDIR--/',
    '/--DBPORT--/',
    '/--INSTANCEMODE--/',
    '/--CENTREON_VARLIB--/',
];

// escape double quotes and backslashes
$needle = ['\\', '"'];
$escape = ['\\\\', '\"'];
$password = str_replace($needle, $escape, $parameters['db_password']);


$replacements = [
    $host,
    $parameters['db_user'],
    $password,
    $parameters['db_configuration'],
    $parameters['db_storage'],
    $configuration['centreon_dir'],
    $configuration['centreon_cachedir'],
    $parameters['port'],
    'central',
    $configuration['centreon_varlib'],
];

$centreonEtcPath = rtrim($configuration['centreon_etc'], '/');

/**
 * centreon.conf.php
 */
$centreonConfFile = $centreonEtcPath . '/centreon.conf.php';
$contents = file_get_contents('../../var/configFileTemplate');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($centreonConfFile, $contents);
chmod($centreonConfFile, 0660);
chown($centreonConfFile, 'apache');
chgrp($centreonConfFile, 'apache');

/**
 * conf.pm
 */
$centreonConfPmFile = $centreonEtcPath . '/conf.pm';
$contents = file_get_contents('../../var/configFilePmTemplate');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($centreonConfPmFile, $contents);
chmod($centreonConfPmFile, 0660);
chown($centreonConfPmFile, 'centreon');
chgrp($centreonConfPmFile, 'centreon');

// specific additional mandatory parameters used by Gorgone.d in a full ZMQ mode
array_push(
    $patterns,
    '/--CENTREON_SPOOL--/',
    '/--HTTPSERVERADDRESS--/',
    '/--HTTPSERVERPORT--/',
    '/--SSLMODE--/',
    '/--CENTREON_TRAPDIR--/',
    '/--GORGONE_VARLIB--/',
    '/--ENGINE_COMMAND--/'
);

array_push(
    $replacements,
    '/var/spool/centreon',
    '0.0.0.0',
    '8085',
    'false',
    '/etc/snmp/centreon_traps',
    '/var/lib/centreon-gorgone',
    $engine['monitoring_var_lib'] . '/rw/centengine.cmd'
);

/**
 * Database configuration file
 */
$gorgoneDatabaseFile = $centreonEtcPath . '/config.d/10-database.yaml';
$contents = file_get_contents('../../var/databaseTemplate.yaml');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($gorgoneDatabaseFile, $contents);

/**
 * Gorgone daemon configuration file for a central
 */
$gorgoneCoreFileForCentral = $centreonEtcPath . '/../centreon-gorgone/config.d/40-gorgoned.yaml';
$contents = file_get_contents('../../var/gorgone/gorgoneCentralTemplate.yaml');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($gorgoneCoreFileForCentral, $contents);

$return['result'] = 0;
echo json_encode($return);
exit;
