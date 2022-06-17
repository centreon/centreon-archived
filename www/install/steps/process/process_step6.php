<?php

/*
 * Copyright 2005-2022 Centreon
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
require_once __DIR__ . '/../functions.php';

define('SQL_ERROR_CODE_ACCESS_DENIED', 1698);

$requiredParameters = array(
    'db_configuration',
    'db_storage',
    'db_user',
    'db_password',
    'db_password_confirm'
);

$err = array(
    'required' => array(),
    'password' => true,
    'connection' => ''
);

$parameters = filter_input_array(INPUT_POST);
foreach ($parameters as $name => $value) {
    if (in_array($name, $requiredParameters) && trim($value) == '') {
        $err['required'][] = $name;
    }
}

if (!in_array('db_password', $err['required']) && !in_array('db_password_confirm', $err['required']) &&
    $parameters['db_password'] != $parameters['db_password_confirm']
) {
    $err['password'] = false;
}

try {
    if ($parameters['address'] == "") {
        $parameters['address'] = "localhost";
    }
    if ($parameters['port'] == "") {
        $parameters['port'] = "3306";
    }
    if ($parameters['root_user'] == "") {
        $parameters['root_user'] = "root";
    }
    $link = new \PDO(
        'mysql:host=' . $parameters['address'] . ';port=' . $parameters['port'],
        $parameters['root_user'],
        $parameters['root_password']
    );
    checkMariaDBPrerequisite($link);
    $link = null;
} catch (\Exception $e) {
    if ($e instanceof \PDOException && (int) $e->getCode() === SQL_ERROR_CODE_ACCESS_DENIED) {
        $err['connection'] =
            'Please check the root database username and password. '
            . 'If the problem persists, check that you have properly '
            . '<a target="_blank" href="https://docs.centreon.com/docs/installation'
            . '/installation-of-a-central-server/using-packages/#secure-the-database">secured your DBMS</a>';
    } else {
        $err['connection'] = $e->getMessage();
    }
}

if (!count($err['required']) && $err['password'] && trim($err['connection']) == '') {
    $step = new \CentreonLegacy\Core\Install\Step\Step6($dependencyInjector);
    $step->setDatabaseConfiguration($parameters);
}

echo json_encode($err);
