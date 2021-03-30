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

$return = array(
    'id' => 'baseconf',
    'result' => 1,
    'msg' => ''
);

$factory = new \CentreonLegacy\Core\Utils\Factory($dependencyInjector);
$utils = $factory->newUtils();
$step = new \CentreonLegacy\Core\Install\Step\Step6($dependencyInjector);
$parameters = $step->getDatabaseConfiguration();

try {
    $link = new \PDO(
        'mysql:host=' . $parameters['address'] . ';port=' . $parameters['port'],
        $parameters['root_user'],
        $parameters['root_password']
    );
} catch (\PDOException $e) {
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

/**
 * Create tables
 */
try {
    $result = $link->query('use ' . $parameters['db_configuration']);
    if (!$result) {
        throw new \Exception('Cannot access to "' . $parameters['db_configuration'] . '" database');
    }

    $macros = array_merge(
        $step->getBaseConfiguration(),
        $step->getDatabaseConfiguration(),
        $step->getAdminConfiguration(),
        $step->getEngineConfiguration(),
        $step->getBrokerConfiguration()
    );

    $utils->executeSqlFile(__DIR__ . '/../../insertMacros.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../insertCommands.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../insertTimeperiods.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../var/baseconf/centreon-engine.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../var/baseconf/centreon-broker.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../insertTopology.sql', $macros);
    $utils->executeSqlFile(__DIR__ . '/../../insertBaseConf.sql', $macros);
} catch (\Exception $e) {
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

$hostName = gethostname() ?: null;
// Insert Central to 'platform_topology' table, as first server and parent of all others.
$centralServerQuery = $link->query("SELECT `id`, `name` FROM nagios_server WHERE localhost = '1'");
if ($row = $centralServerQuery->fetch()) {
    $stmt = $link->prepare("
        INSERT INTO `platform_topology` (
            `address`,
            `hostname`,
            `name`,
            `type`,
            `parent_id`,
            `server_id`,
            `pending`
        ) VALUES (
            :centralAddress,
            :hostname,
            :name,
            'central',
            NULL,
            :id,
            '0'
        )
    ");
    $stmt->bindValue(':centralAddress', $_SERVER['SERVER_ADDR'], \PDO::PARAM_STR);
    $stmt->bindValue(':hostname', $hostName, \PDO::PARAM_STR);
    $stmt->bindValue(':name', $row['name'], \PDO::PARAM_STR);
    $stmt->bindValue(':id', (int)$row['id'], \PDO::PARAM_INT);
    $stmt->execute();
}

// Manage timezone
$timezone = date_default_timezone_get();
$resTimezone = $link->query("SELECT timezone_id FROM timezone WHERE timezone_name= '" . $timezone . "'");
if (!$resTimezone) {
    $return['msg'] = _('Cannot get timezone information');
    echo json_encode($return);
    exit;
}
if ($row = $resTimezone->fetch()) {
    $timezoneId = $row['timezone_id'];
} else {
    $timezoneId = '334'; # Europe/London timezone
}
$link->exec("INSERT INTO `options` (`key`, `value`) VALUES ('gmt','" . $timezoneId . "')");

# Generate random key for this instance and set it to be not central and not remote
$uniqueKey = md5(uniqid(rand(), true));
$informationsTableInsert = "INSERT INTO `informations` (`key`,`value`) VALUES
    ('appKey', '{$uniqueKey}'),
    ('isRemote', 'no'),
    ('isCentral', 'yes')";

$link->exec($informationsTableInsert);

splitQueries('../../insertACL.sql', ';', $link, '../../tmp/insertACL');

/* Get Centreon version */
$res = $link->query("SELECT `value` FROM informations WHERE `key` = 'version'");
if (!$res) {
    $return['msg'] = _('Cannot get Centreon version');
    echo json_encode($return);
    exit;
}
$row = $res->fetch();
$step->setVersion($row['value']);

$return['result'] = 0;
echo json_encode($return);
exit;
