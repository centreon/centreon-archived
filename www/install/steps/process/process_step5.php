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
require_once __DIR__ . '/../../../class/centreonAuth.class.php';

$err = array(
    'required' => array(),
    'email' => true,
    'password' => true,
    'password_security_policy' => true
);

$emailRegexp = "/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?" .
    "(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/";

$parameters = filter_input_array(INPUT_POST);
foreach ($parameters as $name => $value) {
    if (trim($value) == '') {
        $err['required'][] = $name;
    }
}

if (!in_array('email', $err['required']) && !preg_match($emailRegexp, $parameters['email'])) {
    $err['email'] = false;
}

if (
    !in_array('admin_password', $err['required'])
    && !in_array('confirm_password', $err['required'])
    && $parameters['admin_password'] !== $parameters['confirm_password']
) {
    $err['password'] = false;
}

if (
    !preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
        $parameters['admin_password']
    )
) {
    $err['password_security_policy'] = false;
} else {
    $parameters['admin_password'] = password_hash(
        $parameters['admin_password'],
        \CentreonAuth::PASSWORD_HASH_ALGORITHM
    );
}

if (!count($err['required']) && $err['password'] && $err['email']) {
    $step = new \CentreonLegacy\Core\Install\Step\Step5($dependencyInjector);
    $step->setAdminConfiguration($parameters);
}

echo json_encode($err);
