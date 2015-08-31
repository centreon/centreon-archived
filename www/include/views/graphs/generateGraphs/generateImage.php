<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

/**
 * Include config file
 */
include "@CENTREON_ETC@/centreon.conf.php";

require_once $centreon_path . '/www/class/centreon.class.php';
require_once $centreon_path . '/www/class/centreonACL.class.php';
require_once $centreon_path . '/www/class/centreonGraph.class.php';
require_once $centreon_path . '/www/class/centreonDB.class.php';
require_once $centreon_path . '/www/class/centreonBroker.class.php';


$pearDB = new CentreonDB();

$mySessionId = isset($_GET['session_id']) ? $_GET['session_id'] : '' ;

/**
 * Checks for token
 */
if ((isset($_GET["token"]) || isset($_GET["akey"])) && isset($_GET['username'])) {    
    $token = isset($_GET['token']) ? $_GET['token'] : $_GET['akey'];
    $DBRESULT = $pearDB->query("SELECT * FROM `contact`
    				WHERE `contact_alias` = '".$pearDB->escape($_GET["username"])."'
    				AND `contact_activate` = '1'
    				AND `contact_autologin_key` = '".$token."' LIMIT 1");
    if ($DBRESULT->numRows()) {
        $row = $DBRESULT->fetchRow();
        session_start();
        $mySessionId = session_id();
        $res = $pearDB->query("SELECT session_id FROM session WHERE session_id = '".$mySessionId."'");
        if (!$res->numRows()) {
            $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".$mySessionId."', '".$row["contact_id"]."', '', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
        }        
    } else {
        die('Invalid token');
    }

}

$index = isset($_GET['index']) ? $_GET['index'] : 0;

if (isset($_GET["hostname"]) && isset($_GET["service"])) {
    $pearDBO = new CentreonDB("centstorage");
    $DBRESULT = $pearDBO->query("SELECT `id`
                                 FROM index_data
    				 WHERE host_name = '".$pearDB->escape($_GET["hostname"])."'
    				 AND service_description = '".$pearDB->escape($_GET["service"])."'
    				 LIMIT 1");
    if ($DBRESULT->numRows()) {
        $res = $DBRESULT->fetchRow();
        $index = $res["id"];
    } else {
        die('Resource not found');
    }
}

$sql = "SELECT c.contact_id, c.contact_admin 
        FROM session s, contact c
        WHERE s.session_id = '".$mySessionId."'
        AND s.user_id = c.contact_id 
        LIMIT 1";
$res = $pearDB->query($sql);
if (!$res->numRows()) {
    die('Unknown user');
}

$row = $res->fetchRow();
$isAdmin = $row['contact_admin'];
$contactId = $row['contact_id'];

if (!$isAdmin) {
    $acl = new CentreonACL($contactId, $isAdmin);
    $brokerObj = new CentreonBroker($pearDB);
    if ($brokerObj->getBroker() == 'broker') {
        $dbmon = new CentreonDB('centstorage');
    } else {
        $dbmon = new CentreonDB('ndo');
    }
    $dbstorage = new CentreonDB('centstorage');
    $aclGroups = $acl->getAccessGroupsString();
    $sql = "SELECT host_id, service_id FROM index_data WHERE id = " .$pearDB->escape($index);
    $res = $dbstorage->query($sql);
    if (!$res->numRows()) {
        die('Graph not found');
    }    
    $row = $res->fetchRow();
    unset($res);
    $hostId = $row['host_id'];
    $serviceId = $row['service_id'];
    $sql = "SELECT service_id 
            FROM centreon_acl 
            WHERE host_id = $hostId
            AND service_id = $serviceId
            AND group_id IN ($aclGroups)";
    $res = $dbmon->query($sql);
    if (!$res->numRows()) {
        die('Access denied');
    }
}

/**
 * Create XML Request Objects
 */
$obj = new CentreonGraph($mySessionId, $index, 0, 1);

if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    ;
} else {
    $obj->displayError();
}

require_once $centreon_path."www/include/common/common-Func.php";

/**
 * Set arguments from GET
 */
$obj->setRRDOption("start", $obj->checkArgument("start", $_GET, time() - (60*60*48)) );
$obj->setRRDOption("end",   $obj->checkArgument("end", $_GET, time()) );

$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

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

/**
 * Display Images Binary Data
 */
$obj->displayImageFlow();


/**
 * Closing session
 */
if (isset($_GET['akey'])) {
    $pearDB->query("DELETE FROM session WHERE session_id = '".$pearDB->escape($mySessionId)."'AND user_id = (SELECT contact_id from contact where contact_autologin_key = '".$_GET['akey']."')");
}

