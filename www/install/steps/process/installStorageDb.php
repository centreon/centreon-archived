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
    'id' => 'dbstorage',
    'result' => 1,
    'msg' => ''
);

$factory = new \CentreonLegacy\Core\Utils\Factory($dependencyInjector);
$utils = $factory->newUtils();
$step = new \CentreonLegacy\Core\Install\Step\Step6($dependencyInjector);
$parameters = $step->getDatabaseConfiguration();

try {
    $db = new \PDO(
        'mysql:host=' . $parameters['address'] . ';port=' . $parameters['port'],
        $parameters['root_user'],
        $parameters['root_password']
    );
} catch (\PDOException $e) {
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

try {
    //Check if realtime database exists
    $statementShowDatabase = $db->prepare("SHOW DATABASES LIKE :dbStorage");
    $statementShowDatabase->bindValue(':dbStorage', $parameters['db_storage'], \PDO::PARAM_STR);
    $statementShowDatabase->execute();

    //If it doesn't exist, create it
    if ($result = $statementShowDatabase->fetch(\PDO::FETCH_ASSOC) === false) {
        $db->exec('CREATE DATABASE `' . $parameters['db_storage'] . '`');
    } else {
        //If it exist, check if database is empty (no tables)
        $statement = $db->prepare(
            "SELECT COUNT(*) as tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :dbStorage"
        );
        $statement->bindValue(':dbStorage', $parameters['db_storage'], \PDO::PARAM_STR);
        $statement->execute();
        if (($resultCount = $statement->fetch(\PDO::FETCH_ASSOC)) && (int) $resultCount['tables'] > 0) {
            throw new \Exception(
                sprintf('Your database \'%s\' is not empty, please remove all your tables or drop your database ' .
                    'then click on refresh to retry', $parameters['db_storage'])
            );
        }
    }
    $macros = array_merge(
        $step->getBaseConfiguration(),
        $step->getDatabaseConfiguration(),
        $step->getAdminConfiguration(),
        $step->getEngineConfiguration(),
        $step->getBrokerConfiguration()
    );
    $result = $db->query('use `' . $parameters['db_storage'] . '`');
    if (!$result) {
        throw new \Exception('Cannot access to "' . $parameters['db_storage'] . '" database');
    }
    $result = splitQueries(
        '../../createTablesCentstorage.sql',
        ';',
        $db,
        '../../tmp/createTablesCentstorage',
        $macros
    );
    if ("0" != $result) {
        $return['msg'] = $result;
        echo json_encode($return);
        exit;
    }
    $result = splitQueries(
        '../../installBroker.sql',
        ';',
        $db,
        '../../tmp/installBroker',
        $macros
    );
    if ("0" != $result) {
        $return['msg'] = $result;
        echo json_encode($return);
        exit;
    }
} catch (\Exception $e) {
    if (!is_file('../../tmp/createTablesCentstorage')) {
        $return['msg'] = $e->getMessage();
        echo json_encode($return);
        exit;
    }
}

$return['result'] = 0;
echo json_encode($return);
exit;
