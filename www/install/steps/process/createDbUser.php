<?php
/*
 * Copyright 2005-2019 Centreon
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

$queryValues = [];
$queryValues[':dbUser'] = $parameters['db_user'];
$queryValues[':host'] = $host;
$queryValues[':dbPass'] = $parameters['db_password'];

// Compatibility adaptation for mysql 8 with php7.1 before 7.1.16, or php7.2 before 7.2.4.
$createUser = "CREATE USER :dbUser@:host IDENTIFIED BY :dbPass";

// As ALTER USER won't work on a mariaDB < 10.2, we need to check it before trying this request
$checkMysqlVersion = "SHOW VARIABLES WHERE Variable_name LIKE 'version%'";

// creating the user - mandatory for MySQL DB
$alterQuery = "ALTER USER :dbUser@:host IDENTIFIED WITH mysql_native_password BY :dbPass";
$query = "GRANT ALL PRIVILEGES ON `%s`.* TO " . $parameters['db_user'] . "@" . $host . " WITH GRANT OPTION";
$flushQuery = "FLUSH PRIVILEGES";

try {
    $prepareCreate = $link->prepare($createUser);
    $prepareAlter = $link->prepare($alterQuery);
    foreach ($queryValues as $key => $value) {
        $prepareCreate->bindValue($key, $value, \PDO::PARAM_STR);
        $prepareAlter->bindValue($key, $value, \PDO::PARAM_STR);
    }
    // creating the user
    $prepareCreate->execute();

    // checking mysql version before trying to alter the password plugin
    $prepareCheckVersion = $link->query($checkMysqlVersion);
    $versionName = $versionNumber = "";
    while ($row = $prepareCheckVersion->fetch()) {
        if ($row['Variable_name'] === "version") {
            $versionNumber = $row['Variable_name'];
        } elseif ($row['Variable_name'] === "version_comment") {
            $versionName = $row['Variable_name'];
        }
    }
    if ((strpos($versionName, "MariaDB") !== false && version_compare($versionNumber, '10.2.0') >= 0)
        || (strpos($versionName, "MySQL") !== false && version_compare($versionNumber, '8.0.0') >= 0)
    ) {
        // altering the mysql's password plugin using the ALTER USER request
        $prepareAlter->execute();
    }

    // granting privileges
    $link->exec(sprintf($query, $parameters['db_configuration']));
    $link->exec(sprintf($query, $parameters['db_storage']));

    // enabling the new parameters
    $link->exec($flushQuery);
} catch (\PDOException $e) {
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

$return['result'] = 0;
echo json_encode($return);
exit;
