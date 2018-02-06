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

require_once realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
require_once _CENTREON_PATH_ . 'www/class/centreon.class.php';
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . '/class/webService.class.php';
require_once dirname(__FILE__) . '/exceptions.php';

$pearDB = new CentreonDB();

/* Purge old token */
$pearDB->query("DELETE FROM ws_token WHERE generate_date < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

/* Test if the call is for authenticate */
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_GET['action']) && $_GET['action'] == 'authenticate') {
    if (false === isset($_POST['username']) || false === isset($_POST['password'])) {
        CentreonWebService::sendJson("Bad parameters", 400);
    }
    
    /* @todo Check if user already have valid token */
    require_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";
    require_once _CENTREON_PATH_ . "/www/class/centreonAuth.class.php";
    
    /* Authenticate the user */
    $log = new CentreonUserLog(0, $pearDB);
    $auth = new CentreonAuth($_POST['username'], $_POST['password'], 0, $pearDB, $log, 1, "", "API");
    if ($auth->passwdOk == 0) {
        CentreonWebService::sendJson("Bad credentials", 403);
        exit();
    }
    
    /* Check if user exists in contact table */
    $reachAPI = 0;
    $res = $pearDB->prepare("SELECT contact_id, reach_api, contact_admin FROM contact WHERE contact_activate = '1' AND contact_register = '1' AND contact_alias = ?");
    $res = $pearDB->execute($res, array($_POST['username']));
    while ($data = $res->fetchRow()) {
      if (isset($data['contact_admin']) && $data['contact_admin'] == 1) {
            $reachAPI = 1;
        } else {
            if (isset($data['reach_api']) && $data['reach_api'] == 1) {
               $reachAPI = 1;
            }
        }
    }

    /* Sorry no access for this user */
    if ($reachAPI == 0) {
        CentreonWebService::sendJson("Unauthorized - Account not enabled", 401);
        exit();
    }

    /* Insert Token in API webservice session table */
    $token = base64_encode(uniqid('', true));
    $res = $pearDB->prepare("INSERT INTO ws_token (contact_id, token, generate_date) VALUES (?, ?, NOW())");
    $pearDB->execute($res, array($auth->userInfos['contact_id'], $token));

    /* Send Data in Json */
    CentreonWebService::sendJson(array('authToken' => $token));
}

/* Test authentication */
if (false === isset($_SERVER['HTTP_CENTREON_AUTH_TOKEN'])) {
    CentreonWebService::sendJson("Unauthorized", 401);
}

/* Create the default object */
$res = $pearDB->prepare("SELECT c.* FROM ws_token w, contact c WHERE c.contact_id = w.contact_id AND token = ?");
$res = $pearDB->execute($res, array($_SERVER['HTTP_CENTREON_AUTH_TOKEN']));
if (PEAR::isError($res)) {
    CentreonWebService::sendJson("Database error", 500);
}
$userInfos = $res->fetchRow();
if (is_null($userInfos)) {
    CentreonWebService::sendJson("Unauthorized", 401);
}

$centreon = new Centreon($userInfos);
$oreon = $centreon;

CentreonWebService::router();
