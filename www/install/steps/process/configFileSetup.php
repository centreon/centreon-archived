<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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

$return = array(
    'id' => 'configfile',
    'result' => 1,
    'msg' => ''
);

$step = new \CentreonLegacy\Core\Install\Step\Step6($dependencyInjector);
$parameters = $step->getDatabaseConfiguration();
$configuration = $step->getBaseConfiguration();

if ($parameters['address']) {
    $host = $parameters['address'];
} else {
    $host = 'localhost';
}

$patterns = array(
    '/--ADDRESS--/',
    '/--DBUSER--/',
    '/--DBPASS--/',
    '/--CONFDB--/',
    '/--STORAGEDB--/',
    '/--CENTREONDIR--/',
    '/--CENTREON_CACHEDIR--/',
    '/--DBPORT--/',
    '/--INSTANCEMODE--/',
    '/--CENTREON_VARLIB--/'
);

$replacements = array(
    $host,
    $parameters['db_user'],
    $parameters['db_password'],
    $parameters['db_configuration'],
    $parameters['db_storage'],
    $configuration['centreon_dir'],
    $configuration['centreon_cachedir'],
    $parameters['port'],
    "central",
    $configuration['centreon_varlib']
);

/**
 * centreon.conf.php
 */
$centreonConfFile = rtrim($configuration['centreon_etc'], '/') . '/centreon.conf.php';
$contents = file_get_contents('../../var/configFileTemplate');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($centreonConfFile, $contents);

/**
 * conf.pm
 */
$centreonConfPmFile = rtrim($configuration['centreon_etc'], '/') . '/conf.pm';
$contents = file_get_contents('../../var/configFilePmTemplate');
$contents = preg_replace($patterns, $replacements, $contents);
file_put_contents($centreonConfPmFile, $contents);

$return['result'] = 0;
echo json_encode($return);
exit;
