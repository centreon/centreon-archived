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

if (!isset($oreon)) {
    exit();
 }

isset($_GET["id"]) ? $cG = $_GET["id"] : $cG = NULL;
isset($_POST["id"]) ? $cP = $_POST["id"] : $cP = NULL;
$cG ? $id = $cG : $id = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

require_once './class/centreonConfigCentreonBroker.php';

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configCentreonBroker/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
 }

$acl = $oreon->user->access;
$serverString = $acl->getPollerString();
$allowedBrokerConf = array();
if ($serverString != "''") {
    $sql = "SELECT config_id
                FROM cfg_centreonbroker
                WHERE ns_nagios_server IN (".$serverString.")";
    $res = $pearDB->query($sql);
    while ($row = $res->fetchRow()) {
        $allowedBrokerConf[$row['config_id']] = true;
    }
 }

switch ($o) {
 case "j" :
     require_once($path."blockConfig.php");
     break; // For display a block config
 case "a" :
     require_once($path."formCentreonBroker.php");
     break; // Add CentreonBroker
 case "w" :
     require_once($path."formCentreonBroker.php");
     break; // Watch CentreonBroker
 case "c" :
     require_once($path."formCentreonBroker.php");
     break; // modify CentreonBroker
 case "s" :
     enableCentreonBrokerInDB($id);
     require_once($path."listCentreonBroker.php");
     break; // Activate a CentreonBroker CFG
 case "u" :
     disablCentreonBrokerInDB($id);
     require_once($path."listCentreonBroker.php");
     break; // Desactivate a CentreonBroker CFG
 case "m" :
     multipleCentreonBrokerInDB(isset($select) ? $select : array(), $dupNbr);
     require_once($path."listCentreonBroker.php");
     break; // Duplicate n CentreonBroker CFGs
 case "d" :
     deleteCentreonBrokerInDB(isset($select) ? $select : array());
     require_once($path."listCentreonBroker.php");
     break; // Delete n CentreonBroker CFG
 default :
     require_once($path."listCentreonBroker.php");
     break;
 }

?>
