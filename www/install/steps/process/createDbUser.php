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
    'id' => 'createuser',
    'result' => 1,
    'msg' => ''
);

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

$dbUser = $parameters['db_user'];
$dbPass = $parameters['db_password'];
$host = "localhost";
// if database server is not on localhost...
if ($parameters['address'] != "127.0.0.1" && $parameters['address'] != "localhost") {
    $getIpQuery = $link->prepare(
        'SELECT host FROM information_schema.processlist WHERE ID = connection_id()'
    );
    $getIpQuery->execute();
    // The result example (172.17.0.1:38216), use the explode function to remove port
    $host = explode(":", $getIpQuery->fetchAll(PDO::FETCH_COLUMN)[0])[0];
}
$query = "GRANT ALL PRIVILEGES ON `%s`.* TO `" . $dbUser . "`@`" . $host .
    "` IDENTIFIED BY '" . $dbPass . "' WITH GRANT OPTION";
try {
    $link->exec(sprintf($query, $parameters['db_configuration']));
    $link->exec(sprintf($query, $parameters['db_storage']));
} catch (\PDOException $e) {
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

$return['result'] = 0;
echo json_encode($return);
exit;
