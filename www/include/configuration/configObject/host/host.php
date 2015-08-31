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
    exit ();
}

isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
$hG ? $host_id = $hG : $host_id = $hP;

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

/*
 * Path to the configuration dir
 */
global $path;

$path = "./include/configuration/configObject/host/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

if (isset($_POST["o1"]) && isset($_POST["o2"])){
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
 }

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
 }

$acl = $oreon->user->access;
$dbmon = $oreon->broker->getBroker() == 'broker' ? new CentreonDB('centstorage') : new CentreonDB('ndo');
$aclDbName = $acl->getNameDBAcl($oreon->broker->getBroker());
$hgs = $acl->getHostGroupAclConf(null, $oreon->broker->getBroker());
$aclHostString = $acl->getHostsString('ID', $dbmon);
$aclPollerString = $acl->getPollerString();

switch ($o)	{
 case "a" 	: require_once($path."formHost.php"); break; #Add a host
 case "w" 	: require_once($path."formHost.php"); break; #Watch a host
 case "c" 	: require_once($path."formHost.php"); break; #Modify a host
 case "mc" 	: require_once($path."formHost.php"); break; # Massive Change
 case "s" 	: enableHostInDB($host_id); require_once($path."listHost.php"); break; #Activate a host
 case "ms" 	: enableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
 case "u" 	: disableHostInDB($host_id); require_once($path."listHost.php"); break; #Desactivate a host
 case "mu" 	: disableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
 case "m" 	: multipleHostInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listHost.php"); break; #Duplicate n hosts
 case "d" 	: deleteHostInDB(isset($select) ? $select : array()); require_once($path."listHost.php"); break; #Delete n hosts
 default 	: require_once($path."listHost.php"); break;
}

?>
