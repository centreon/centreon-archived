<?php
/**
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

/**
 * Include config file
 */
require_once realpath(__DIR__ . "/../../../../../config/centreon.config.php");

require_once _CENTREON_PATH_ . '/www/class/centreon.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonACL.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonGraph.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonBroker.class.php';

$pearDB = new CentreonDB();

session_start();
session_write_close();

$mySessionId = session_id();

// checks for token
if (!empty($_GET['username'])) {
    $userName = filter_var($_GET['username'] ?? null, FILTER_SANITIZE_STRING);
}

$token = filter_var(
    $_GET['token'] ?? $_GET['akey'] ?? null,
    FILTER_SANITIZE_STRING
);

if (!empty($userName) && !empty($token)) {
    $query = "SELECT contact_id FROM `contact`
        WHERE `contact_alias` = :contact_alias
        AND `contact_activate` = '1'
        AND `contact_autologin_key` = :token LIMIT 1";
    $statement = $pearDB->prepare($query);
    $statement->bindValue(':contact_alias', $userName, \PDO::PARAM_STR);
    $statement->bindValue(':token', $token, \PDO::PARAM_STR);

    $statement->execute();

    if ($row = $statement->fetch()) {
        $res = $pearDB->prepare('SELECT session_id FROM session WHERE session_id = :sessionId');
        $res->bindValue(':sessionId', $mySessionId, \PDO::PARAM_STR);
        $res->execute();
        if (!$res->rowCount()) {
            // security fix - regenerate the sid to prevent session fixation
            session_start();
            session_regenerate_id(true);
            $mySessionId = session_id();

            $query = 'INSERT INTO `session` (`session_id`, `user_id`, `current_page`, `last_reload`, `ip_address`)
                VALUES (:sessionId, :contactId, NULL, :lastReload, :ipAddress)';

            $statement = $pearDB->prepare($query);
            $statement->bindValue(':contactId', $row['contact_id'], \PDO::PARAM_INT);
            $statement->bindValue(':sessionId', $mySessionId, \PDO::PARAM_STR);
            $statement->bindValue(':lastReload', time(), \PDO::PARAM_INT);
            $statement->bindValue(':ipAddress', $_SERVER['REMOTE_ADDR'], \PDO::PARAM_STR);
            $statement->execute();
        }
    } else {
        die('Invalid token');
    }
}

$index = filter_var(
    $_GET['index'] ?? 0,
    FILTER_VALIDATE_INT
);

// Checking hostName and service
if (!empty($_GET['hostname'])) {
    $hostName = filter_var($_GET['hostname'], FILTER_SANITIZE_STRING);
}

if (!empty($_GET['service'])) {
    $serviceDescription = filter_var($_GET['service'], FILTER_SANITIZE_STRING);
}

$pearDBO = new CentreonDB("centstorage");
if (!empty($hostName) && !empty($serviceDescription)) {
    $statement = $pearDBO->prepare(
        "SELECT `id`
        FROM index_data
        WHERE host_name = :hostName
        AND service_description = :serviceDescription
        LIMIT 1"
    );

    $statement->bindValue(':hostName', $hostName, \PDO::PARAM_STR);
    $statement->bindValue(':serviceDescription', $serviceDescription, \PDO::PARAM_STR);
    $statement->execute();
    if ($res = $statement->fetch()) {
        $index = $res["id"];
    } else {
        die('Resource not found');
    }
}

$chartId = null;

if (!empty($_GET['chartId'])) {
    $chartId = filter_var($_GET['chartId'], FILTER_SANITIZE_STRING);
}

if (!empty($chartId)) {
    if (preg_match('/([0-9]+)_([0-9]+)/', $chartId, $matches)) {
        $hostId = (int) $matches[1];
        $serviceId = (int) $matches[2];
    } else {
        throw new \InvalidArgumentException('chartId must be a combination of integers');
    }
    $statement = $pearDBO->prepare(
        'SELECT id FROM index_data WHERE host_id = :hostId AND service_id = :serviceId'
    );
    $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
    $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);

    $statement->execute();

    if ($row = $statement->fetch()) {
        $index = $row['id'];
    } else {
        die('Resource not found');
    }
}

$res = $pearDB->prepare(
    "SELECT c.contact_id, c.contact_admin
    FROM session s, contact c
    WHERE s.session_id = :sessionId
    AND s.user_id = c.contact_id
    LIMIT 1"
);
$res->bindValue(':sessionId', $mySessionId, \PDO::PARAM_STR);
$res->execute();

if (!$res->rowCount()) {
    die('Unknown user');
}

$row = $res->fetch();
$isAdmin = $row['contact_admin'];
$contactId = $row['contact_id'];

if (!$isAdmin) {
    $acl = new CentreonACL($contactId, $isAdmin);
    $dbstorage = new CentreonDB('centstorage');

    $aclGroups = $acl->getAccessGroupsString();
    $sql = "SELECT host_id, service_id FROM index_data WHERE id = " .$pearDB->escape($index);
    $res = $dbstorage->query($sql);
    if (!$res->rowCount()) {
        die('Graph not found');
    }
    $row = $res->fetch();
    unset($res);
    $hostId = $row['host_id'];
    $serviceId = $row['service_id'];
    $sql = "SELECT service_id FROM centreon_acl WHERE host_id = $hostId AND service_id = $serviceId
        AND group_id IN ($aclGroups)";
    $res = $pearDBO->query($sql);
    if (!$res->rowCount()) {
        die('Access denied');
    }
}

/* Check security session */
if (!CentreonSession::checkSession($mySessionId, $pearDB)) {
    CentreonGraph::displayError();
}

require_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

/**
 * Create XML Request Objects
 */
$obj = new CentreonGraph($contactId, $index, 0, 1);

/**
 * Set arguments from GET
 */
$obj->setRRDOption("start", $obj->checkArgument("start", $_GET, time() - (60*60*48)));
$obj->setRRDOption("end", $obj->checkArgument("end", $_GET, time()));

/**
 * Template Management
 */
if (isset($_GET["template_id"])) {
    $obj->setTemplate($_GET["template_id"]);
} else {
    $obj->setTemplate();
}

$obj->init();
if (isset($_GET["flagperiod"])) {
    $obj->setCommandLineTimeLimit($_GET["flagperiod"]);
}

/**
 * Init Curve list
 */
if (isset($_GET["metric"])) {
    $obj->setMetricList($_GET["metric"]);
}
$obj->initCurveList();

/**
 * Comment time
 */
$obj->setOption("comment_time");

/**
 * Create Legende
 */
$obj->createLegend();

$obj->setColor('BACK', '#FFFFFF');
$obj->setColor('FRAME', '#FFFFFF');
$obj->setColor('SHADEA', '#EFEFEF');
$obj->setColor('SHADEB', '#EFEFEF');
$obj->setColor('ARROW', '#FF0000');

/**
 * Display Images Binary Data
 */
$obj->displayImageFlow();

/**
 * Closing session
 */
if (isset($_GET['akey'])) {
    $dbResult = $pearDB->prepare(
        "DELETE FROM session
        WHERE session_id = ? AND user_id = (SELECT contact_id from contact where contact_autologin_key = ?)");
    $dbResult = $pearDB->execute($dbResult, array($mySessionId, $_GET['akey']));
}
